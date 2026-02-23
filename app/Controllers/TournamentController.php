<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Services\TournamentService;
use App\Models\Tournament;

class TournamentController extends Controller {
    private TournamentService $tournamentService;
    private Tournament $tournamentModel;
    
    public function __construct() {
        $this->tournamentService = new TournamentService();
        $this->tournamentModel = new Tournament();
    }
    
    public function index(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $userId = Session::userId();
        $activeTournaments = $this->tournamentModel->getActiveTournaments();
        $myTournaments = $this->tournamentModel->getUserTournaments($userId);
        
        foreach ($activeTournaments as &$tournament) {
            $tournament['is_registered'] = $this->tournamentModel->isUserRegistered($tournament['id'], $userId);
        }
        unset($tournament);
        
        $this->view('game/tournaments', [
            'tournaments' => $activeTournaments,
            'my_tournaments' => $myTournaments,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function show(int $id): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $tournament = $this->tournamentModel->getWithRewards($id);
        
        if (!$tournament) {
            $this->redirect('/tournaments');
        }
        
        $userId = Session::userId();
        $tournament['is_registered'] = $this->tournamentModel->isUserRegistered($id, $userId);
        $tournament['current_match'] = null;
        
        if ($tournament['status'] === 'ongoing' && $tournament['is_registered']) {
            $tournament['current_match'] = $this->tournamentService->getUserCurrentMatch($id, $userId);
        }
        
        $this->view('game/tournament-detail', [
            'tournament' => $tournament,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function join(int $id): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $result = $this->tournamentService->joinTournament($id, Session::userId());
        
        if ($result['success']) {
            $this->jsonSuccess([], $result['message']);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function leave(int $id): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $result = $this->tournamentService->leaveTournament($id, Session::userId());
        
        if ($result['success']) {
            $this->jsonSuccess([], $result['message']);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function bracket(int $id): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $tournament = $this->tournamentService->getTournamentBracket($id);
        
        if (!$tournament) {
            $this->redirect('/tournaments');
        }
        
        $this->view('game/tournament-bracket', [
            'tournament' => $tournament,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function match(int $tournamentId, int $matchId): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $tournament = $this->tournamentModel->findById($tournamentId);
        
        if (!$tournament || $tournament['status'] !== 'ongoing') {
            $this->redirect('/tournaments');
        }
        
        $this->view('game/tournament-match', [
            'tournament' => $tournament,
            'match_id' => $matchId,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function reportResult(int $matchId): void {
        if (!Session::isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $winnerId = (int)($_POST['winner_id'] ?? 0);
        
        if ($winnerId <= 0) {
            $this->jsonError('Invalid winner', 400);
        }
        
        $result = $this->tournamentService->processMatchResult($matchId, $winnerId);
        
        if ($result['success']) {
            $this->jsonSuccess([], $result['message']);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function create(): void {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            $this->redirect('/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->redirectWithError('/tournaments', 'Invalid request');
            }
            
            $name = trim($_POST['name'] ?? '');
            $maxPlayers = (int)($_POST['max_players'] ?? 8);
            $entryFeeGold = (int)($_POST['entry_fee_gold'] ?? 0);
            $entryFeeGems = (int)($_POST['entry_fee_gems'] ?? 0);
            $minRating = (int)($_POST['min_rating'] ?? 0);
            $startTime = $_POST['start_time'] ?? null;
            
            if (empty($name)) {
                $this->redirectWithError('/tournaments', 'Name is required');
            }
            
            $tournamentId = $this->tournamentService->createTournament([
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'max_players' => $maxPlayers,
                'entry_fee_gold' => $entryFeeGold,
                'entry_fee_gems' => $entryFeeGems,
                'min_rating' => $minRating,
                'start_time' => $startTime,
            ], Session::userId());
            
            $this->redirectWithSuccess("/tournaments/{$tournamentId}", 'Tournament created');
        }
        
        $this->view('admin/tournament-create', [
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function start(int $id): void {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
        }
        
        $success = $this->tournamentService->generateBracket($id);
        
        if ($success) {
            $this->jsonSuccess([], 'Tournament started');
        } else {
            $this->jsonError('Failed to start tournament', 400);
        }
    }
}
