<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Lootbox extends Model {
    protected string $table = 'user_lootboxes';
    
    public function getUserLootboxes(int $userId, bool $unopenedOnly = true): array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        if ($unopenedOnly) {
            $sql .= " AND opened = 0";
        }
        $sql .= " ORDER BY acquired_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getLootboxCounts(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT lootbox_type, COUNT(*) as count 
            FROM {$this->table} 
            WHERE user_id = ? AND opened = 0 
            GROUP BY lootbox_type
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        $counts = [
            'bronze' => 0,
            'silver' => 0,
            'gold' => 0,
            'diamond' => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['lootbox_type']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        
        return $counts;
    }
    
    public function getLootboxIdsByType(int $userId, string $type, int $limit): array {
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table} 
            WHERE user_id = ? AND lootbox_type = ? AND opened = 0 
            LIMIT ?
        ");
        $stmt->execute([$userId, $type, $limit]);
        return array_column($stmt->fetchAll(), 'id');
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
        
        $rewards = $this->generateRewards($lootbox['lootbox_type']);
        
        $this->update($lootboxId, [
            'opened' => 1,
            'opened_at' => date('Y-m-d H:i:s')
        ]);
        
        return array_merge($rewards, ['type' => $lootbox['lootbox_type']]);
    }
    
    public function openMultiple(array $lootboxIds): array {
        $results = [
            'total_gold' => 0,
            'total_gems' => 0,
            'champions_won' => 0,
            'champions' => [],
            'opened_count' => 0,
            'by_type' => []
        ];
        
        foreach ($lootboxIds as $id) {
            $rewards = $this->openLootbox($id);
            if (!empty($rewards)) {
                $results['total_gold'] += $rewards['gold'];
                $results['total_gems'] += $rewards['gems'];
                if ($rewards['champion']) {
                    $results['champions_won']++;
                }
                $results['opened_count']++;
                
                $type = $rewards['type'];
                if (!isset($results['by_type'][$type])) {
                    $results['by_type'][$type] = ['gold' => 0, 'gems' => 0, 'count' => 0];
                }
                $results['by_type'][$type]['gold'] += $rewards['gold'];
                $results['by_type'][$type]['gems'] += $rewards['gems'];
                $results['by_type'][$type]['count']++;
            }
        }
        
        return $results;
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
            'gold' => random_int(50, 150) * $mult,
            'gems' => random_int(5, 20) * $mult,
            'champion' => random_int(1, 100) <= (20 * $mult)
        ];
    }
}