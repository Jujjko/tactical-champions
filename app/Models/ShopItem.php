<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class ShopItem extends Model {
    protected string $table = 'shop_items';
    protected bool $softDeletes = true;
    
    public function getActiveItems(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE is_active = TRUE AND deleted_at IS NULL
            ORDER BY sort_order, category
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getFeaturedItems(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE is_active = TRUE AND is_featured = TRUE AND deleted_at IS NULL
            ORDER BY sort_order
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByCategory(string $category): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE category = ? AND is_active = TRUE AND deleted_at IS NULL
            ORDER BY sort_order
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    public function incrementSold(int $itemId): void {
        $this->db->prepare("
            UPDATE {$this->table} SET sold_count = sold_count + 1 WHERE id = ?
        ")->execute([$itemId]);
    }
}
