<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class TournamentParticipant extends Model {
    protected string $table = 'tournament_participants';
    
    public function getByTournament(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT tp.*, u.username, u.level, pr.rating
            FROM {$this->table} tp
            JOIN users u ON tp.user_id = u.id
            LEFT JOIN pvp_ratings pr ON u.id = pr.user_id
            WHERE tp.tournament_id = ?
            ORDER BY tp.seed ASC
        ");
        $stmt->execute([$tournamentId]);
        return $stmt->fetchAll();
    }
    
    public function getActiveParticipants(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT tp.*, u.username, pr.rating
            FROM {$this->table} tp
            JOIN users u ON tp.user_id = u.id
            LEFT JOIN pvp_ratings pr ON u.id = pr.user_id
            WHERE tp.tournament_id = ? AND tp.eliminated = 0
            ORDER BY tp.seed ASC
        ");
        $stmt->execute([$tournamentId]);
        return $stmt->fetchAll();
    }
    
    public function eliminate(int $participantId, int $round): bool {
        return $this->update($participantId, [
            'eliminated' => 1,
            'eliminated_round' => $round
        ]);
    }
    
    public function setFinalRank(int $participantId, int $rank): bool {
        return $this->update($participantId, [
            'final_rank' => $rank
        ]);
    }
    
    public function addWin(int $participantId): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET wins = wins + 1 WHERE id = ?
        ");
        $stmt->execute([$participantId]);
    }
    
    public function addLoss(int $participantId): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET losses = losses + 1 WHERE id = ?
        ");
        $stmt->execute([$participantId]);
    }
}
