<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Session;
use App\Helpers\RankHelper;

class ArenaQueue extends Model
{
    protected string $table = 'arena_queue';

    public function joinQueue(int $userId, int $championId, int $rating): bool
    {
        $this->leaveQueue($userId);
        
        return $this->create([
            'user_id' => $userId,
            'champion_id' => $championId,
            'rating' => $rating,
        ]) > 0;
    }

    public function leaveQueue(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function findMatch(int $userId, int $rating, int $range = 150): ?array
    {
        $stmt = $this->db->prepare("
            SELECT aq.*, u.username, uc.level as champion_level, c.name as champion_name,
                   pr.rating, pr.wins, pr.losses
            FROM {$this->table} aq
            JOIN users u ON aq.user_id = u.id
            JOIN user_champions uc ON aq.champion_id = uc.id
            JOIN champions c ON uc.champion_id = c.id
            LEFT JOIN pvp_ratings pr ON aq.user_id = pr.user_id
            WHERE aq.user_id != ?
              AND aq.rating BETWEEN ? AND ?
              AND aq.created_at < DATE_SUB(NOW(), INTERVAL 3 SECOND)
            ORDER BY ABS(aq.rating - ?) ASC, aq.created_at ASC
            LIMIT 1
        ");
        
        $minRating = max(0, $rating - $range);
        $maxRating = $rating + $range;
        
        $stmt->execute([$userId, $minRating, $maxRating, $rating]);
        return $stmt->fetch() ?: null;
    }

    public function expandSearch(int $userId, int $rating, int $range = 300): ?array
    {
        $stmt = $this->db->prepare("
            SELECT aq.*, u.username, uc.level as champion_level, c.name as champion_name,
                   pr.rating, pr.wins, pr.losses
            FROM {$this->table} aq
            JOIN users u ON aq.user_id = u.id
            JOIN user_champions uc ON aq.champion_id = uc.id
            JOIN champions c ON uc.champion_id = c.id
            LEFT JOIN pvp_ratings pr ON aq.user_id = pr.user_id
            WHERE aq.user_id != ?
              AND aq.rating BETWEEN ? AND ?
            ORDER BY ABS(aq.rating - ?) ASC, aq.created_at ASC
            LIMIT 1
        ");
        
        $minRating = max(0, $rating - $range);
        $maxRating = $rating + $range;
        
        $stmt->execute([$userId, $minRating, $maxRating, $rating]);
        return $stmt->fetch() ?: null;
    }

    public function getQueuePosition(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as position
            FROM {$this->table}
            WHERE created_at <= (SELECT created_at FROM {$this->table} WHERE user_id = ?)
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? (int)$result['position'] : 0;
    }

    public function getQueueCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    }

    public function isInQueue(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() !== false;
    }

    public function getQueueEntry(int $userId): ?array
    {
        return $this->whereFirst('user_id', $userId);
    }

    public function cleanupStale(int $maxMinutes = 5): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$maxMinutes]);
        return $stmt->rowCount();
    }
}
