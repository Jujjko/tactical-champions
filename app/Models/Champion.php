<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Champion extends Model {
    protected string $table = 'champions';
    protected bool $softDeletes = true;
    
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
        
        $rand = random_int(1, 100);
        $cumulative = 0;
        $selectedTier = 'common';
        
        foreach ($rarities as $tier => $chance) {
            $cumulative += $chance;
            if ($rand <= $cumulative) {
                $selectedTier = $tier;
                break;
            }
        }
        
        $champions = $this->getByTier($selectedTier);
        if (empty($champions)) {
            return null;
        }
        
        return $champions[array_rand($champions)];
    }
}