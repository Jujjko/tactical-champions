<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UserChampion;
use App\Models\Resource;
use App\Models\Champion;
use Core\Database;

class ChampionService {
    private UserChampion $userChampionModel;
    private Champion $championModel;
    private Resource $resourceModel;
    private Database $db;
    
    private const XP_PER_LEVEL = 50;
    private const STAT_GROWTH = [
        'health' => 1.10,
        'attack' => 1.10,
        'defense' => 1.10,
        'speed' => 1.05
    ];
    
    private const UPGRADE_COSTS = [
        'gold_per_level' => 100,
        'gold_base' => 50
    ];
    
    public function __construct() {
        $this->userChampionModel = new UserChampion();
        $this->championModel = new Champion();
        $this->resourceModel = new Resource();
        $this->db = Database::getInstance();
    }
    
    public function getUserChampion(int $userChampionId, int $userId): ?array {
        return $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
    }
    
    public function getUpgradeInfo(int $userChampionId, int $userId): ?array {
        $champion = $this->getUserChampion($userChampionId, $userId);
        
        if (!$champion) {
            return null;
        }
        
        $level = $champion['level'];
        $currentExp = $champion['experience'];
        $expToNextLevel = $level * self::XP_PER_LEVEL;
        
        $baseChampion = $this->championModel->findById($champion['champion_id']);
        
        return [
            'champion' => $champion,
            'base_stats' => [
                'health' => $baseChampion['base_health'],
                'attack' => $baseChampion['base_attack'],
                'defense' => $baseChampion['base_defense'],
                'speed' => $baseChampion['base_speed']
            ],
            'current_exp' => $currentExp,
            'exp_to_next_level' => $expToNextLevel,
            'exp_progress' => ($currentExp / $expToNextLevel) * 100,
            'next_level_stats' => $this->calculateNextLevelStats($champion),
            'gold_upgrade_cost' => $this->calculateGoldUpgradeCost($level),
            'can_upgrade_with_gold' => $this->canUpgradeWithGold($userId, $level),
            'stat_growth' => self::STAT_GROWTH
        ];
    }
    
    public function upgradeWithGold(int $userChampionId, int $userId): array {
        $champion = $this->getUserChampion($userChampionId, $userId);
        
        if (!$champion) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        $cost = $this->calculateGoldUpgradeCost($champion['level']);
        $resources = $this->resourceModel->getUserResources($userId);
        
        if ($resources['gold'] < $cost) {
            return ['success' => false, 'error' => 'Not enough gold'];
        }
        
        $this->db->beginTransaction();
        
        try {
            $this->resourceModel->addGold($userId, -$cost);
            $this->levelUp($userChampionId);
            $this->db->commit();
            
            $updatedChampion = $this->getUserChampion($userChampionId, $userId);
            
            return [
                'success' => true,
                'champion' => $updatedChampion,
                'gold_spent' => $cost
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => 'Upgrade failed'];
        }
    }
    
    public function addExperience(int $userChampionId, int $exp): array {
        $champion = $this->userChampionModel->findById($userChampionId);
        
        if (!$champion) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        $oldLevel = $champion['level'];
        $this->userChampionModel->addExperience($userChampionId, $exp);
        
        $updatedChampion = $this->userChampionModel->findById($userChampionId);
        $levelsGained = $updatedChampion['level'] - $oldLevel;
        
        return [
            'success' => true,
            'exp_gained' => $exp,
            'levels_gained' => $levelsGained,
            'champion' => $updatedChampion
        ];
    }
    
    private function levelUp(int $userChampionId): void {
        $champion = $this->userChampionModel->findById($userChampionId);
        
        $newLevel = $champion['level'] + 1;
        $newHealth = (int)($champion['health'] * self::STAT_GROWTH['health']);
        $newAttack = (int)($champion['attack'] * self::STAT_GROWTH['attack']);
        $newDefense = (int)($champion['defense'] * self::STAT_GROWTH['defense']);
        $newSpeed = (int)($champion['speed'] * self::STAT_GROWTH['speed']);
        
        $this->userChampionModel->update($userChampionId, [
            'level' => $newLevel,
            'experience' => 0,
            'health' => $newHealth,
            'attack' => $newAttack,
            'defense' => $newDefense,
            'speed' => $newSpeed
        ]);
    }
    
    private function calculateNextLevelStats(array $champion): array {
        return [
            'health' => (int)($champion['health'] * self::STAT_GROWTH['health']),
            'attack' => (int)($champion['attack'] * self::STAT_GROWTH['attack']),
            'defense' => (int)($champion['defense'] * self::STAT_GROWTH['defense']),
            'speed' => (int)($champion['speed'] * self::STAT_GROWTH['speed'])
        ];
    }
    
    private function calculateGoldUpgradeCost(int $level): int {
        return self::UPGRADE_COSTS['gold_base'] + ($level * self::UPGRADE_COSTS['gold_per_level']);
    }
    
    private function canUpgradeWithGold(int $userId, int $level): bool {
        $resources = $this->resourceModel->getUserResources($userId);
        $cost = $this->calculateGoldUpgradeCost($level);
        return $resources['gold'] >= $cost;
    }
    
    public function getChampionBattleStats(int $userChampionId, int $userId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_battles,
                SUM(CASE WHEN b.result = 'victory' THEN 1 ELSE 0 END) as victories,
                AVG(b.duration_seconds) as avg_duration
            FROM battles b
            WHERE b.user_id = ?
            AND JSON_CONTAINS(b.champions_used, CAST(? AS JSON), '$')
        ");
        
        $stmt->execute([$userId, json_encode($userChampionId)]);
        return $stmt->fetch() ?: [];
    }
}