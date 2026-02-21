<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Quest extends Model {
    protected string $table = 'quests';
    
    public function getDailyQuests(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE type = ? AND is_active = TRUE 
            ORDER BY sort_order
        ");
        $stmt->execute(['daily']);
        return $stmt->fetchAll();
    }
    
    public function getWeeklyQuests(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE type = ? AND is_active = TRUE 
            ORDER BY sort_order
        ");
        $stmt->execute(['weekly']);
        return $stmt->fetchAll();
    }
    
    public function getActiveQuests(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE is_active = TRUE 
            ORDER BY type, sort_order
        ");
        $stmt->execute([]);
        return $stmt->fetchAll();
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