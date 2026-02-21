<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Tutorial extends Model
{
    protected string $table = 'user_tutorials';

    public function getProgress(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $progress = $stmt->fetchAll();
        
        $result = [];
        foreach ($progress as $p) {
            $result[$p['tutorial_step']] = $p;
        }
        
        return $result;
    }

    public function completeStep(int $userId, string $step): bool
    {
        $existing = $this->db->prepare("
            SELECT id FROM {$this->table} WHERE user_id = ? AND tutorial_step = ?
        ");
        $existing->execute([$userId, $step]);
        
        if ($existing->fetch()) {
            return true;
        }
        
        return $this->create([
            'user_id' => $userId,
            'tutorial_step' => $step,
            'completed_at' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    public function isCompleted(int $userId, string $step): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table} WHERE user_id = ? AND tutorial_step = ?
        ");
        $stmt->execute([$userId, $step]);
        return $stmt->fetch() !== false;
    }

    public function hasCompletedAll(int $userId): bool
    {
        $requiredSteps = ['welcome', 'champion', 'battle', 'upgrade'];
        
        foreach ($requiredSteps as $step) {
            if (!$this->isCompleted($userId, $step)) {
                return false;
            }
        }
        
        return true;
    }

    public function getNextStep(int $userId): ?string
    {
        $steps = ['welcome', 'champion', 'battle', 'upgrade'];
        
        foreach ($steps as $step) {
            if (!$this->isCompleted($userId, $step)) {
                return $step;
            }
        }
        
        return null;
    }

    public function getCompletionPercent(int $userId): int
    {
        $steps = ['welcome', 'champion', 'battle', 'upgrade'];
        $completed = 0;
        
        foreach ($steps as $step) {
            if ($this->isCompleted($userId, $step)) {
                $completed++;
            }
        }
        
        return (int)(($completed / count($steps)) * 100);
    }
}
