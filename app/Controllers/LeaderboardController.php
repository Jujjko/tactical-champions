<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Services\LeaderboardService;

class LeaderboardController extends Controller {
    private LeaderboardService $service;
    
    public function __construct() {
        $this->service = new LeaderboardService();
    }
    
    public function index(): void {
        $tab = $_GET['tab'] ?? 'level';
        
        $globalLevel = $this->service->getGlobalLevelLeaderboard(50);
        $globalWins = $this->service->getGlobalWinsLeaderboard(50);
        $pvpRank = $this->service->getPvpLeaderboard(50);
        $byChampions = $this->service->getTopPlayersByChampionCount(50);
        $byGold = $this->service->getTopPlayersByGold(50);
        
        $myPosition = null;
        if (Session::isLoggedIn()) {
            $userId = Session::userId();
            $myPosition = [
                'level' => $this->service->getUserGlobalPosition($userId),
                'pvp' => $this->service->getUserPvpPosition($userId),
            ];
        }
        
        $this->view('game/leaderboard', [
            'global_level' => $globalLevel,
            'global_wins' => $globalWins,
            'pvp_rank' => $pvpRank,
            'by_champions' => $byChampions,
            'by_gold' => $byGold,
            'my_position' => $myPosition,
            'active_tab' => $tab,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function pvp(): void {
        $leaderboard = $this->service->getPvpLeaderboard(100);
        
        $myPosition = null;
        if (Session::isLoggedIn()) {
            $myPosition = $this->service->getUserPvpPosition(Session::userId());
        }
        
        $this->view('game/leaderboard-pvp', [
            'leaderboard' => $leaderboard,
            'my_position' => $myPosition,
            'csrf_token' => Session::csrfToken(),
        ]);
    }
    
    public function api(): void {
        $type = $_GET['type'] ?? 'level';
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
        
        $data = match($type) {
            'level' => $this->service->getGlobalLevelLeaderboard($limit),
            'wins' => $this->service->getGlobalWinsLeaderboard($limit),
            'pvp' => $this->service->getPvpLeaderboard($limit),
            'champions' => $this->service->getTopPlayersByChampionCount($limit),
            'gold' => $this->service->getTopPlayersByGold($limit),
            default => $this->service->getGlobalLevelLeaderboard($limit),
        };
        
        $this->jsonSuccess(['leaderboard' => $data]);
    }
}
