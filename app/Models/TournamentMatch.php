<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class TournamentMatch extends Model {
    protected string $table = 'tournament_matches';
    
    public function getByTournament(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT tm.*, 
                   u1.username as player1_name, u2.username as player2_name,
                   uw.username as winner_name
            FROM {$this->table} tm
            LEFT JOIN users u1 ON tm.player1_id = u1.id
            LEFT JOIN users u2 ON tm.player2_id = u2.id
            LEFT JOIN users uw ON tm.winner_id = uw.id
            WHERE tm.tournament_id = ?
            ORDER BY tm.round ASC, tm.match_number ASC
        ");
        $stmt->execute([$tournamentId]);
        return $stmt->fetchAll();
    }
    
    public function getByRound(int $tournamentId, int $round): array {
        $stmt = $this->db->prepare("
            SELECT tm.*, 
                   u1.username as player1_name, u2.username as player2_name
            FROM {$this->table} tm
            LEFT JOIN users u1 ON tm.player1_id = u1.id
            LEFT JOIN users u2 ON tm.player2_id = u2.id
            WHERE tm.tournament_id = ? AND tm.round = ?
            ORDER BY tm.match_number ASC
        ");
        $stmt->execute([$tournamentId, $round]);
        return $stmt->fetchAll();
    }
    
    public function getPendingMatches(int $tournamentId): array {
        $stmt = $this->db->prepare("
            SELECT tm.*, 
                   u1.username as player1_name, u2.username as player2_name
            FROM {$this->table} tm
            LEFT JOIN users u1 ON tm.player1_id = u1.id
            LEFT JOIN users u2 ON tm.player2_id = u2.id
            WHERE tm.tournament_id = ? AND tm.status = 'pending'
            ORDER BY tm.round ASC, tm.match_number ASC
        ");
        $stmt->execute([$tournamentId]);
        return $stmt->fetchAll();
    }
    
    public function getUserCurrentMatch(int $tournamentId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT tm.*, 
                   u1.username as player1_name, u2.username as player2_name
            FROM {$this->table} tm
            LEFT JOIN users u1 ON tm.player1_id = u1.id
            LEFT JOIN users u2 ON tm.player2_id = u2.id
            WHERE tm.tournament_id = ? 
              AND (tm.player1_id = ? OR tm.player2_id = ?)
              AND tm.status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([$tournamentId, $userId, $userId]);
        return $stmt->fetch() ?: null;
    }
    
    public function setWinner(int $matchId, int $winnerId, int $loserId): bool {
        return $this->update($matchId, [
            'winner_id' => $winnerId,
            'loser_id' => $loserId,
            'status' => 'finished',
            'finished_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function countPendingMatches(int $tournamentId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table} 
            WHERE tournament_id = ? AND status = 'pending'
        ");
        $stmt->execute([$tournamentId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function countFinishedMatches(int $tournamentId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table} 
            WHERE tournament_id = ? AND status = 'finished'
        ");
        $stmt->execute([$tournamentId]);
        return (int)$stmt->fetchColumn();
    }
}
