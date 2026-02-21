<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Referral extends Model {
    protected string $table = 'referrals';
    
    public function getReferralCode(int $userId): ?string {
        $stmt = $this->db->prepare("
            SELECT code FROM referral_codes WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: null;
    }
    
    public function createReferralCode(int $userId): string {
        $existing = $this->getReferralCode($userId);
        if ($existing) return $existing;
        
        $code = $this->generateCode();
        
        $this->db->prepare("
            INSERT INTO referral_codes (user_id, code) VALUES (?, ?)
        ")->execute([$userId, $code]);
        
        return $code;
    }
    
    public function useReferralCode(string $code, int $refereeId): bool {
        $stmt = $this->db->prepare("
            SELECT * FROM referral_codes WHERE code = ? AND uses < max_uses
        ");
        $stmt->execute([$code]);
        $referralCode = $stmt->fetch();
        
        if (!$referralCode) return false;
        
        if ($referralCode['user_id'] === $refereeId) return false;
        
        $this->db->prepare("
            INSERT INTO referrals (referrer_id, referee_id) VALUES (?, ?)
        ")->execute([$referralCode['user_id'], $refereeId]);
        
        $this->db->prepare("
            UPDATE referral_codes SET uses = uses + 1 WHERE code = ?
        ")->execute([$code]);
        
        return true;
    }
    
    public function getReferrals(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username as referee_name, u.level as referee_level
            FROM {$this->table} r
            JOIN users u ON r.referee_id = u.id
            WHERE r.referrer_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getReferralCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table} WHERE referrer_id = ?
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    private function generateCode(): string {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        $stmt = $this->db->prepare("SELECT 1 FROM referral_codes WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            return $this->generateCode();
        }
        
        return $code;
    }
}
