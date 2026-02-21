<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class PvpRewardsLog extends Model {
    protected string $table = 'pvp_rewards_log';
    
    public function getUserRewards(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT prl.*, pb.attacker_id, pb.defender_id, pb.winner_id,
                   u1.username as attacker_name, u2.username as defender_name
            FROM {$this->table} prl
            JOIN pvp_battles pb ON prl.battle_id = pb.id
            JOIN users u1 ON pb.attacker_id = u1.id
            JOIN users u2 ON pb.defender_id = u2.id
            WHERE prl.user_id = ?
            ORDER BY prl.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getTotalRewards(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(gold_earned) as total_gold,
                SUM(gems_earned) as total_gems,
                SUM(shard_earned) as total_shards,
                COUNT(*) as total_battles
            FROM {$this->table}
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function getRecentShardDrops(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT prl.*, u.username
            FROM {$this->table} prl
            JOIN users u ON prl.user_id = u.id
            WHERE prl.shard_earned = 1
            ORDER BY prl.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
