<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class GuildMember extends Model {
    protected string $table = 'guild_members';
    
    public function getUserGuild(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT gm.*, g.name, g.tag, g.description, g.level, g.icon, g.banner_color,
                   g.gold_treasury, g.gems_treasury, g.max_members, g.leader_id
            FROM {$this->table} gm
            JOIN guilds g ON gm.guild_id = g.id
            WHERE gm.user_id = ? AND g.deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
    
    public function isMemberOf(int $userId, int $guildId): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM {$this->table} gm
            JOIN guilds g ON gm.guild_id = g.id
            WHERE gm.user_id = ? AND gm.guild_id = ? AND g.deleted_at IS NULL
        ");
        $stmt->execute([$userId, $guildId]);
        return (bool)$stmt->fetch();
    }
    
    public function joinGuild(int $userId, int $guildId, string $role = 'recruit'): bool {
        try {
            $this->create([
                'user_id' => $userId,
                'guild_id' => $guildId,
                'role' => $role
            ]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    public function leaveGuild(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function updateRole(int $userId, string $role): bool {
        $member = $this->whereFirst('user_id', $userId);
        if (!$member) return false;
        
        return $this->update($member['id'], ['role' => $role]);
    }
}
