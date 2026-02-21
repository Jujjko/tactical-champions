<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PvpRating;
use App\Models\Resource;
use Core\Database;

class SeasonService
{
    private Database $db;
    private PvpRating $pvpRatingModel;
    private Resource $resourceModel;
    
    private const RANK_REWARDS = [
        1 => ['gold' => 10000, 'gems' => 100, 'lootboxes' => 5],
        2 => ['gold' => 7500, 'gems' => 75, 'lootboxes' => 4],
        3 => ['gold' => 5000, 'gems' => 50, 'lootboxes' => 3],
        ['gold' => 4000, 'gems' => 40, 'lootboxes' => 2],
        ['gold' => 3500, 'gems' => 35, 'lootboxes' => 2],
        ['gold' => 3000, 'gems' => 30, 'lootboxes' => 2],
        ['gold' => 2500, 'gems' => 25, 'lootboxes' => 1],
        ['gold' => 2000, 'gems' => 20, 'lootboxes' => 1],
        ['gold' => 1500, 'gems' => 15, 'lootboxes' => 1],
        ['gold' => 1000, 'gems' => 10, 'lootboxes' => 1],
    ];
    
    private const PARTICIPATION_REWARD = ['gold' => 500, 'gems' => 5, 'lootboxes' => 0];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pvpRatingModel = new PvpRating();
        $this->resourceModel = new Resource();
    }
    
    public function getActiveSeason(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM pvp_seasons 
            WHERE is_active = TRUE 
              AND starts_at <= NOW() 
              AND ends_at > NOW()
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    public function getSeasonById(int $seasonId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pvp_seasons WHERE id = ?");
        $stmt->execute([$seasonId]);
        return $stmt->fetch() ?: null;
    }
    
    public function getSeasons(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM pvp_seasons 
            ORDER BY starts_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function calculateSeasonRewards(int $seasonId): int
    {
        $season = $this->getSeasonById($seasonId);
        
        if (!$season) {
            return 0;
        }
        
        $leaderboard = $this->pvpRatingModel->getLeaderboard(1000);
        $distributed = 0;
        
        foreach ($leaderboard as $index => $player) {
            $rank = $index + 1;
            
            $rewards = $rank <= 10 
                ? (self::RANK_REWARDS[$rank] ?? self::RANK_REWARDS[10])
                : self::PARTICIPATION_REWARD;
            
            $existingReward = $this->db->prepare("
                SELECT id FROM season_rewards 
                WHERE season_id = ? AND user_id = ?
            ");
            $existingReward->execute([$seasonId, $player['user_id']]);
            
            if ($existingReward->fetch()) {
                continue;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO season_rewards 
                (season_id, user_id, final_rank, final_rating, reward_gold, reward_gems, reward_lootboxes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $seasonId,
                $player['user_id'],
                $rank,
                $player['rating'],
                $rewards['gold'],
                $rewards['gems'],
                $rewards['lootboxes'],
            ]);
            
            $distributed++;
        }
        
        $this->db->prepare("
            UPDATE pvp_seasons SET rewards_distributed = TRUE WHERE id = ?
        ")->execute([$seasonId]);
        
        return $distributed;
    }
    
    public function endSeason(int $seasonId): bool
    {
        $season = $this->getSeasonById($seasonId);
        
        if (!$season || !$season['is_active']) {
            return false;
        }
        
        $this->calculateSeasonRewards($seasonId);
        
        $this->db->prepare("
            UPDATE pvp_seasons SET is_active = FALSE WHERE id = ?
        ")->execute([$seasonId]);
        
        $this->db->prepare("
            UPDATE pvp_ratings SET rating = 1000, wins = 0, losses = 0, current_streak = 0
        ")->execute();
        
        return true;
    }
    
    public function startNewSeason(string $name, string $description, int $durationDays = 30): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO pvp_seasons (name, description, is_active, starts_at, ends_at)
            VALUES (?, ?, TRUE, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
        ");
        
        $stmt->execute([$name, $description, $durationDays]);
        
        return (int)$this->db->lastInsertId();
    }
    
    public function getUserSeasonRewards(int $userId, ?int $seasonId = null): array
    {
        if ($seasonId) {
            $stmt = $this->db->prepare("
                SELECT sr.*, ps.name as season_name
                FROM season_rewards sr
                JOIN pvp_seasons ps ON sr.season_id = ps.id
                WHERE sr.user_id = ? AND sr.season_id = ?
            ");
            $stmt->execute([$userId, $seasonId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT sr.*, ps.name as season_name
                FROM season_rewards sr
                JOIN pvp_seasons ps ON sr.season_id = ps.id
                WHERE sr.user_id = ?
                ORDER BY sr.created_at DESC
            ");
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    public function claimSeasonReward(int $userId, int $rewardId): array
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, ps.name as season_name
            FROM season_rewards sr
            JOIN pvp_seasons ps ON sr.season_id = ps.id
            WHERE sr.id = ? AND sr.user_id = ? AND sr.claimed = FALSE
        ");
        $stmt->execute([$rewardId, $userId]);
        $reward = $stmt->fetch();
        
        if (!$reward) {
            return ['success' => false, 'error' => 'Reward not found or already claimed'];
        }
        
        $resources = $this->resourceModel->getUserResources($userId);
        
        $this->resourceModel->update($resources['id'], [
            'gold' => $resources['gold'] + $reward['reward_gold'],
            'gems' => $resources['gems'] + $reward['reward_gems'],
        ]);
        
        if ($reward['reward_lootboxes'] > 0) {
            for ($i = 0; $i < $reward['reward_lootboxes']; $i++) {
                $this->resourceModel->addLootbox($userId, 'silver');
            }
        }
        
        $this->db->prepare("
            UPDATE season_rewards SET claimed = TRUE, claimed_at = NOW() WHERE id = ?
        ")->execute([$rewardId]);
        
        return [
            'success' => true,
            'rewards' => [
                'gold' => $reward['reward_gold'],
                'gems' => $reward['reward_gems'],
                'lootboxes' => $reward['reward_lootboxes'],
            ],
        ];
    }
    
    public function checkAndEndSeason(): bool
    {
        $season = $this->getActiveSeason();
        
        if (!$season) {
            return false;
        }
        
        if (strtotime($season['ends_at']) <= time()) {
            return $this->endSeason($season['id']);
        }
        
        return false;
    }
    
    public function getTimeRemaining(int $seasonId): array
    {
        $season = $this->getSeasonById($seasonId);
        
        if (!$season) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        
        $diff = strtotime($season['ends_at']) - time();
        
        if ($diff <= 0) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        
        return [
            'days' => (int)floor($diff / 86400),
            'hours' => (int)floor(($diff % 86400) / 3600),
            'minutes' => (int)floor(($diff % 3600) / 60),
            'seconds' => $diff % 60,
        ];
    }
}