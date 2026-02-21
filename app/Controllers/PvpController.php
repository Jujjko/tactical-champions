<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Services\MatchmakingService;
use App\Services\PvpService;
use App\Services\BattleEngine;
use App\Services\BattleStateManager;
use App\Models\UserChampion;
use App\Models\PvpRating;

class PvpController extends Controller {
    private MatchmakingService $matchmaking;
    private PvpService $pvpService;
    
    public function __construct() {
        $this->matchmaking = new MatchmakingService();
        $this->pvpService = new PvpService();
    }
    
    public function index(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $userId = Session::userId();
        $season = $this->matchmaking->getActiveSeason();
        $stats = $this->pvpService->getUserStats($userId);
        $champions = (new UserChampion())->getUserChampions($userId);
        $leaderboard = $this->matchmaking->getLeaderboard(10);
        $recentBattles = $this->pvpService->getBattleHistory($userId, 5);
        
        $this->view('game/pvp', [
            'season' => $season,
            'stats' => $stats,
            'champions' => $champions,
            'leaderboard' => $leaderboard,
            'recentBattles' => $recentBattles,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function findMatch(): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $userId = Session::userId();
        $championId = (int)($_POST['champion_id'] ?? 0);
        
        if (!$this->pvpService->canAffordPvp($userId)) {
            $this->jsonError('Not enough energy (15 required)', 400);
        }
        
        $match = $this->matchmaking->findMatch($userId, $championId > 0 ? $championId : null);
        
        if (!$match) {
            $this->jsonError('No opponents available. Try again later.', 404);
        }
        
        $this->jsonSuccess($match);
    }
    
    public function startBattle(): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $userId = Session::userId();
        $opponentId = (int)($_POST['opponent_id'] ?? 0);
        $myChampionId = (int)($_POST['my_champion_id'] ?? 0);
        $opponentChampionId = (int)($_POST['opponent_champion_id'] ?? 0);
        
        if ($opponentId <= 0 || $myChampionId <= 0 || $opponentChampionId <= 0) {
            $this->jsonError('Invalid match data', 400);
        }
        
        if (!$this->pvpService->canAffordPvp($userId)) {
            $this->jsonError('Not enough energy', 400);
        }
        
        $this->pvpService->deductPvpEnergy($userId);
        
        $result = $this->pvpService->startPvpBattle(
            $userId,
            $opponentId,
            $myChampionId,
            $opponentChampionId
        );
        
        if (!$result['success']) {
            $this->jsonError($result['error'] ?? 'Failed to start battle', 400);
        }
        
        $battleState = $result['battle_state'];
        $battleState['is_pvp'] = true;
        $battleState['opponent_id'] = $opponentId;
        $battleState['opponent_champion_id'] = $opponentChampionId;
        $battleState['my_champion_id'] = $myChampionId;
        $battleState['start_time'] = time();
        
        $battleStateManager = new BattleStateManager();
        $battleStateManager->save($userId, $battleState);
        
        $this->jsonSuccess([
            'redirect' => '/pvp/battle',
            'battle_id' => $userId,
        ]);
    }
    
    public function battle(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $userId = Session::userId();
        $battleStateManager = new BattleStateManager();
        $battleState = $battleStateManager->get($userId);
        
        if (!$battleState) {
            $this->redirect('/pvp');
        }
        
        $myChampion = (new UserChampion())->getChampionWithDetails(
            $battleState['my_champion_id'], 
            $userId
        );
        
        $this->view('game/pvp-battle', [
            'battle_state' => $battleState,
            'my_champion' => $myChampion,
            'is_pvp' => true,
            'opponent_name' => $battleState['opponent_name'] ?? 'Opponent',
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function executeAction(): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $userId = Session::userId();
        $action = $_POST['action'] ?? 'attack';
        $attackerId = $_POST['attacker_id'] ?? null;
        $targetId = $_POST['target_id'] ?? null;
        $useAbility = (bool)($_POST['use_ability'] ?? false);
        
        $battleStateManager = new BattleStateManager();
        $battleState = $battleStateManager->get($userId);
        
        if (!$battleState) {
            $this->jsonError('No active battle', 404);
        }
        
        $battleEngine = new BattleEngine();
        $battleEngine->setState($battleState);
        
        $result = $battleEngine->executeTurn($action, $attackerId, $targetId, $useAbility);
        
        if ($result['battle_ended']) {
            $duration = time() - ($battleState['start_time'] ?? time());
            $battleResult = $result['winner'] === 'victory' ? 'victory' : 'defeat';
            
            $battleResultData = $this->pvpService->processBattleResult(
                $userId,
                $battleState['opponent_id'],
                $battleResult,
                $duration,
                json_encode($result['battle_log'])
            );
            
            $battleStateManager->delete($userId);
            
            $stats = $this->pvpService->getUserStats($userId);
            
            $this->jsonSuccess([
                'battle_ended' => true,
                'result' => $battleResult,
                'state' => $result,
                'stats' => $stats,
                'rewards' => $battleResultData['rewards'] ?? null,
            ]);
        }
        
        $aiResult = $battleEngine->executeAITurn();
        $battleStateManager->save($userId, $aiResult);
        
        if ($aiResult['battle_ended']) {
            $duration = time() - ($battleState['start_time'] ?? time());
            $battleResult = $aiResult['winner'] === 'victory' ? 'victory' : 'defeat';
            
            $battleResultData = $this->pvpService->processBattleResult(
                $userId,
                $battleState['opponent_id'],
                $battleResult,
                $duration,
                json_encode($aiResult['battle_log'])
            );
            
            $battleStateManager->delete($userId);
            
            $stats = $this->pvpService->getUserStats($userId);
            
            $this->jsonSuccess([
                'battle_ended' => true,
                'result' => $battleResult,
                'state' => $aiResult,
                'stats' => $stats,
                'rewards' => $battleResultData['rewards'] ?? null,
            ]);
        }
        
        $battleStateManager->save($userId, $result);
        
        $this->jsonSuccess([
            'battle_ended' => false,
            'state' => $result,
        ]);
    }
    
    public function leaderboard(): void {
        $leaderboard = $this->matchmaking->getLeaderboard(100);
        
        $this->view('game/pvp-leaderboard', [
            'leaderboard' => $leaderboard,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function history(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $userId = Session::userId();
        $battles = $this->pvpService->getBattleHistory($userId, 50);
        
        $this->view('game/pvp-history', [
            'battles' => $battles,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
}
