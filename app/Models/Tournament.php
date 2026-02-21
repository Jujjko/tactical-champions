<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Tournament extends Model {
    protected string $table = 'tournaments';
    
    public function getActiveTournaments(): array {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as participant_count
            FROM {$this->table} t
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.status IN ('open', 'full', 'ongoing')
            ORDER BY t.start_time ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getOpenTournaments(): array {
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as participant_count
            FROM {$this->table} t
            WHERE t.status = 'open'
            ORDER BY t.start_time ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getWithParticipants(int $id): ?array {
        $tournament = $this->findById($id);
        if (!$tournament) return null;
        
        $stmt = $this->db->prepare("
            SELECT tp.*, u.username, u.level, pr.rating
            FROM tournament_participants tp
            JOIN users u ON tp.user_id = u.id
            LEFT JOIN pvp_ratings pr ON u.id = pr.user_id
            WHERE tp.tournament_id = ?
            ORDER BY tp.seed ASC
        ");
        $stmt->execute([$id]);
        $tournament['participants'] = $stmt->fetchAll();
        
        return $tournament;
    }
    
    public function getWithMatches(int $id): ?array {
        $tournament = $this->getWithParticipants($id);
        if (!$tournament) return null;
        
        $stmt = $this->db->prepare("
            SELECT tm.*, 
                   u1.username as player1_name, u2.username as player2_name,
                   uw.username as winner_name
            FROM tournament_matches tm
            LEFT JOIN users u1 ON tm.player1_id = u1.id
            LEFT JOIN users u2 ON tm.player2_id = u2.id
            LEFT JOIN users uw ON tm.winner_id = uw.id
            WHERE tm.tournament_id = ?
            ORDER BY tm.round ASC, tm.match_number ASC
        ");
        $stmt->execute([$id]);
        $tournament['matches'] = $stmt->fetchAll();
        
        return $tournament;
    }
    
    public function getWithRewards(int $id): ?array {
        $tournament = $this->getWithMatches($id);
        if (!$tournament) return null;
        
        $stmt = $this->db->prepare("
            SELECT * FROM tournament_rewards WHERE tournament_id = ? ORDER BY place ASC
        ");
        $stmt->execute([$id]);
        $tournament['rewards'] = $stmt->fetchAll();
        
        return $tournament;
    }
    
    public function getParticipantCount(int $tournamentId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM tournament_participants WHERE tournament_id = ?
        ");
        $stmt->execute([$tournamentId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function isUserRegistered(int $tournamentId, int $userId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tournament_participants 
            WHERE tournament_id = ? AND user_id = ?
        ");
        $stmt->execute([$tournamentId, $userId]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function getUserTournaments(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT t.*, tp.eliminated, tp.final_rank, tp.wins, tp.losses
            FROM tournaments t
            JOIN tournament_participants tp ON t.id = tp.tournament_id
            WHERE tp.user_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
