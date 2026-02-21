<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserPurchase extends Model {
    protected string $table = 'user_purchases';
    
    public function getUserPurchases(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT up.*, si.name, si.icon, si.category
            FROM {$this->table} up
            LEFT JOIN shop_items si ON up.shop_item_id = si.id
            WHERE up.user_id = ?
            ORDER BY up.purchased_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getTotalSpent(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(total_gems), 0) as total_gems,
                COALESCE(SUM(total_gold), 0) as total_gold
            FROM {$this->table}
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function recordPurchase(int $userId, int $itemId, string $itemName, int $quantity, int $gemsSpent, int $goldSpent): int {
        return $this->create([
            'user_id' => $userId,
            'shop_item_id' => $itemId,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'total_gems' => $gemsSpent,
            'total_gold' => $goldSpent
        ]);
    }
}
