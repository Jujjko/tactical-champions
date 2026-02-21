<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use App\Services\CacheService;

class UserChampion extends Model {
    protected string $table = 'user_champions';
    protected bool $softDeletes = true;
    
    public function getUserChampions(int $userId): array {
        $cache = CacheService::getInstance();
        $key = "user:{$userId}:champions";
        
        return $cache->remember($key, function() use ($userId) {
            $stmt = $this->db->prepare("
                SELECT uc.*, c.name, c.tier, c.special_ability, c.description, c.image_url
                FROM {$this->table} uc
                JOIN champions c ON uc.champion_id = c.id
                WHERE uc.user_id = ? AND uc.deleted_at IS NULL
                ORDER BY uc.stars DESC, uc.level DESC, c.tier DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        }, 120);
    }
    
    public function addChampionToUser(int $userId, int $championId): int {
        $stmt = $this->db->prepare("SELECT * FROM champions WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$championId]);
        $champion = $stmt->fetch();
        
        if (!$champion) {
            error_log("UserChampion::addChampionToUser - Champion not found: {$championId}");
            return 0;
        }
        
        error_log("UserChampion::addChampionToUser - Found champion: " . $champion['name']);
        
        $id = $this->create([
            'user_id' => $userId,
            'champion_id' => $championId,
            'level' => 1,
            'stars' => 1,
            'experience' => 0,
            'health' => $champion['base_health'],
            'attack' => $champion['base_attack'],
            'defense' => $champion['base_defense'],
            'speed' => $champion['base_speed']
        ]);
        
        error_log("UserChampion::addChampionToUser - Created user_champion with ID: {$id}");
        
        $this->invalidateCache($userId);
        return $id;
    }

    public function getChampionWithDetails(int $userChampionId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT uc.*, c.name, c.tier, c.special_ability, c.description, c.image_url
            FROM {$this->table} uc
            JOIN champions c ON uc.champion_id = c.id
            WHERE uc.id = ? AND uc.user_id = ? AND uc.deleted_at IS NULL
        ");
        $stmt->execute([$userChampionId, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function addExperience(int $userChampionId, int $exp): void {
        $champion = $this->findById($userChampionId);
        $newExp = $champion['experience'] + $exp;
        $newLevel = $champion['level'];
        $currentHealth = $champion['health'];
        $currentAttack = $champion['attack'];
        $currentDefense = $champion['defense'];
        $currentSpeed = $champion['speed'];
        
        while ($newExp >= ($newLevel * 50)) {
            $newExp -= ($newLevel * 50);
            $newLevel++;
            
            $currentHealth = (int)($currentHealth * 1.1);
            $currentAttack = (int)($currentAttack * 1.1);
            $currentDefense = (int)($currentDefense * 1.1);
            $currentSpeed = (int)($currentSpeed * 1.05);
        }
        
        $this->update($userChampionId, [
            'experience' => $newExp,
            'level' => $newLevel,
            'health' => $currentHealth,
            'attack' => $currentAttack,
            'defense' => $currentDefense,
            'speed' => $currentSpeed
        ]);
        
        $this->invalidateCache($champion['user_id']);
    }
    
    public function getChampionCount(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE user_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
    
    public function getChampionsByType(int $userId, int $championId): array
    {
        $stmt = $this->db->prepare("
            SELECT uc.*, c.name, c.tier
            FROM {$this->table} uc
            JOIN champions c ON uc.champion_id = c.id
            WHERE uc.user_id = ? AND uc.champion_id = ? AND uc.deleted_at IS NULL
            ORDER BY uc.stars DESC, uc.level DESC
        ");
        $stmt->execute([$userId, $championId]);
        return $stmt->fetchAll();
    }
    
    private function invalidateCache(int $userId): void {
        CacheService::getInstance()->delete("user:{$userId}:champions");
    }
}