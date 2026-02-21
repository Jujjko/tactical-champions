<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Achievement extends Model {
    protected string $table = 'achievements';
    
    public function getByCategory(string $category): array {
        return $this->where('category', $category);
    }
    
    public function getUserAchievements(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT a.*, ua.progress, ua.completed, ua.completed_at
            FROM {$this->table} a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            WHERE a.is_hidden = FALSE OR ua.completed = TRUE
            ORDER BY a.category, a.requirement_value
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getCompletedCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM user_achievements 
            WHERE user_id = ? AND completed = TRUE
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function getTotalPoints(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(a.reward_gold + a.reward_gems * 10), 0)
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND ua.completed = TRUE
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
