<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ChampionShard;
use App\Models\UserChampion;
use Core\Session;

class AscensionService {
    private ChampionShard $shardModel;
    private UserChampion $userChampionModel;
    
    private const TIER_ORDER = ['white', 'blue', 'red', 'gold'];
    private const MAX_STARS = 5;
    
    private const SHARD_REQUIREMENTS = [
        'common' => [
            1 => 15, 2 => 25, 3 => 40, 4 => 60, 5 => 0
        ],
        'rare' => [
            1 => 20, 2 => 35, 3 => 55, 4 => 80, 5 => 0
        ],
        'epic' => [
            1 => 30, 2 => 50, 3 => 80, 4 => 120, 5 => 0
        ],
        'legendary' => [
            1 => 40, 2 => 70, 3 => 110, 4 => 160, 5 => 0
        ],
        'mythic' => [
            1 => 60, 2 => 100, 3 => 160, 4 => 240, 5 => 0
        ]
    ];
    
    private const TIER_MULTIPLIERS = [
        'white' => 1.0,
        'blue' => 1.5,
        'red' => 2.2,
        'gold' => 3.0
    ];
    
    private const TIER_UPGRADE_COST = [
        'white' => ['blue' => 100],
        'blue' => ['red' => 200],
        'red' => ['gold' => 350]
    ];
    
    public function __construct() {
        $this->shardModel = new ChampionShard();
        $this->userChampionModel = new UserChampion();
    }
    
    public function getAscensionInfo(int $userId, int $userChampionId): array {
        $userChamp = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$userChamp) {
            return ['error' => 'Champion not found'];
        }
        
        $currentShards = $this->shardModel->getAmount($userId, $userChamp['champion_id']);
        $requiredShards = $this->getRequiredShards($userChamp['tier'], $userChamp['stars']);
        $canAscend = $this->canAscend($userChamp, $currentShards);
        $canTierUp = $this->canTierUp($userChamp, $currentShards);
        
