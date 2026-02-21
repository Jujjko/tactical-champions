<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Guild extends Model {
    protected string $table = 'guilds';
    protected bool $softDeletes = true;
    
    public function findByIdWithMembers(int $guildId): ?array {
        $guild = $this->findById($guildId);
        if (!$guild) return null;
        
        $stmt = $this->db->prepare("
            SELECT gm.*, u.username, u.level
            FROM guild_members gm
            JOIN users u ON gm.user_id = u.id
            WHERE gm.guild_id = ?
            ORDER BY 
                CASE gm.role 
                    WHEN 'leader' THEN 1 
                    WHEN 'officer' THEN 2 
                    WHEN 'veteran' THEN 3 
                    WHEN 'member' THEN 4 
                    WHEN 'recruit' THEN 5 
                END,
                gm.joined_at ASC
        ");
        $stmt->execute([$guildId]);
        $guild['members'] = $stmt->fetchAll();
        $guild['member_count'] = count($guild['members']);
        
        return $guild;
    }
    
    public function getLeaderboard(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT g.*, u.username as leader_name, 
                   (SELECT COUNT(*) FROM guild_members WHERE guild_id = g.id) as member_count
            FROM {$this->table} g
            JOIN users u ON g.leader_id = u.id
            WHERE g.deleted_at IS NULL
            ORDER BY g.level DESC, g.experience DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getRecruitingGuilds(int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT g.*, u.username as leader_name,
                   (SELECT COUNT(*) FROM guild_members WHERE guild_id = g.id) as member_count
            FROM {$this->table} g
            JOIN users u ON g.leader_id = u.id
            WHERE g.is_recruiting = TRUE AND g.deleted_at IS NULL
            ORDER BY g.level DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function createGuild(int $leaderId, string $name, string $tag, string $description = ''): int {
        $guildId = $this->create([
            'name' => $name,
            'tag' => $tag,
            'description' => $description,
            'leader_id' => $leaderId
        ]);
        
        $this->db->prepare("
            INSERT INTO guild_members (guild_id, user_id, role) VALUES (?, ?, 'leader')
        ")->execute([$guildId, $leaderId]);
        
        return $guildId;
    }
}
