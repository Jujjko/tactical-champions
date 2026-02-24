<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class PvpBattle extends Model
{
    protected string $table = 'pvp_battles';
    
    public function getBattles(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT pb.*,
                   u1.username as attacker_name,
                   u2.username as defender_name,
                   u3.username as winner_name
            FROM {$this->table} pb
            JOIN users u1 ON pb.attacker_id = u1.id
            JOIN users u2 ON pb.defender_id = u2.id
            JOIN users u3 ON pb.winner_id = u3.id
            ORDER BY pb.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getUserBattles(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT pb.*,
                   u1.username as attacker_name,
                   u2.username as defender_name,
                   u3.username as winner_name
            FROM {$this->table} pb
            JOIN users u1 ON pb.attacker_id = u1.id
            JOIN users u2 ON pb.defender_id = u2.id
            JOIN users u3 ON pb.winner_id = u3.id
            WHERE pb.attacker_id = ? OR pb.defender_id = ?
            ORDER BY pb.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
            $stmt->execute();
        } else {
            $where = [];
            $params = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $where));
            $stmt->execute($params);
        }
        return (int)$stmt->fetch()['count'];
    }
    
    public function createBattle(array $data): int
    {
        return $this->create([
            'attacker_id' => $data['attacker_id'],
            'attacker_champion_id' => $data['attacker_champion_id'],
            'defender_id' => $data['defender_id'],
            'defender_champion_id' => $data['defender_champion_id'],
            'winner_id' => $data['winner_id'],
            'loser_id' => $data['loser_id'],
            'attacker_rating_change' => $data['attacker_rating_change'] ?? 0,
            'defender_rating_change' => $data['defender_rating_change'] ?? 0,
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'battle_log' => $data['battle_log'] ?? null,
        ]);
    }
    
    public function getStats(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_battles,
                AVG(duration_seconds) as avg_duration,
                COUNT(DISTINCT attacker_id) as unique_attackers,
                COUNT(DISTINCT defender_id) as unique_defenders
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getRecentWinners(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT u.username, u.id, COUNT(*) as wins
            FROM {$this->table} pb
            JOIN users u ON pb.winner_id = u.id
            WHERE pb.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY u.id
            ORDER BY wins DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}