        return [
            'champion' => $userChamp,
            'current_shards' => $currentShards,
            'required_shards' => $requiredShards,
            'can_ascend' => $canAscend,
            'can_tier_up' => $canTierUp,
            'next_tier' => $this->getNextTier($userChamp['star_tier'] ?? 'white'),
            'is_maxed' => $this->isMaxed($userChamp),
            'tier_multiplier' => self::TIER_MULTIPLIERS[$userChamp['star_tier'] ?? 'white'] ?? 1.0,
            'total_level' => $this->getTotalLevel($userChamp),
            'star_tier' => $userChamp['star_tier'] ?? 'white',
        ];
    }
    
    public function ascend(int $userId, int $userChampionId): array {
        $userChamp = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$userChamp) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        if ($this->isMaxed($userChamp)) {
            return ['success' => false, 'error' => 'Champion is already at maximum level'];
        }
        
        $currentShards = $this->shardModel->getAmount($userId, $userChamp['champion_id']);
        $required = $this->getRequiredShards($userChamp['tier'], $userChamp['stars']);
        
        if ($currentShards < $required) {
            return [
                'success' => false, 
                'error' => "Need {$required} shards, you have {$currentShards}",
                'current' => $currentShards,
                'required' => $required
            ];
        }
        
        if (!$this->shardModel->removeShards($userId, $userChamp['champion_id'], $required)) {
            return ['success' => false, 'error' => 'Failed to remove shards'];
        }
        
        $newStars = $userChamp['stars'] + 1;
        $newTier = $userChamp['star_tier'] ?? 'white';
        
        if ($newStars > self::MAX_STARS) {
            $newStars = 1;
            $newTier = $this->getNextTier($userChamp['star_tier'] ?? 'white');
        }
        
        $this->userChampionModel->update($userChampionId, [
            'stars' => $newStars,
            'star_tier' => $newTier
        ]);
        
        $this->recalculateStats($userChampionId);
        
        return [
            'success' => true,
            'old_stars' => $userChamp['stars'],
            'new_stars' => $newStars,
            'old_tier' => $userChamp['star_tier'] ?? 'white',
            'new_tier' => $newTier,
            'message' => $this->getAscendMessage($userChamp['stars'], $newStars, $userChamp['star_tier'] ?? 'white', $newTier)
        ];
    }
    
    public function tierUp(int $userId, int $userChampionId): array {
        $userChamp = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$userChamp) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        if ($userChamp['stars'] < self::MAX_STARS) {
            return ['success' => false, 'error' => 'Champion must be 5★ to tier up'];
        }
        
        $currentTier = $userChamp['star_tier'] ?? 'white';
        $nextTier = $this->getNextTier($currentTier);
        if (!$nextTier) {
            return ['success' => false, 'error' => 'Already at maximum tier'];
        }
        
        $tierCost = self::TIER_UPGRADE_COST[$currentTier][$nextTier] ?? 0;
        $currentShards = $this->shardModel->getAmount($userId, $userChamp['champion_id']);
        
        if ($currentShards < $tierCost) {
            return [
                'success' => false,
                'error' => "Need {$tierCost} shards for tier upgrade",
                'current' => $currentShards,
                'required' => $tierCost
            ];
        }
        
        if (!$this->shardModel->removeShards($userId, $userChamp['champion_id'], $tierCost)) {
            return ['success' => false, 'error' => 'Failed to remove shards'];
        }
        
        $this->userChampionModel->update($userChampionId, [
            'stars' => 1,
            'star_tier' => $nextTier
        ]);
        
        $this->recalculateStats($userChampionId);
        
        return [
            'success' => true,
            'new_tier' => $nextTier,
            'new_stars' => 1,
            'message' => "Champion ascended to {$nextTier} tier!"
        ];
    }
    
    private function canAscend(array $userChamp, int $currentShards): bool {
        if ($this->isMaxed($userChamp)) return false;
        if ($userChamp['stars'] >= self::MAX_STARS) return false;
        
        $required = $this->getRequiredShards($userChamp['tier'], $userChamp['stars']);
        return $currentShards >= $required;
    }
    
    private function canTierUp(array $userChamp, int $currentShards): bool {
        if ($userChamp['stars'] < self::MAX_STARS) return false;
        
        $currentTier = $userChamp['star_tier'] ?? 'white';
        $nextTier = $this->getNextTier($currentTier);
        if (!$nextTier) return false;
        
        $cost = self::TIER_UPGRADE_COST[$currentTier][$nextTier] ?? 0;
        return $currentShards >= $cost;
    }
    
    private function getRequiredShards(string $tier, int $currentStars): int {
        if ($currentStars >= self::MAX_STARS) return 0;
        return self::SHARD_REQUIREMENTS[strtolower($tier)][$currentStars] ?? 9999;
    }
    
    private function getNextTier(string $currentTier): ?string {
        $currentIndex = array_search($currentTier, self::TIER_ORDER);
        if ($currentIndex === false || $currentIndex >= count(self::TIER_ORDER) - 1) {
            return null;
        }
        return self::TIER_ORDER[$currentIndex + 1];
    }
    
    private function isMaxed(array $userChamp): bool {
        $tier = $userChamp['star_tier'] ?? 'white';
        return $tier === 'gold' && $userChamp['stars'] >= self::MAX_STARS;
    }
    
    private function getTotalLevel(array $userChamp): int {
        $tier = $userChamp['star_tier'] ?? 'white';
        $tierIndex = array_search($tier, self::TIER_ORDER);
        if ($tierIndex === false) $tierIndex = 0;
        return ($tierIndex * self::MAX_STARS) + $userChamp['stars'];
    }
    
    private function recalculateStats(int $userChampionId): void {
        $userChamp = $this->userChampionModel->getChampionWithDetails($userChampionId, 0);
        if (!$userChamp) return;
        
        $championModel = new \App\Models\Champion();
        $baseChamp = $championModel->findById($userChamp['champion_id']);
        if (!$baseChamp) return;
        
        $tier = $userChamp['star_tier'] ?? 'white';
        $tierMultiplier = self::TIER_MULTIPLIERS[$tier] ?? 1.0;
        $starMultiplier = 1 + (($userChamp['stars'] - 1) * 0.15);
        $levelMultiplier = 1 + ($userChamp['level'] * 0.05);
        
        $totalMultiplier = $tierMultiplier * $starMultiplier * $levelMultiplier;
        
        $this->userChampionModel->update($userChampionId, [
            'health' => (int)($baseChamp['base_health'] * $totalMultiplier),
            'attack' => (int)($baseChamp['base_attack'] * $totalMultiplier),
            'defense' => (int)($baseChamp['base_defense'] * $totalMultiplier),
            'speed' => (int)($baseChamp['base_speed'] * (1 + (($userChamp['stars'] - 1) * 0.05))),
        ]);
    }
    
    private function getAscendMessage(int $oldStars, int $newStars, string $oldTier, string $newTier): string {
        if ($oldTier !== $newTier) {
            $tierName = ucfirst($newTier);
            return "Champion ascended to {$tierName} tier 1★!";
        }
        return "Champion ascended to {$newStars}★!";
    }
    
    public function getShardRewardsForBattle(string $difficulty, bool $victory): array {
        $baseAmounts = [
            'easy' => ['min' => 3, 'max' => 8],
            'medium' => ['min' => 5, 'max' => 12],
            'hard' => ['min' => 8, 'max' => 18],
            'expert' => ['min' => 12, 'max' => 25]
        ];
        
        $amounts = $baseAmounts[$difficulty] ?? $baseAmounts['easy'];
        
        if (!$victory) {
            $amounts['min'] = (int)($amounts['min'] * 0.5);
            $amounts['max'] = (int)($amounts['max'] * 0.5);
        }
        
        return [
            'min' => $amounts['min'],
            'max' => $amounts['max'],
            'chance' => $victory ? 40 : 20
        ];
    }
    
    public static function getStarBonus(int $stars): float {
        $multipliers = [
            1 => 1.00,
            2 => 1.15,
            3 => 1.35,
            4 => 1.60,
            5 => 1.90,
        ];
        return $multipliers[$stars] ?? 1.0;
    }
    
    public static function getStarsHtml(int $stars): string {
        $html = '<div class="flex gap-1">';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $stars) {
                $html .= '<span class="text-yellow-400 text-xl">⭐</span>';
            } else {
                $html .= '<span class="text-white/20 text-xl">☆</span>';
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    public static function getStarsDisplay(int $stars): string {
        return str_repeat('⭐', $stars) . str_repeat('☆', 5 - $stars);
    }
    
    public function convertDuplicateToShards(int $userId, int $userChampionId): array {
        $userChamp = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$userChamp) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        $champions = $this->userChampionModel->getChampionsByType($userId, $userChamp['champion_id']);
        
        if (count($champions) <= 1) {
            return ['success' => false, 'error' => 'No duplicates to convert'];
        }
        
        $shardAmount = match($userChamp['tier']) {
            'mythic' => random_int(80, 120),
            'legendary' => random_int(50, 80),
            'epic' => random_int(35, 55),
            'rare' => random_int(25, 40),
            default => random_int(15, 30),
        };
        
        $this->shardModel->addShards($userId, $userChamp['champion_id'], $shardAmount);
        
        $this->userChampionModel->softDelete($userChampionId);
        
        return [
            'success' => true,
            'shards_received' => $shardAmount,
            'message' => "Converted duplicate to {$shardAmount} shards!"
        ];
    }
}
