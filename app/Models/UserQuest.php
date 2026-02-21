<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserQuest extends Model {
    protected string $table = 'user_quests';
    
    public function getUserDailyQuests(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT q.*, uq.progress, uq.completed, uq.claimed, uq.id as user_quest_id
            FROM quests q
            LEFT JOIN user_quests uq ON q.id = uq.quest_id AND uq.user_id = ?
            WHERE q.type = 'daily' AND q.is_active = TRUE
            ORDER BY q.sort_order
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getUserWeeklyQuests(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT q.*, uq.progress, uq.completed, uq.claimed, uq.id as user_quest_id
            FROM quests q
            LEFT JOIN user_quests uq ON q.id = uq.quest_id AND uq.user_id = ?
            WHERE q.type = 'weekly' AND q.is_active = TRUE
            ORDER BY q.sort_order
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getAllUserQuests(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT q.*, uq.progress, uq.completed, uq.claimed, uq.id as user_quest_id
            FROM quests q
            LEFT JOIN user_quests uq ON q.id = uq.quest_id AND uq.user_id = ?
            WHERE q.is_active = TRUE
            ORDER BY q.type, q.sort_order
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function initializeUserQuests(int $userId): void {
        $questModel = new Quest();
        $quests = $questModel->getActiveQuests();
        
        foreach ($quests as $quest) {
            $existing = $this->db->prepare("
                SELECT 1 FROM {$this->table} WHERE user_id = ? AND quest_id = ?
            ");
            $existing->execute([$userId, $quest['id']]);
            
            if (!$existing->fetch()) {
                $this->create([
                    'user_id' => $userId,
                    'quest_id' => $quest['id'],
                    'progress' => 0,
                    'completed' => false,
                    'claimed' => false
                ]);
            }
        }
    }
    
    public function updateProgress(int $userId, string $requirementType, int $amount = 1): void {
        $this->initializeUserQuests($userId);
        
        $stmt = $this->db->prepare("
            SELECT uq.*, q.requirement_value, q.requirement_type
            FROM {$this->table} uq
            JOIN quests q ON uq.quest_id = q.id
            WHERE uq.user_id = ? AND q.requirement_type = ? AND uq.completed = FALSE
        ");
        $stmt->execute([$userId, $requirementType]);
        $quests = $stmt->fetchAll();
        
        foreach ($quests as $quest) {
            $newProgress = min($quest['progress'] + $amount, $quest['requirement_value']);
            $completed = $newProgress >= $quest['requirement_value'];
            
            $this->update($quest['id'], [
                'progress' => $newProgress,
                'completed' => $completed,
                'completed_at' => $completed ? date('Y-m-d H:i:s') : null
            ]);
        }
    }
    
    public function claimReward(int $userId, int $userQuestId): array {
        $stmt = $this->db->prepare("
            SELECT uq.*, q.name, q.reward_gold, q.reward_gems, q.reward_experience, q.reward_battle_pass_xp
            FROM {$this->table} uq
            JOIN quests q ON uq.quest_id = q.id
            WHERE uq.id = ? AND uq.user_id = ? AND uq.completed = TRUE AND uq.claimed = FALSE
        ");
        $stmt->execute([$userQuestId, $userId]);
        $quest = $stmt->fetch();
        
        if (!$quest) {
            return ['success' => false, 'error' => 'Quest not found or already claimed'];
        }
        
        $this->update($quest['id'], [
            'claimed' => true,
            'claimed_at' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'rewards' => [
                'gold' => $quest['reward_gold'],
                'gems' => $quest['reward_gems'],
                'experience' => $quest['reward_experience'],
                'battle_pass_xp' => $quest['reward_battle_pass_xp']
            ]
        ];
    }
    
    public function resetDailyQuests(): void {
        $stmt = $this->db->prepare("
            DELETE uq FROM {$this->table} uq
            JOIN quests q ON uq.quest_id = q.id
            WHERE q.type = 'daily'
        ");
        $stmt->execute();
    }
    
    public function resetWeeklyQuests(): void {
        $stmt = $this->db->prepare("
            DELETE uq FROM {$this->table} uq
            JOIN quests q ON uq.quest_id = q.id
            WHERE q.type = 'weekly'
        ");
        $stmt->execute();
    }
    
    public function getCompletedCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND completed = TRUE
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function getUnclaimedCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND completed = TRUE AND claimed = FALSE
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
