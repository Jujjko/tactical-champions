<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class PvpChallenge extends Model {
    protected string $table = 'pvp_challenges';
    
    public function getPendingChallenges(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT pc.*, 
                   u1.username as challenger_name,
                   u2.username as defender_name,
                   uc.level as challenger_champion_level,
                   c.name as challenger_champion_name
            FROM {$this->table} pc
            JOIN users u1 ON pc.challenger_id = u1.id
            JOIN users u2 ON pc.defender_id = u2.id
            JOIN user_champions uc ON pc.challenger_champion_id = uc.id
            JOIN champions c ON uc.champion_id = c.id
            WHERE pc.defender_id = ? AND pc.status = 'pending' AND pc.expires_at > NOW()
            ORDER BY pc.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getSentChallenges(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT pc.*, 
                   u.username as defender_name,
                   uc.level as defender_champion_level
            FROM {$this->table} pc
            JOIN users u ON pc.defender_id = u.id
            LEFT JOIN user_champions uc ON pc.defender_champion_id = uc.id
            WHERE pc.challenger_id = ?
            ORDER BY pc.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function createChallenge(int $challengerId, int $challengerChampionId, int $defenderId, int $goldReward = 100): int {
        return $this->create([
            'challenger_id' => $challengerId,
            'challenger_champion_id' => $challengerChampionId,
            'defender_id' => $defenderId,
            'rewards_gold' => $goldReward,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]);
    }
    
    public function acceptChallenge(int $challengeId, int $defenderChampionId): bool {
        return $this->update($challengeId, [
            'status' => 'accepted',
            'defender_champion_id' => $defenderChampionId,
            'responded_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function declineChallenge(int $challengeId): bool {
        return $this->update($challengeId, [
            'status' => 'declined',
            'responded_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function completeChallenge(int $challengeId, int $winnerId): bool {
        return $this->update($challengeId, [
            'status' => 'completed',
            'winner_id' => $winnerId
        ]);
    }
}
