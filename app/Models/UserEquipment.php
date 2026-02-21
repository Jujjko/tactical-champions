<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserEquipment extends Model {
    protected string $table = 'user_equipment';
    protected bool $softDeletes = true;
    
    public function getUserEquipment(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT ue.*, e.name, e.type, e.slot, e.tier, 
                   e.health_bonus, e.attack_bonus, e.defense_bonus, e.speed_bonus,
                   e.special_effect, e.description, e.image_url,
                   uc.id as equipped_champion_id, c.name as equipped_champion_name
            FROM {$this->table} ue
            JOIN equipment e ON ue.equipment_id = e.id
            LEFT JOIN user_champions uc ON ue.equipped_to_champion_id = uc.id
            LEFT JOIN champions c ON uc.champion_id = c.id
            WHERE ue.user_id = ? AND ue.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY e.tier DESC, e.type, e.name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getEquipmentBySlot(int $userId, string $slot): array {
        $stmt = $this->db->prepare("
            SELECT ue.*, e.name, e.type, e.slot, e.tier, 
                   e.health_bonus, e.attack_bonus, e.defense_bonus, e.speed_bonus
            FROM {$this->table} ue
            JOIN equipment e ON ue.equipment_id = e.id
            WHERE ue.user_id = ? AND e.slot = ? AND ue.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY e.tier DESC
        ");
        $stmt->execute([$userId, $slot]);
        return $stmt->fetchAll();
    }
    
    public function addEquipmentToUser(int $userId, int $equipmentId): int {
        return $this->create([
            'user_id' => $userId,
            'equipment_id' => $equipmentId,
            'level' => 1,
            'is_equipped' => false
        ]);
    }
    
    public function equipToChampion(int $userEquipmentId, int $userChampionId, int $userId): bool {
        $equipment = $this->findById($userEquipmentId);
        if (!$equipment || $equipment['user_id'] !== $userId) {
            return false;
        }
        
        $this->unequipFromSlot($userChampionId, $equipment['equipment_id'], $userId);
        
        return $this->update($userEquipmentId, [
            'is_equipped' => true,
            'equipped_to_champion_id' => $userChampionId
        ]);
    }
    
    public function unequip(int $userEquipmentId, int $userId): bool {
        $equipment = $this->findById($userEquipmentId);
        if (!$equipment || $equipment['user_id'] !== $userId) {
            return false;
        }
        
        return $this->update($userEquipmentId, [
            'is_equipped' => false,
            'equipped_to_champion_id' => null
        ]);
    }
    
    private function unequipFromSlot(int $userChampionId, int $equipmentId, int $userId): void {
        $stmt = $this->db->prepare("
            SELECT ue.id, e.slot 
            FROM {$this->table} ue
            JOIN equipment e ON ue.equipment_id = e.id
            WHERE ue.equipped_to_champion_id = ? AND ue.user_id = ? AND ue.deleted_at IS NULL
        ");
        $stmt->execute([$userChampionId, $userId]);
        $equipped = $stmt->fetch();
        
        if ($equipped) {
            $newEquipment = $this->db->prepare("SELECT slot FROM equipment WHERE id = ?");
            $newEquipment->execute([$equipmentId]);
            $newSlot = $newEquipment->fetchColumn();
            
            if ($equipped['slot'] === $newSlot) {
                $this->update($equipped['id'], [
                    'is_equipped' => false,
                    'equipped_to_champion_id' => null
                ]);
            }
        }
    }
    
    public function getChampionEquipment(int $userChampionId): array {
        $stmt = $this->db->prepare("
            SELECT ue.*, e.name, e.type, e.slot, e.tier, 
                   e.health_bonus, e.attack_bonus, e.defense_bonus, e.speed_bonus
            FROM {$this->table} ue
            JOIN equipment e ON ue.equipment_id = e.id
            WHERE ue.equipped_to_champion_id = ? AND ue.deleted_at IS NULL AND e.deleted_at IS NULL
        ");
        $stmt->execute([$userChampionId]);
        return $stmt->fetchAll();
    }
    
    public function getTotalStats(int $userChampionId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(e.health_bonus), 0) as health,
                COALESCE(SUM(e.attack_bonus), 0) as attack,
                COALESCE(SUM(e.defense_bonus), 0) as defense,
                COALESCE(SUM(e.speed_bonus), 0) as speed
            FROM {$this->table} ue
            JOIN equipment e ON ue.equipment_id = e.id
            WHERE ue.equipped_to_champion_id = ? AND ue.deleted_at IS NULL AND e.deleted_at IS NULL
        ");
        $stmt->execute([$userChampionId]);
        return $stmt->fetch();
    }
    
    public function countUserEquipment(int $userId): int {
        return $this->count(['user_id' => $userId]);
    }
}
