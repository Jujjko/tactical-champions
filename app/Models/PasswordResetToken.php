<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use PDO;

class PasswordResetToken extends Model {
    protected string $table = 'password_reset_tokens';
    
    public function createToken(int $userId): string {
        $this->deleteUsedTokens($userId);
        
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        
        $this->create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        return $token;
    }
    
    public function findValidToken(string $token): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE token = ? AND used = FALSE AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public function markAsUsed(string $token): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET used = TRUE WHERE token = ?
        ");
        $stmt->execute([$token]);
    }
    
    public function deleteUsedTokens(int $userId): void {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    public function cleanupExpired(): void {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} WHERE expires_at < NOW()
        ");
        $stmt->execute();
    }
}