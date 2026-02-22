<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use App\Models\Achievement;

class UserAchievement extends Model {
    protected string $table = 'user_achievements';
    
    public function updateProgress(int $userId, int $achievementId, int $progress): bool {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$userId, $achievementId]);
        $existing = $stmt->fetch();
        
        $achievementModel = new Achievement();
        $achievement = $achievementModel->findById($achievementId);
        
        if (!$achievement) {
            return false;
        }
        
        $completed = $progress >= $achievement['requirement_value'];
        
        if ($existing) {
            if ($progress <= ($existing['progress'] ?? 0)) {
                return true;
            }
            
            $updateData = [
                'progress' => $progress,
                'completed' => $completed ? 1 : 0
            ];
            
            if ($completed && empty($existing['completed_at'])) {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }
            
            return $this->update((int)$existing['id'], $updateData);
        }
        
        $this->create([
            'user_id' => $userId,
            'achievement_id' => $achievementId,
            'progress' => $progress,
            'completed' => $completed ? 1 : 0,
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
        
        if (!$record) {
            return false;
        }
        
        $resourceModel = new Resource();
        if ($record['reward_gold'] > 0) {
            $resourceModel->addGold($userId, (int)$record['reward_gold']);
        }
        if ($record['reward_gems'] > 0) {
            $resourceModel->addGems($userId, (int)$record['reward_gems']);
        }
        
        return true;
    }
}
