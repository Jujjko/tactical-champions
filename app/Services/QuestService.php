<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UserQuest;

class QuestService {
    private UserQuest $userQuest;
    
    public function __construct() {
        $this->userQuest = new UserQuest();
    }
    
    public function trackProgress(int $userId, string $type, int $amount = 1): void {
        $this->userQuest->updateProgress($userId, $type, $amount);
    }
    
    public function trackBattle(int $userId, bool $won): void {
        $this->trackProgress($userId, 'battles', 1);
        
        if ($won) {
            $this->trackProgress($userId, 'battles_won', 1);
        }
    }
    
    public function trackMission(int $userId): void {
        $this->trackProgress($userId, 'missions_completed', 1);
    }
    
    public function trackChampionUpgrade(int $userId): void {
        $this->trackProgress($userId, 'champion_upgrades', 1);
    }
    
    public function trackEquipmentChange(int $userId): void {
        $this->trackProgress($userId, 'equipment_changes', 1);
    }
    
    public function trackEnergySpent(int $userId, int $amount): void {
        $this->trackProgress($userId, 'energy_spent', $amount);
    }
    
    public function trackLootboxOpened(int $userId): void {
        $this->trackProgress($userId, 'lootboxes_opened', 1);
    }
    
    public function trackSocialAction(int $userId): void {
        $this->trackProgress($userId, 'social_actions', 1);
    }
    
    public function trackPvpBattle(int $userId): void {
        $this->trackProgress($userId, 'pvp_battles', 1);
    }
    
    public function trackGuildAction(int $userId): void {
        $this->trackProgress($userId, 'guild_actions', 1);
    }
}
