<?php
declare(strict_types=1);

namespace App\Helpers;

class RankHelper
{
    private const RANKS = [
        ['name' => 'Bronze', 'min' => 0, 'max' => 1199, 'color' => '#cd7f32', 'icon' => '🥉'],
        ['name' => 'Silver', 'min' => 1200, 'max' => 1499, 'color' => '#c0c0c0', 'icon' => '🥈'],
        ['name' => 'Gold', 'min' => 1500, 'max' => 1849, 'color' => '#ffd700', 'icon' => '🥇'],
        ['name' => 'Platinum', 'min' => 1850, 'max' => 2199, 'color' => '#e5e4e2', 'icon' => '💎'],
        ['name' => 'Diamond', 'min' => 2200, 'max' => 2599, 'color' => '#b9f2ff', 'icon' => '💠'],
        ['name' => 'Master', 'min' => 2600, 'max' => 2999, 'color' => '#9945ff', 'icon' => '👑'],
        ['name' => 'Grandmaster', 'min' => 3000, 'max' => 99999, 'color' => '#ff4500', 'icon' => '🏆'],
    ];

    private const TIERS = ['V', 'IV', 'III', 'II', 'I'];

    public static function getRank(int $rating): array
    {
        foreach (self::RANKS as $rank) {
            if ($rating >= $rank['min'] && $rating <= $rank['max']) {
                $tier = self::calculateTier($rating, $rank['min'], $rank['max']);
                return [
                    'name' => $rank['name'],
                    'tier' => $tier,
                    'full_name' => $rank['name'] . ' ' . $tier,
                    'icon' => $rank['icon'],
                    'color' => $rank['color'],
                    'rating' => $rating,
                    'next_rank' => self::getNextRank($rating),
                    'progress' => self::calculateProgress($rating, $rank['min'], $rank['max']),
                ];
            }
        }
        
        return self::RANKS[0] + ['tier' => 'V', 'full_name' => 'Bronze V', 'progress' => 0];
    }

    public static function getRankIcon(int $rating): string
    {
        $rank = self::getRank($rating);
        return $rank['icon'];
    }

    public static function getRankBadge(int $rating): string
    {
        $rank = self::getRank($rating);
        return sprintf(
            '<span style="color: %s; text-shadow: 0 0 10px %s40;">%s</span> <span class="font-bold">%s %s</span>',
            $rank['color'],
            $rank['color'],
            $rank['icon'],
            $rank['name'],
            $rank['tier']
        );
    }

    public static function getRankBadgeHtml(int $rating): string
    {
        $rank = self::getRank($rating);
        return sprintf(
            '<div class="flex items-center gap-2 px-3 py-2 rounded-xl" style="background: linear-gradient(135deg, %s20, %s10); border: 1px solid %s40;">
                <span class="text-2xl">%s</span>
                <div>
                    <div class="font-bold" style="color: %s;">%s</div>
                    <div class="text-xs text-white/60">%d Rating</div>
                </div>
            </div>',
            $rank['color'],
            $rank['color'],
            $rank['color'],
            $rank['icon'],
            $rank['color'],
            $rank['full_name'],
            $rating
        );
    }

    public static function getRankColor(int $rating): string
    {
        $rank = self::getRank($rating);
        return $rank['color'];
    }

    public static function getAllRanks(): array
    {
        return self::RANKS;
    }

    private static function calculateTier(int $rating, int $min, int $max): string
    {
        if ($rating >= $max - 50) {
            return 'I';
        }
        
        $range = $max - $min;
        $position = $rating - $min;
        $tierIndex = (int)floor(($position / $range) * 5);
        $tierIndex = min(4, max(0, $tierIndex));
        
        return self::TIERS[$tierIndex];
    }

    private static function calculateProgress(int $rating, int $min, int $max): int
    {
        if ($rating >= $max) {
            return 100;
        }
        
        $range = $max - $min;
        $position = $rating - $min;
        
        return (int)(($position / $range) * 100);
    }

    private static function getNextRank(int $rating): ?array
    {
        foreach (self::RANKS as $index => $rank) {
            if ($rating >= $rank['min'] && $rating <= $rank['max']) {
                if (isset(self::RANKS[$index + 1])) {
                    $next = self::RANKS[$index + 1];
                    return [
                        'name' => $next['name'],
                        'icon' => $next['icon'],
                        'color' => $next['color'],
                        'required_rating' => $next['min'],
                        'rating_needed' => $next['min'] - $rating,
                    ];
                }
                return null;
            }
        }
        return null;
    }
}
