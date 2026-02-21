<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Database;

class Leaderboard extends Model {
    protected string $table = 'leaderboards';
    
    public function getTop(string $type, int $limit = 50, ?int $seasonId = null, ?int $guildId = null): array {
        $sql = "SELECT l.*, u.username, u.level, u.experience";
        
        if ($type === 'pvp_rank' || $type === 'season') {
            $sql .= ", pr.rating, pr.wins, pr.losses, pr.current_streak";
        }
        
        $sql .= " FROM {$this->table} l
                  JOIN users u ON l.user_id = u.id";
        
        if ($type === 'pvp_rank' || $type === 'season') {
            $sql .= " LEFT JOIN pvp_ratings pr ON u.id = pr.user_id";
        }
        
        $sql .= " WHERE l.type = ?";
        
        $params = [$type];
        
        if ($seasonId !== null) {
            $sql .= " AND l.season_id = ?";
            $params[] = $seasonId;
        }
        
        if ($guildId !== null) {
            $sql .= " AND l.guild_id = ?";
            $params[] = $guildId;
        }
        
        $sql .= " ORDER BY l.score DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll();
        
        foreach ($results as $i => &$row) {
            $row['position'] = $i + 1;
        }
        
        return $results;
    }
    
    public function getUserRank(int $userId, string $type, ?int $seasonId = null): ?array {
        $sql = "SELECT l.*, u.username, u.level FROM {$this->table} l
                JOIN users u ON l.user_id = u.id
                WHERE l.user_id = ? AND l.type = ?";
        
        $params = [$userId, $type];
        
        if ($seasonId !== null) {
            $sql .= " AND l.season_id = ?";
            $params[] = $seasonId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() ?: null;
    }
    
    public function getUserPosition(int $userId, string $type, ?int $seasonId = null): int {
        $userRank = $this->getUserRank($userId, $type, $seasonId);
        
        if (!$userRank) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) + 1 as position FROM {$this->table}
                WHERE type = ? AND score > ?";
        
        $params = [$type, $userRank['score']];
        
        if ($seasonId !== null) {
            $sql .= " AND season_id = ?";
            $params[] = $seasonId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }
    
    public function updatePlayerScore(int $userId, string $type, int $score, ?int $seasonId = null, ?int $guildId = null): void {
        $sql = "INSERT INTO {$this->table} (type, user_id, score, rank, season_id, guild_id)
                VALUES (?, ?, ?, 0, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = NOW()";
        
        $this->db->prepare($sql)->execute([$type, $userId, $score, $seasonId, $guildId]);
    }
    
    public function recalculateRanks(string $type, ?int $seasonId = null): int {
        $sql = "SELECT id FROM {$this->table} WHERE type = ?";
        $params = [$type];
        
        if ($seasonId !== null) {
            $sql .= " AND season_id = ?";
            $params[] = $seasonId;
        }
        
        $sql .= " ORDER BY score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();
        
        $updated = 0;
        foreach ($entries as $i => $entry) {
            $rank = $i + 1;
            $this->db->prepare("UPDATE {$this->table} SET rank = ? WHERE id = ?")
                     ->execute([$rank, $entry['id']]);
            $updated++;
        }
        
        return $updated;
    }
    
    public function createSnapshot(string $type, ?int $seasonId = null): int {
        $topPlayers = $this->getTop($type, 100, $seasonId);
        
        $stmt = $this->db->prepare("
            INSERT INTO leaderboard_snapshots (type, season_id, snapshot_data)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$type, $seasonId, json_encode($topPlayers)]);
        
        return (int)$this->db->lastInsertId();
    }
    
    public function getSnapshots(string $type, int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM leaderboard_snapshots 
            WHERE type = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$type, $limit]);
        return $stmt->fetchAll();
    }
}
