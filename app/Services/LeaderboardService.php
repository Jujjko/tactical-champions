<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Leaderboard;
use App\Models\User;
use App\Models\Battle;
use App\Models\PvpRating;
use Core\Database;

class LeaderboardService {
    private Leaderboard $leaderboardModel;
    private Database $db;
    
    public function __construct() {
        $this->leaderboardModel = new Leaderboard();
        $this->db = Database::getInstance();
    }
    
    public function updateAllForUser(int $userId): void {
        $user = (new User())->findById($userId);
        
        if (!$user) return;
        
        $this->leaderboardModel->updatePlayerScore($userId, 'global_level', $user['level']);
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as wins FROM battles WHERE user_id = ? AND result = 'victory'
        ");
        $stmt->execute([$userId]);
        $wins = (int)$stmt->fetchColumn();
        $this->leaderboardModel->updatePlayerScore($userId, 'global_wins', $wins);
        
        $season = (new SeasonService())->getActiveSeason();
        if ($season) {
            $pvpRating = (new PvpRating())->getOrCreateForUser($userId);
            $this->leaderboardModel->updatePlayerScore(
                $userId, 
                'pvp_rank', 
                $pvpRating['rating'], 
                $season['id']
            );
        }
    }
    
    public function getGlobalLevelLeaderboard(int $limit = 50): array {
        return $this->leaderboardModel->getTop('global_level', $limit);
    }
    
    public function getGlobalWinsLeaderboard(int $limit = 50): array {
        return $this->leaderboardModel->getTop('global_wins', $limit);
    }
    
    public function getPvpLeaderboard(int $limit = 50): array {
        $season = (new SeasonService())->getActiveSeason();
        $seasonId = $season['id'] ?? null;
        
        return $this->leaderboardModel->getTop('pvp_rank', $limit, $seasonId);
    }
    
    public function getUserGlobalPosition(int $userId): int {
        return $this->leaderboardModel->getUserPosition($userId, 'global_level');
    }
    
    public function getUserPvpPosition(int $userId): int {
        $season = (new SeasonService())->getActiveSeason();
        return $this->leaderboardModel->getUserPosition($userId, 'pvp_rank', $season['id'] ?? null);
    }
    
    public function recalculateAllRanks(): void {
        $this->leaderboardModel->recalculateRanks('global_level');
        $this->leaderboardModel->recalculateRanks('global_wins');
        
        $season = (new SeasonService())->getActiveSeason();
        if ($season) {
            $this->leaderboardModel->recalculateRanks('pvp_rank', $season['id']);
        }
    }
    
    public function rebuildFromScratch(): int {
        $count = 0;
        
        $users = $this->db->query("SELECT id, level FROM users WHERE deleted_at IS NULL")->fetchAll();
        foreach ($users as $user) {
            $this->leaderboardModel->updatePlayerScore($user['id'], 'global_level', $user['level']);
            $count++;
        }
        
        $battles = $this->db->query("
            SELECT user_id, COUNT(*) as wins 
            FROM battles 
            WHERE result = 'victory' 
            GROUP BY user_id
        ")->fetchAll();
        
        foreach ($battles as $battle) {
            $this->leaderboardModel->updatePlayerScore($battle['user_id'], 'global_wins', $battle['wins']);
        }
        
        $season = (new SeasonService())->getActiveSeason();
        if ($season) {
            $pvpRatings = $this->db->query("SELECT user_id, rating FROM pvp_ratings")->fetchAll();
            foreach ($pvpRatings as $rating) {
                $this->leaderboardModel->updatePlayerScore(
                    $rating['user_id'], 
                    'pvp_rank', 
                    $rating['rating'], 
                    $season['id']
                );
            }
        }
        
        $this->recalculateAllRanks();
        
        return $count;
    }
    
    public function getCombinedLeaderboard(int $limit = 100): array {
        $globalLevel = $this->getGlobalLevelLeaderboard($limit);
        $pvpRank = $this->getPvpLeaderboard($limit);
        
        $combined = [];
        
        foreach ($globalLevel as $player) {
            $userId = $player['user_id'];
            $combined[$userId] = [
                'user_id' => $userId,
                'username' => $player['username'],
                'level' => $player['level'],
                'level_rank' => $player['position'],
                'pvp_rating' => null,
                'pvp_rank' => null,
            ];
        }
        
        foreach ($pvpRank as $player) {
            $userId = $player['user_id'];
            if (isset($combined[$userId])) {
                $combined[$userId]['pvp_rating'] = $player['score'];
                $combined[$userId]['pvp_rank'] = $player['position'];
            } else {
                $combined[$userId] = [
                    'user_id' => $userId,
                    'username' => $player['username'],
                    'level' => $player['level'] ?? 1,
                    'level_rank' => null,
                    'pvp_rating' => $player['score'],
                    'pvp_rank' => $player['position'],
                ];
            }
        }
        
        return array_values($combined);
    }
    
    public function getTopPlayersByChampionCount(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT u.id as user_id, u.username, u.level, COUNT(uc.id) as champion_count
            FROM users u
            LEFT JOIN user_champions uc ON u.id = uc.user_id AND uc.deleted_at IS NULL
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY champion_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getTopPlayersByGold(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT u.id as user_id, u.username, u.level, ur.gold
            FROM users u
            JOIN user_resources ur ON u.id = ur.user_id
            WHERE u.deleted_at IS NULL
            ORDER BY ur.gold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getTopPlayersByWinRate(int $minBattles = 10, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT u.id as user_id, u.username, u.level,
                   COUNT(b.id) as total_battles,
                   SUM(CASE WHEN b.result = 'victory' THEN 1 ELSE 0 END) as wins,
                   ROUND(SUM(CASE WHEN b.result = 'victory' THEN 1 ELSE 0 END) * 100.0 / COUNT(b.id), 1) as win_rate
            FROM users u
            JOIN battles b ON u.id = b.user_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            HAVING COUNT(b.id) >= ?
            ORDER BY win_rate DESC
            LIMIT ?
        ");
        $stmt->execute([$minBattles, $limit]);
        return $stmt->fetchAll();
    }
}
