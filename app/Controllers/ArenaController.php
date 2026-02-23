<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\PvpRating;
use App\Models\PvpChallenge;
use App\Models\UserChampion;
use App\Models\User;
use App\Models\ArenaQueue;
use App\Helpers\RankHelper;

class ArenaController extends Controller {
    private PvpRating $pvpRatingModel;
    private PvpChallenge $pvpChallengeModel;
    private UserChampion $userChampionModel;
    private User $userModel;
    private ArenaQueue $arenaQueueModel;
    
    public function __construct() {
        $this->pvpRatingModel = new PvpRating();
        $this->pvpChallengeModel = new PvpChallenge();
        $this->userChampionModel = new UserChampion();
        $this->userModel = new User();
        $this->arenaQueueModel = new ArenaQueue();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $rating = $this->pvpRatingModel->getOrCreateForUser($userId);
        $pendingChallenges = $this->pvpChallengeModel->getPendingChallenges($userId);
        $sentChallenges = $this->pvpChallengeModel->getSentChallenges($userId);
        $champions = $this->userChampionModel->getUserChampions($userId);
        $leaderboard = $this->pvpRatingModel->getLeaderboard(10);
        $queueEntry = $this->arenaQueueModel->getQueueEntry($userId);
        $queueCount = $this->arenaQueueModel->getQueueCount();
        $rankInfo = RankHelper::getRank((int)$rating['rating']);
        
        $this->view('game/arena', [
            'rating' => $rating,
            'pendingChallenges' => $pendingChallenges,
            'sentChallenges' => $sentChallenges,
            'champions' => $champions,
            'leaderboard' => $leaderboard,
            'queueEntry' => $queueEntry,
            'queueCount' => $queueCount,
            'rankInfo' => $rankInfo,
        ]);
    }
    
    public function leaderboard(): void {
        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        
        $leaderboard = $this->pvpRatingModel->getLeaderboard($limit);
        
        $this->view('game/arena-leaderboard', [
            'leaderboard' => $leaderboard,
            'page' => $page
        ]);
    }
    
    public function challenge(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $defenderId = (int)($_POST['defender_id'] ?? 0);
        $championId = (int)($_POST['champion_id'] ?? 0);
        
        if ($defenderId <= 0 || $championId <= 0) {
            $this->jsonError('Invalid parameters', 400);
            return;
        }
        
        if ($defenderId === $userId) {
            $this->jsonError('Cannot challenge yourself', 400);
            return;
        }
        
        $champion = $this->userChampionModel->findById($championId);
        
        if (!$champion || $champion['user_id'] !== $userId) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $defender = $this->userModel->findById($defenderId);
        
        if (!$defender) {
            $this->jsonError('Player not found', 404);
            return;
        }
        
        $challengeId = $this->pvpChallengeModel->createChallenge($userId, $championId, $defenderId);
        
        $this->jsonSuccess(['challenge_id' => $challengeId, 'message' => 'Challenge sent!']);
    }
    
    public function acceptChallenge(string $id): void {
        $userId = Session::userId();
        $challengeId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $championId = (int)($_POST['champion_id'] ?? 0);
        
        if ($championId <= 0) {
            $this->jsonError('Select a champion', 400);
            return;
        }
        
        $challenge = $this->pvpChallengeModel->findById($challengeId);
        
        if (!$challenge || $challenge['defender_id'] !== $userId) {
            $this->jsonError('Challenge not found', 404);
            return;
        }
        
        if ($challenge['status'] !== 'pending') {
            $this->jsonError('Challenge already processed', 400);
            return;
        }
        
        $this->pvpChallengeModel->acceptChallenge($challengeId, $championId);
        
        $this->jsonSuccess(['message' => 'Challenge accepted! Prepare for battle!']);
    }
    
    public function declineChallenge(string $id): void {
        $userId = Session::userId();
        $challengeId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $challenge = $this->pvpChallengeModel->findById($challengeId);
        
        if (!$challenge || $challenge['defender_id'] !== $userId) {
            $this->jsonError('Challenge not found', 404);
            return;
        }
        
        $this->pvpChallengeModel->declineChallenge($challengeId);
        
        $this->jsonSuccess(['message' => 'Challenge declined']);
    }
    
    public function joinQueue(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $championId = (int)($_POST['champion_id'] ?? 0);
        
        if ($championId <= 0) {
            $this->jsonError('Select a champion', 400);
            return;
        }
        
        $champion = $this->userChampionModel->findById($championId);
        
        if (!$champion || $champion['user_id'] !== $userId) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $rating = $this->pvpRatingModel->getOrCreateForUser($userId);
        
        $this->arenaQueueModel->joinQueue($userId, $championId, (int)$rating['rating']);
        
        $this->jsonSuccess([
            'message' => 'Joined matchmaking queue',
            'position' => $this->arenaQueueModel->getQueuePosition($userId),
        ]);
    }
    
    public function leaveQueue(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $this->arenaQueueModel->leaveQueue($userId);
        
        $this->jsonSuccess(['message' => 'Left matchmaking queue']);
    }
    
    public function checkMatch(): void {
        $userId = Session::userId();
        
        $queueEntry = $this->arenaQueueModel->getQueueEntry($userId);
        
        if (!$queueEntry) {
            $this->jsonSuccess(['status' => 'not_in_queue']);
            return;
        }
        
        $rating = $this->pvpRatingModel->getOrCreateForUser($userId);
        
        $match = $this->arenaQueueModel->findMatch($userId, (int)$rating['rating'], 150);
        
        if (!$match) {
            $match = $this->arenaQueueModel->expandSearch($userId, (int)$rating['rating'], 300);
        }
        
        if ($match) {
            $this->arenaQueueModel->leaveQueue($userId);
            $this->arenaQueueModel->leaveQueue($match['user_id']);
            
            $challengeId = $this->pvpChallengeModel->createChallenge(
                $userId,
                $queueEntry['champion_id'],
                $match['user_id'],
                150
            );
            $this->pvpChallengeModel->acceptChallenge($challengeId, $match['champion_id']);
            
            $opponentRank = RankHelper::getRank((int)($match['rating'] ?? 1000));
            
            $this->jsonSuccess([
                'status' => 'matched',
                'challenge_id' => $challengeId,
                'opponent' => [
                    'username' => $match['username'],
                    'champion_name' => $match['champion_name'],
                    'champion_level' => $match['champion_level'],
                    'rating' => $match['rating'] ?? 1000,
                    'rank' => $opponentRank,
                ],
            ]);
            return;
        }
        
        $position = $this->arenaQueueModel->getQueuePosition($userId);
        $queueCount = $this->arenaQueueModel->getQueueCount();
        $waitTime = time() - strtotime($queueEntry['created_at']);
        
        $this->jsonSuccess([
            'status' => 'searching',
            'position' => $position,
            'queue_count' => $queueCount,
            'wait_time' => $waitTime,
        ]);
    }
}
