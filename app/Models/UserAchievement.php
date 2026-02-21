<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserAchievement extends Model {
    protected string $table = 'user_achievements';
    
    public function updateProgress(int $userId, int $achievementId, int $progress): bool {
        $existing = $this->db->prepare("
            SELECT * FROM {$this->table} WHERE user_id = ? AND achievement_id = ?
        ");
        $existing->execute([$userId, $achievementId]);
        $record = $existing->fetch();
        
        $achievementModel = new Achievement();
        $achievement = $achievementModel->findById($achievementId);
        
        if (!$achievement) return false;
        
        $completed = $progress >= $achievement['requirement_value'];
        
        if ($record) {
            if ($progress <= $record['progress']) return true;
            
            return $this->update($record['id'], [
                'progress' => $progress,
                'completed' => $completed,
                'completed_at' => $completed ? date('Y-m-d H:i:s') : null
            ]);
        }
        
        $this->create([
            'user_id' => $userId,
            'achievement_id' => $achievementId,
            'progress' => $progress,
            'completed' => $completed,
            'completed_at' => $completed ? date('Y-m-d H:i:s') : null
        ]);
        
        return true;
    }
    
    public function claimReward(int $userId, int $achievementId): bool {
        $stmt = $this->db->prepare("
            SELECT ua.*, a.reward_gold, a.reward_gems, a.reward_experience
            FROM {$this->table} ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND ua.achievement_id = ? AND ua.completed = TRUE
        ");
        $stmt->execute([$userId, $achievementId]);
        $record = $stmt->fetch();
        
        if (!$record) return false;
        
        $resourceModel = new Resource();
        $resourceModel->addGold($userId, $record['reward_gold']);
        $resourceModel->addGems($userId, $record['reward_gems']);
        
        return true;
    }
}
