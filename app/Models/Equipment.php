<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Equipment extends Model {
    protected string $table = 'equipment';
    protected bool $softDeletes = true;
    
    public function getByType(string $type): array {
        return $this->where('type', $type);
    }
    
    public function getBySlot(string $slot): array {
        return $this->where('slot', $slot);
    }
    
    public function getByTier(string $tier): array {
        return $this->where('tier', $tier);
    }
    
    public function getRandomByRarity(): ?array {
        $rarities = [
            'common' => 50,
            'rare' => 30,
            'epic' => 15,
            'legendary' => 4,
            'mythic' => 1
        ];
        
        $rand = rand(1, 100);
        $cumulative = 0;
        $selectedTier = 'common';
        
        foreach ($rarities as $tier => $chance) {
            $cumulative += $chance;
            if ($rand <= $cumulative) {
                $selectedTier = $tier;
                break;
            }
        }
        
        $equipment = $this->getByTier($selectedTier);
        if (empty($equipment)) {
            return null;
        }
        
        return $equipment[array_rand($equipment)];
    }
    
    public function getAllWithTypeFilter(?string $type = null): array {
        if ($type) {
            return $this->getByType($type);
        }
        return $this->all();
    }
}
