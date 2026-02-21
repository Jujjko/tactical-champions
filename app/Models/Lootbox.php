<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Lootbox extends Model {
    protected string $table = 'user_lootboxes';
    
    public function getUserLootboxes(int $userId, bool $openedOnly = false): array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        if (!$openedOnly) {
            $sql .= " AND opened = 0";
        }
        $sql .= " ORDER BY acquired_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function createLootbox(int $userId, string $type = 'bronze'): int {
        return $this->create([
            'user_id' => $userId,
            'lootbox_type' => $type,
            'opened' => 0
        ]);
    }
    
    public function openLootbox(int $lootboxId): array {
        $lootbox = $this->findById($lootboxId);
        
        if (!$lootbox || $lootbox['opened']) {
            return [];
        }
        
        // Determine rewards based on lootbox type
        $rewards = $this->generateRewards($lootbox['lootbox_type']);
        
        // Mark as opened
        $this->update($lootboxId, [
            'opened' => 1,
            'opened_at' => date('Y-m-d H:i:s')
        ]);
        
        return $rewards;
    }
    
    private function generateRewards(string $type): array {
        $multipliers = [
            'bronze' => 1,
            'silver' => 2,
            'gold' => 3,
            'diamond' => 5
        ];
        
        $mult = $multipliers[$type] ?? 1;
        
        return [
            'gold' => rand(50, 150) * $mult,
            'gems' => rand(5, 20) * $mult,
            'champion' => rand(1, 100) <= (20 * $mult) // Champion chance
        ];
    }
}