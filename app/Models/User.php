<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class User extends Model {
    protected string $table = 'users';
    protected bool $softDeletes = true;
    
    public function findByUsername(string $username): ?array {
        return $this->whereFirst('username', $username);
    }
    
    public function findByEmail(string $email): ?array {
        return $this->whereFirst('email', $email);
    }
    
    public function createUser(string $username, string $email, string $password): int {
        $userId = $this->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'player',
            'level' => 1
        ]);
        
        $stmt = $this->db->prepare("INSERT INTO user_resources (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        return $userId;
    }
    
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    public function addExperience(int $userId, int $experience): void {
        $user = $this->findById($userId);
        $newExp = $user['experience'] + $experience;
        $newLevel = $user['level'];
        
        while ($newExp >= ($newLevel * 100)) {
            $newExp -= ($newLevel * 100);
            $newLevel++;
        }
        
        $this->update($userId, [
            'experience' => $newExp,
            'level' => $newLevel
        ]);
    }
    
    public function updatePassword(int $userId, string $newPassword): void {
        $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }
    
    public function searchByName(string $query, int $excludeUserId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT id, username, level
            FROM {$this->table}
            WHERE username LIKE ? AND id != ? AND deleted_at IS NULL
            LIMIT ?
        ");
        $stmt->execute(["%{$query}%", $excludeUserId, $limit]);
        return $stmt->fetchAll();
    }
}