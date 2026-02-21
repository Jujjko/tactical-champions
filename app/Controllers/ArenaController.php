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
    public function index(): void {
        $userId = Session::userId();
        
        $pvpRatingModel = new PvpRating();
        $pvpChallengeModel = new PvpChallenge();
        $userChampionModel = new UserChampion();
        $arenaQueueModel = new ArenaQueue();
        
        $rating = $pvpRatingModel->getOrCreateForUser($userId);
        $pendingChallenges = $pvpChallengeModel->getPendingChallenges($userId);
        $sentChallenges = $pvpChallengeModel->getSentChallenges($userId);
        $champions = $userChampionModel->getUserChampions($userId);
        $leaderboard = $pvpRatingModel->getLeaderboard(10);
        $queueEntry = $arenaQueueModel->getQueueEntry($userId);
        $queueCount = $arenaQueueModel->getQueueCount();
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
        $pvpRatingModel = new PvpRating();
        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        
        $leaderboard = $pvpRatingModel->getLeaderboard($limit);
        
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
        
        $userChampionModel = new UserChampion();
        $champion = $userChampionModel->findById($championId);
        
        if (!$champion || $champion['user_id'] !== $userId) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $userModel = new User();
        $defender = $userModel->findById($defenderId);
        
        if (!$defender) {
            $this->jsonError('Player not found', 404);
            return;
        }
        
        $pvpChallengeModel = new PvpChallenge();
        $challengeId = $pvpChallengeModel->createChallenge($userId, $championId, $defenderId);
        
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
        
        $pvpChallengeModel = new PvpChallenge();
        $challenge = $pvpChallengeModel->findById($challengeId);
        
        if (!$challenge || $challenge['defender_id'] !== $userId) {
            $this->jsonError('Challenge not found', 404);
            return;
        }
        
        if ($challenge['status'] !== 'pending') {
            $this->jsonError('Challenge already processed', 400);
            return;
        }
        
        $pvpChallengeModel->acceptChallenge($challengeId, $championId);
        
        $this->jsonSuccess(['message' => 'Challenge accepted! Prepare for battle!']);
    }
    
    public function declineChallenge(string $id): void {
        $userId = Session::userId();
        $challengeId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $pvpChallengeModel = new PvpChallenge();
        $challenge = $pvpChallengeModel->findById($challengeId);
        
        if (!$challenge || $challenge['defender_id'] !== $userId) {
            $this->jsonError('Challenge not found', 404);
            return;
        }
        
        $pvpChallengeModel->declineChallenge($challengeId);
        
        $this->jsonSuccess(['message' => 'Challenge declined']);
    }
    
    public function joinQueue(): void
    {
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
        
        $userChampionModel = new UserChampion();
        $champion = $userChampionModel->findById($championId);
        
        if (!$champion || $champion['user_id'] !== $userId) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $pvpRatingModel = new PvpRating();
        $rating = $pvpRatingModel->getOrCreateForUser($userId);
        
        $arenaQueueModel = new ArenaQueue();
        $arenaQueueModel->joinQueue($userId, $championId, (int)$rating['rating']);
        
        $this->jsonSuccess([
            'message' => 'Joined matchmaking queue',
            'position' => $arenaQueueModel->getQueuePosition($userId),
        ]);
    }
    
    public function leaveQueue(): void
    {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $arenaQueueModel = new ArenaQueue();
        $arenaQueueModel->leaveQueue($userId);
        
        $this->jsonSuccess(['message' => 'Left matchmaking queue']);
    }
    
    public function checkMatch(): void
    {
        $userId = Session::userId();
        
        $arenaQueueModel = new ArenaQueue();
        $queueEntry = $arenaQueueModel->getQueueEntry($userId);
        
        if (!$queueEntry) {
            $this->jsonSuccess(['status' => 'not_in_queue']);
            return;
        }
        
        $pvpRatingModel = new PvpRating();
        $rating = $pvpRatingModel->getOrCreateForUser($userId);
        
        $match = $arenaQueueModel->findMatch($userId, (int)$rating['rating'], 150);
        
        if (!$match) {
            $match = $arenaQueueModel->expandSearch($userId, (int)$rating['rating'], 300);
        }
        
        if ($match) {
            $arenaQueueModel->leaveQueue($userId);
            $arenaQueueModel->leaveQueue($match['user_id']);
            
            $pvpChallengeModel = new PvpChallenge();
            $challengeId = $pvpChallengeModel->createChallenge(
                $userId,
                $queueEntry['champion_id'],
                $match['user_id'],
                150
            );
            $pvpChallengeModel->acceptChallenge($challengeId, $match['champion_id']);
            
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
        
        $position = $arenaQueueModel->getQueuePosition($userId);
        $queueCount = $arenaQueueModel->getQueueCount();
        $waitTime = time() - strtotime($queueEntry['created_at']);
        
        $this->jsonSuccess([
            'status' => 'searching',
            'position' => $position,
            'queue_count' => $queueCount,
            'wait_time' => $waitTime,
        ]);
    }
}
