<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Mission extends Model {
    protected string $table = 'missions';
    protected bool $softDeletes = true;
    
    public function getAvailableMissions(int $userLevel): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE is_active = 1 AND required_level <= ? AND deleted_at IS NULL
            ORDER BY difficulty, required_level
        ");
        $stmt->execute([$userLevel]);
        return $stmt->fetchAll();
    }
    
    public function completeMission(int $userId, int $missionId): array {
        $mission = $this->findById($missionId);
        
        $lootboxEarned = (rand(1, 10000) / 100) <= $mission['lootbox_chance'];
        
        $stmt = $this->db->prepare("
            INSERT INTO mission_completions 
            (user_id, mission_id, gold_earned, experience_earned, lootbox_earned)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $missionId,
            $mission['gold_reward'],
            $mission['experience_reward'],
            $lootboxEarned ? 1 : 0
        ]);
        
        return [
            'gold' => $mission['gold_reward'],
            'experience' => $mission['experience_reward'],
            'lootbox' => $lootboxEarned
        ];
    }
    
    public function getUserCompletions(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT m.name, mc.completed_at, mc.gold_earned, mc.experience_earned
            FROM mission_completions mc
            JOIN missions m ON mc.mission_id = m.id
            WHERE mc.user_id = ?
            ORDER BY mc.completed_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}