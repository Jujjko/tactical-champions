<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Battle extends Model {
    protected string $table = 'battles';
    
    public function createBattle(int $userId, ?int $missionId, array $champions): int {
        return $this->create([
            'user_id' => $userId,
            'mission_id' => $missionId,
            'result' => 'victory',
            'duration_seconds' => 0,
            'champions_used' => json_encode($champions)
        ]);
    }
    
    public function completeBattle(int $battleId, string $result, int $duration, array $rewards): void {
        $this->update($battleId, [
            'result' => $result,
            'duration_seconds' => $duration,
            'rewards_earned' => json_encode($rewards)
        ]);
    }
    
    public function getUserBattles(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT b.*, m.name as mission_name
            FROM {$this->table} b
            LEFT JOIN missions m ON b.mission_id = m.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getRecentBattles(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT b.*, m.name as mission_name, m.difficulty
            FROM {$this->table} b
            LEFT JOIN missions m ON b.mission_id = m.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getUserStats(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_battles,
                SUM(CASE WHEN result = 'victory' THEN 1 ELSE 0 END) as victories,
                SUM(CASE WHEN result = 'defeat' THEN 1 ELSE 0 END) as defeats,
                AVG(duration_seconds) as avg_duration
            FROM {$this->table}
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }
    
    public function getWinRate(int $userId): float {
        $stats = $this->getUserStats($userId);
        if ($stats['total_battles'] == 0) {
            return 0.0;
        }
        return round(($stats['victories'] / $stats['total_battles']) * 100, 1);
    }
    
    public function countByUser(?int $userId = null): int {
        if ($userId) {
            return $this->count(['user_id' => $userId]);
        }
        return $this->count();
    }
}