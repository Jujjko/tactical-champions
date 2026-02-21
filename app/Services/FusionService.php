<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UserChampion;
use App\Models\Resource;
use Core\Session;

class FusionService
{
    private UserChampion $userChampionModel;
    private Resource $resourceModel;
    
    private const FUSION_GOLD_COST = 1000;
    private const FUSION_GEMS_COST = 10;
    
    private const STAR_BONUS_MULTIPLIER = [
        1 => 1.00,
        2 => 1.10,
        3 => 1.25,
        4 => 1.45,
        5 => 1.70,
    ];
    
    public function __construct()
    {
        $this->userChampionModel = new UserChampion();
        $this->resourceModel = new Resource();
    }
    
    public function getFusionCandidates(int $userId, int $userChampionId): array
    {
        $targetChampion = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$targetChampion) {
            return [];
        }
        
        if ($targetChampion['stars'] >= 5) {
            return [];
        }
        
        $stmt = $this->userChampionModel->getDb()->prepare("
            SELECT uc.*, c.name, c.tier
            FROM user_champions uc
            JOIN champions c ON uc.champion_id = c.id
            WHERE uc.user_id = ?
              AND uc.champion_id = ?
              AND uc.id != ?
              AND uc.stars = ?
              AND uc.deleted_at IS NULL
            ORDER BY uc.level DESC
        ");
        $stmt->execute([
            $userId,
            $targetChampion['champion_id'],
            $userChampionId,
            $targetChampion['stars']
        ]);
        
        return $stmt->fetchAll();
    }
    
    public function canFuse(int $userId, int $userChampionId): array
    {
        $candidates = $this->getFusionCandidates($userId, $userChampionId);
        $resources = $this->resourceModel->getUserResources($userId);
        
        $targetChampion = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        $hasCandidates = count($candidates) > 0;
        $hasEnoughGold = $resources['gold'] >= self::FUSION_GOLD_COST;
        $hasEnoughGems = $resources['gems'] >= self::FUSION_GEMS_COST;
        $isMaxStars = $targetChampion && $targetChampion['stars'] >= 5;
        
        return [
            'can_fuse' => $hasCandidates && $hasEnoughGold && $hasEnoughGems && !$isMaxStars,
            'has_candidates' => $hasCandidates,
            'candidate_count' => count($candidates),
            'has_enough_gold' => $hasEnoughGold,
            'has_enough_gems' => $hasEnoughGems,
            'gold_cost' => self::FUSION_GOLD_COST,
            'gems_cost' => self::FUSION_GEMS_COST,
            'is_max_stars' => $isMaxStars,
        ];
    }
    
    public function fuse(int $userId, int $targetId, int $materialId): array
    {
        $targetChampion = $this->userChampionModel->getChampionWithDetails($targetId, $userId);
        $materialChampion = $this->userChampionModel->getChampionWithDetails($materialId, $userId);
        
        if (!$targetChampion || !$materialChampion) {
            return ['success' => false, 'error' => 'Champion not found'];
        }
        
        if ($targetChampion['user_id'] !== $userId || $materialChampion['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Invalid ownership'];
        }
        
        if ($targetChampion['champion_id'] !== $materialChampion['champion_id']) {
            return ['success' => false, 'error' => 'Champions must be the same type'];
        }
        
        if ($targetChampion['stars'] !== $materialChampion['stars']) {
            return ['success' => false, 'error' => 'Champions must have the same star level'];
        }
        
        if ($targetChampion['stars'] >= 5) {
            return ['success' => false, 'error' => 'Champion is already at max stars'];
        }
        
        $resources = $this->resourceModel->getUserResources($userId);
        
        if ($resources['gold'] < self::FUSION_GOLD_COST) {
            return ['success' => false, 'error' => 'Not enough gold'];
        }
        
        if ($resources['gems'] < self::FUSION_GEMS_COST) {
            return ['success' => false, 'error' => 'Not enough gems'];
        }
        
        $this->resourceModel->update($resources['id'], [
            'gold' => $resources['gold'] - self::FUSION_GOLD_COST,
            'gems' => $resources['gems'] - self::FUSION_GEMS_COST,
        ]);
        
        $newStars = $targetChampion['stars'] + 1;
        $bonusMultiplier = self::STAR_BONUS_MULTIPLIER[$newStars];
        
        $newHealth = (int)($targetChampion['health'] * $bonusMultiplier);
        $newAttack = (int)($targetChampion['attack'] * $bonusMultiplier);
        $newDefense = (int)($targetChampion['defense'] * $bonusMultiplier);
        $newSpeed = (int)($targetChampion['speed'] * $bonusMultiplier);
        
        $this->userChampionModel->update($targetId, [
            'stars' => $newStars,
            'health' => $newHealth,
            'attack' => $newAttack,
            'defense' => $newDefense,
            'speed' => $newSpeed,
        ]);
        
        $this->userChampionModel->update($materialId, [
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
        
        return [
            'success' => true,
            'new_stars' => $newStars,
            'new_stats' => [
                'health' => $newHealth,
                'attack' => $newAttack,
                'defense' => $newDefense,
                'speed' => $newSpeed,
            ],
            'gold_spent' => self::FUSION_GOLD_COST,
            'gems_spent' => self::FUSION_GEMS_COST,
        ];
    }
    
    public function getStarBonus(int $stars): float
    {
        return self::STAR_BONUS_MULTIPLIER[$stars] ?? 1.0;
    }
    
    public function getFusionCost(): array
    {
        return [
            'gold' => self::FUSION_GOLD_COST,
            'gems' => self::FUSION_GEMS_COST,
        ];
    }
    
    public static function getStarsDisplay(int $stars): string
    {
        return str_repeat('⭐', $stars) . str_repeat('☆', 5 - $stars);
    }
    
    public static function getStarsHtml(int $stars): string
    {
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
}