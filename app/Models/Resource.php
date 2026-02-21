<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Resource extends Model {
    protected string $table = 'user_resources';
    
    public function getUserResources(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function addGold(int $userId, int $amount): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET gold = gold + ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $userId]);
    }
    
    public function addGems(int $userId, int $amount): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET gems = gems + ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $userId]);
    }
    
    public function addEnergy(int $userId, int $amount): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET energy = LEAST(energy + ?, max_energy)
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $userId]);
    }
    
    public function deductGold(int $userId, int $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET gold = gold - ? 
            WHERE user_id = ? AND gold >= ?
        ");
        $stmt->execute([$amount, $userId, $amount]);
        return $stmt->rowCount() > 0;
    }
    
    public function deductGems(int $userId, int $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET gems = gems - ? 
            WHERE user_id = ? AND gems >= ?
        ");
        $stmt->execute([$amount, $userId, $amount]);
        return $stmt->rowCount() > 0;
    }
    
    public function setEnergy(int $userId, int $amount): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET energy = LEAST(?, max_energy)
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $userId]);
    }
    
    public function addLootbox(int $userId, string $type = 'bronze'): void {
        $stmt = $this->db->prepare("
            INSERT INTO user_lootboxes (user_id, lootbox_type) VALUES (?, ?)
        ");
        $stmt->execute([$userId, $type]);
    }
    
    public function useEnergy(int $userId, int $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET energy = energy - ? 
            WHERE user_id = ? AND energy >= ?
        ");
        $stmt->execute([$amount, $userId, $amount]);
        return $stmt->rowCount() > 0;
    }
    
    public function regenerateEnergy(int $userId): void {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET energy = LEAST(
                    energy + FLOOR(TIMESTAMPDIFF(SECOND, last_energy_update, NOW()) / ?) * ?,
                    max_energy
                ),
                last_energy_update = NOW()
            WHERE user_id = ?
              AND TIMESTAMPDIFF(SECOND, last_energy_update, NOW()) >= ?
        ");
        
        $regenRate = (int)($_ENV['ENERGY_REGEN_RATE'] ?? 10);
        $regenInterval = (int)($_ENV['ENERGY_REGEN_INTERVAL'] ?? 600);
        
        $stmt->execute([$regenInterval, $regenRate, $userId, $regenInterval]);
    }
}