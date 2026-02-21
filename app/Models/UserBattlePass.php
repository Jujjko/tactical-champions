<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserBattlePass extends Model {
    protected string $table = 'user_battle_passes';
    
    public function getProgress(int $userId, int $seasonId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ? AND season_id = ?
        ");
        $stmt->execute([$userId, $seasonId]);
        return $stmt->fetch() ?: null;
    }
    
    public function getOrCreate(int $userId, int $seasonId): array {
        $progress = $this->getProgress($userId, $seasonId);
        
        if (!$progress) {
            $this->create([
                'user_id' => $userId,
                'season_id' => $seasonId,
                'level' => 1,
                'experience' => 0
            ]);
            $progress = $this->getProgress($userId, $seasonId);
        }
        
        return $progress;
    }
    
    public function addExperience(int $userId, int $seasonId, int $exp): void {
        $progress = $this->getOrCreate($userId, $seasonId);
        
        $newExp = $progress['experience'] + $exp;
        $newLevel = $progress['level'];
        
        while ($newExp >= $this->getExpForLevel($newLevel + 1)) {
            $newExp -= $this->getExpForLevel($newLevel + 1);
            $newLevel++;
        }
        
        $this->update($progress['id'], [
            'experience' => $newExp,
            'level' => $newLevel
        ]);
    }
    
    private function getExpForLevel(int $level): int {
        return 100 + ($level * 50);
    }
}
