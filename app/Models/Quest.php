<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Quest extends Model {
    protected string $table = 'quests';
    
    public function getDailyQuests(): array {
        return $this->where('type', 'daily', 'AND is_active = TRUE ORDER BY sort_order');
    }
    
    public function getWeeklyQuests(): array {
        return $this->where('type', 'weekly', 'AND is_active = TRUE ORDER BY sort_order');
    }
    
    public function getActiveQuests(): array {
        return $this->where('is_active', true, 'ORDER BY type, sort_order');
    }
    
    public function getByType(string $type): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE type = ? AND is_active = TRUE
            ORDER BY sort_order
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
}
