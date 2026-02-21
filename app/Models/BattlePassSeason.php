<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class BattlePassSeason extends Model {
    protected string $table = 'battle_pass_seasons';
    
    public function getActiveSeason(): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE is_active = TRUE 
            AND starts_at <= NOW() 
            AND ends_at >= NOW()
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    public function getRewards(int $seasonId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM battle_pass_rewards
            WHERE season_id = ?
            ORDER BY level
        ");
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }
}
