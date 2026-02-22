<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Resource;
use App\Models\User;
use App\Services\AuditService;

class RewardService {
    private Resource $resourceModel;
    private User $userModel;
    private AuditService $auditService;
    
    public function __construct() {
        $this->resourceModel = new Resource();
        $this->userModel = new User();
        $this->auditService = new AuditService();
    }
    
    public function grant(int $userId, array $rewards, string $source = 'system'): array {
        $granted = [];
        
        if (!empty($rewards['gold'])) {
            $this->resourceModel->addGold($userId, (int)$rewards['gold']);
            $granted['gold'] = (int)$rewards['gold'];
        }
        
        if (!empty($rewards['gems'])) {
            $this->resourceModel->addGems($userId, (int)$rewards['gems']);
            $granted['gems'] = (int)$rewards['gems'];
        }
        
        if (!empty($rewards['energy'])) {
            $this->resourceModel->addEnergy($userId, (int)$rewards['energy']);
            $granted['energy'] = (int)$rewards['energy'];
        }
        
        if (!empty($rewards['experience'])) {
            $this->userModel->addExperience($userId, (int)$rewards['experience']);
            $granted['experience'] = (int)$rewards['experience'];
        }
        
        if (!empty($rewards['lootbox_type'])) {
            $count = (int)($rewards['lootbox_count'] ?? 1);
            for ($i = 0; $i < $count; $i++) {
                $this->resourceModel->addLootbox($userId, $rewards['lootbox_type']);
            }
            $granted['lootbox_type'] = $rewards['lootbox_type'];
            $granted['lootbox_count'] = $count;
        }
        
        if (!empty($granted)) {
            $this->auditService->log('reward_granted', 'user', $userId, [
                'source' => $source,
                'rewards' => $granted,
            ]);
        }
        
        return $granted;
    }
    
    public function grantBattleReward(int $userId, array $mission, bool $victory, bool $bonusActive = false): array {
        if (!$victory) {
            return [];
        }
        
        $multiplier = $bonusActive ? 2 : 1;
        
        $rewards = [
            'gold' => $mission['gold_reward'] * $multiplier,
            'experience' => $mission['experience_reward'] * $multiplier,
        ];
        
        if (rand(1, 100) <= $mission['lootbox_chance']) {
            $rewards['lootbox_type'] = $this->determineLootboxType($mission['difficulty']);
            $rewards['lootbox_count'] = 1;
        }
        
        return $this->grant($userId, $rewards, 'battle');
    }
    
    public function grantPvpReward(int $userId, bool $won, int $rating = 1000): array {
        if (!$won) {
            return $this->grant($userId, [
                'gold' => 25,
                'experience' => 10,
            ], 'pvp_defeat');
        }
        
        $baseGold = 100 + floor($rating / 20);
        $baseExp = 50 + floor($rating / 10);
        
        return $this->grant($userId, [
            'gold' => $baseGold,
            'experience' => $baseExp,
        ], 'pvp_victory');
    }
    
    public function grantTournamentReward(int $userId, int $place, array $rewardData): array {
        if ($place > 3) {
            return $this->grant($userId, [
                'gold' => $rewardData['participation_gold'] ?? 100,
            ], 'tournament_participation');
        }
        
        $rewards = [];
        
        if (!empty($rewardData['gold'])) {
            $rewards['gold'] = (int)$rewardData['gold'];
        }
        if (!empty($rewardData['gems'])) {
            $rewards['gems'] = (int)$rewardData['gems'];
        }
        if (!empty($rewardData['lootbox_type'])) {
            $rewards['lootbox_type'] = $rewardData['lootbox_type'];
            $rewards['lootbox_count'] = (int)($rewardData['lootbox_count'] ?? 1);
        }
        
        return $this->grant($userId, $rewards, "tournament_place_{$place}");
    }
    
    public function grantQuestReward(int $userId, array $quest): array {
        $rewards = [];
        
        if (!empty($quest['reward_gold'])) {
            $rewards['gold'] = (int)$quest['reward_gold'];
        }
        if (!empty($quest['reward_gems'])) {
            $rewards['gems'] = (int)$quest['reward_gems'];
        }
        if (!empty($quest['reward_experience'])) {
            $rewards['experience'] = (int)$quest['reward_experience'];
        }
        
        return $this->grant($userId, $rewards, 'quest');
    }
    
    public function grantDailyLoginReward(int $userId, int $streak, array $rewardConfig): array {
        $day = (($streak - 1) % 7) + 1;
        $reward = $rewardConfig[$day] ?? $rewardConfig[1] ?? [];
        
        $rewards = [
            'gold' => $reward['gold'] ?? 50,
        ];
        
        if ($day === 7) {
            $rewards['gems'] = $reward['gems'] ?? 10;
        }
        
        return $this->grant($userId, $rewards, 'daily_login');
    }
    
    public function grantReferralReward(int $userId, string $type): array {
        $rewards = match($type) {
            'referrer_register' => ['gold' => 100],
            'referrer_level5' => ['gold' => 250, 'gems' => 5],
            'referrer_level10' => ['gold' => 500, 'gems' => 10],
            'referee_register' => ['gold' => 100],
            default => [],
        };
        
        return $this->grant($userId, $rewards, "referral_{$type}");
    }
    
    private function determineLootboxType(string $difficulty): string {
        return match($difficulty) {
            'easy' => rand(1, 100) <= 80 ? 'bronze' : 'silver',
            'medium' => rand(1, 100) <= 60 ? 'silver' : 'gold',
            'hard' => rand(1, 100) <= 70 ? 'gold' : 'diamond',
            'expert' => rand(1, 100) <= 50 ? 'gold' : 'diamond',
            default => 'bronze',
        };
    }
}
