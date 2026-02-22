<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Resource;
use App\Models\User;
use Core\Database;
use Core\Session;
use PDO;

class AchievementService {
    private Achievement $achievementModel;
    private UserAchievement $userAchievementModel;
    private Resource $resourceModel;
    private User $userModel;
    private PDO $db;
    
    public function __construct() {
        $this->achievementModel = new Achievement();
        $this->userAchievementModel = new UserAchievement();
        $this->resourceModel = new Resource();
        $this->userModel = new User();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function checkAndUnlock(int $userId, string $type, int $value = 1): array {
        try {
            $achievements = $this->getAchievementsByType($type);
            $unlocked = [];
            
            foreach ($achievements as $achievement) {
                $progress = $this->getProgress($userId, $achievement['id']);
                $newProgress = $this->calculateNewProgress($type, $value, $progress);
                
                if ($newProgress >= $achievement['requirement_value'] && !$this->isCompleted($userId, $achievement['id'])) {
                    $this->unlock($userId, $achievement);
                    $unlocked[] = $achievement;
                } elseif ($newProgress > ($progress['progress'] ?? 0)) {
                    $this->updateProgress($userId, $achievement['id'], $newProgress);
                }
            }
            
            return $unlocked;
        } catch (\Exception $e) {
            error_log("AchievementService::checkAndUnlock error: " . $e->getMessage());
            return [];
        }
    }
    
    public function trackBattleWin(int $userId): array {
        return $this->checkAndUnlock($userId, 'battles_won', 1);
    }
    
    public function trackChampionCollected(int $userId, int $count = 1): array {
        return $this->checkAndUnlock($userId, 'champions_owned', $count);
    }
    
    public function trackLevelUp(int $userId, int $level): array {
        return $this->checkAndUnlock($userId, 'player_level', $level);
    }
    
    public function trackFriendAdded(int $userId): array {
        return $this->checkAndUnlock($userId, 'friends_added', 1);
    }
    
    public function trackMissionCompleted(int $userId): array {
        return $this->checkAndUnlock($userId, 'missions_completed', 1);
    }
    
    public function trackPvpWin(int $userId): array {
        return $this->checkAndUnlock($userId, 'pvp_wins', 1);
    }
    
    public function trackLoginStreak(int $userId, int $days): array {
        return $this->checkAndUnlock($userId, 'login_streak', $days);
    }
    
    public function trackLootboxOpened(int $userId): array {
        return $this->checkAndUnlock($userId, 'lootboxes_opened', 1);
    }
    
    private function getAchievementsByType(string $type): array {
        try {
            return $this->achievementModel->where('requirement_type', $type);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getProgress(int $userId, int $achievementId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_achievements 
                WHERE user_id = ? AND achievement_id = ?
            ");
            $stmt->execute([$userId, $achievementId]);
            $result = $stmt->fetch();
            return $result ?: ['progress' => 0];
        } catch (\Exception $e) {
            return ['progress' => 0];
        }
    }
    
    private function calculateNewProgress(string $type, int $value, array $currentProgress): int {
        $cumulativeTypes = ['battles_won', 'champions_owned', 'friends_added', 'missions_completed', 
                          'pvp_wins', 'lootboxes_opened', 'login_streak'];
        
        if (in_array($type, $cumulativeTypes)) {
            return ($currentProgress['progress'] ?? 0) + $value;
        }
        
        if ($type === 'player_level') {
            return $value;
        }
        
        return max($currentProgress['progress'] ?? 0, $value);
    }
    
    private function isCompleted(int $userId, int $achievementId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT completed FROM user_achievements 
                WHERE user_id = ? AND achievement_id = ?
            ");
            $stmt->execute([$userId, $achievementId]);
            $result = $stmt->fetch();
            return $result && (bool)$result['completed'];
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function updateProgress(int $userId, int $achievementId, int $progress): void {
        try {
            $this->userAchievementModel->updateProgress($userId, $achievementId, $progress);
        } catch (\Exception $e) {
            error_log("AchievementService::updateProgress error: " . $e->getMessage());
        }
    }
    
    private function unlock(int $userId, array $achievement): void {
        try {
            $this->userAchievementModel->updateProgress($userId, $achievement['id'], $achievement['requirement_value']);
            
            if (($achievement['reward_gold'] ?? 0) > 0) {
                $this->resourceModel->addGold($userId, $achievement['reward_gold']);
            }
            if (($achievement['reward_gems'] ?? 0) > 0) {
                $this->resourceModel->addGems($userId, $achievement['reward_gems']);
            }
            if (($achievement['reward_experience'] ?? 0) > 0) {
                $this->userModel->addExperience($userId, $achievement['reward_experience']);
            }
            
            Session::flash('achievement_unlocked', [
                'name' => $achievement['name'],
                'description' => $achievement['description'] ?? '',
                'icon' => $achievement['icon'] ?? 'trophy',
                'reward_gold' => $achievement['reward_gold'] ?? 0,
                'reward_gems' => $achievement['reward_gems'] ?? 0,
            ]);
        } catch (\Exception $e) {
            error_log("AchievementService::unlock error: " . $e->getMessage());
        }
    }
    
    public function getUnlockedFlash(): ?array {
        return Session::getFlash('achievement_unlocked');
    }
}
