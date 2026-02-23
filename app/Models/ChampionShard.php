<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class ChampionShard extends Model {
    protected string $table = 'champion_shards';
    
    public function getAmount(int $userId, int $championId): int {
        $stmt = $this->db->prepare("SELECT amount FROM {$this->table} WHERE user_id = ? AND champion_id = ?");
        $stmt->execute([$userId, $championId]);
        return (int)($stmt->fetchColumn() ?? 0);
    }
    
    public function addShards(int $userId, int $championId, int $amount): bool {
        if ($amount <= 0) return false;
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, champion_id, amount)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE amount = amount + ?
        ");
        return $stmt->execute([$userId, $championId, $amount, $amount]);
    }
    
    public function removeShards(int $userId, int $championId, int $amount): bool {
        if ($amount <= 0) return false;
        
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET amount = amount - ? 
            WHERE user_id = ? AND champion_id = ? AND amount >= ?
        ");
        $stmt->execute([$amount, $userId, $championId, $amount]);
        return $stmt->rowCount() > 0;
    }
    
    public function getAllForUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT cs.*, c.name, c.tier, c.image_url
            FROM champion_shards cs
            JOIN champions c ON cs.champion_id = c.id
            WHERE cs.user_id = ? AND cs.amount > 0
            ORDER BY c.tier DESC, cs.amount DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getTopShards(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT cs.*, c.name, c.tier, uc.stars, uc.star_tier
            FROM champion_shards cs
            JOIN champions c ON cs.champion_id = c.id
            LEFT JOIN user_champions uc ON uc.champion_id = cs.champion_id AND uc.user_id = cs.user_id
            WHERE cs.user_id = ? AND cs.amount > 0
            ORDER BY cs.amount DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
