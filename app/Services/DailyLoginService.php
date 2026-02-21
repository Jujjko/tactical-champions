<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Resource;
use Core\Database;

class DailyLoginService {
    private User $userModel;
    private Resource $resourceModel;
    private Database $db;
    
    private const STREAK_REWARDS = [
        1 => ['gold' => 100, 'gems' => 0, 'energy' => 20, 'bonus' => null],
        2 => ['gold' => 150, 'gems' => 0, 'energy' => 25, 'bonus' => null],
        3 => ['gold' => 200, 'gems' => 1, 'energy' => 30, 'bonus' => null],
        4 => ['gold' => 250, 'gems' => 2, 'energy' => 35, 'bonus' => null],
        5 => ['gold' => 300, 'gems' => 3, 'energy' => 40, 'bonus' => 'energy_boost'],
        6 => ['gold' => 400, 'gems' => 5, 'energy' => 50, 'bonus' => null],
        7 => ['gold' => 500, 'gems' => 10, 'energy' => 100, 'bonus' => 'weekly_chest'],
    ];
    
    public function __construct() {
        $this->userModel = new User();
        $this->resourceModel = new Resource();
        $this->db = Database::getInstance();
    }
    
    public function processDailyLogin(int $userId): array {
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        $today = date('Y-m-d');
        $lastLogin = $user['last_daily_login'];
        
        if ($lastLogin === $today) {
            return [
                'success' => true,
                'already_claimed' => true,
                'streak' => $user['login_streak'],
                'next_reward' => $this->getNextReward($user['login_streak'] + 1),
                'streak_reset' => false
            ];
        }
        
        $streak = $this->calculateNewStreak($lastLogin, $user['login_streak']);
        $streakDay = (($streak - 1) % 7) + 1;
        
        $rewards = $this->calculateRewards($streakDay);
        
        $this->grantRewards($userId, $rewards);
        $this->updateUserStreak($userId, $streak, $today);
        $this->logReward($userId, $today, $streakDay, $rewards);
        
        return [
            'success' => true,
            'already_claimed' => false,
            'streak' => $streak,
            'streak_day' => $streakDay,
            'rewards' => $rewards,
            'bonus' => $rewards['bonus'],
            'streak_reset' => $streak < $user['login_streak']
        ];
    }
    
    public function getLoginStatus(int $userId): array {
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            return ['error' => 'User not found'];
        }
        
        $today = date('Y-m-d');
        $claimed = $user['last_daily_login'] === $today;
        $streak = $user['login_streak'];
        $streakDay = $claimed ? (($streak - 1) % 7) + 1 : ($streak % 7) + 1;
        
        return [
            'streak' => $streak,
            'streak_day' => $streakDay,
            'total_logins' => $user['total_daily_logins'],
            'claimed_today' => $claimed,
            'next_reward' => $this->getNextReward($claimed ? $streak + 1 : $streakDay),
            'weekly_progress' => $this->getWeeklyProgress($streak),
            'rewards_schedule' => $this->getRewardsSchedule()
        ];
    }
    
    private function calculateNewStreak(?string $lastLogin, int $currentStreak): int {
        if (!$lastLogin) {
            return 1;
        }
        
        $lastDate = strtotime($lastLogin);
        $today = strtotime(date('Y-m-d'));
        $diff = ($today - $lastDate) / 86400;
        
        if ($diff <= 0) {
            return $currentStreak;
        }
        
        if ($diff === 1.0) {
            return $currentStreak + 1;
        }
        
        return 1;
    }
    
    private function calculateRewards(int $streakDay): array {
        $day = min($streakDay, 7);
        $reward = self::STREAK_REWARDS[$day];
        
        return [
            'gold' => $reward['gold'],
            'gems' => $reward['gems'],
            'energy' => $reward['energy'],
            'bonus' => $reward['bonus'],
            'day' => $day
        ];
    }
    
    private function grantRewards(int $userId, array $rewards): void {
        if ($rewards['gold'] > 0) {
            $this->resourceModel->addGold($userId, $rewards['gold']);
        }
        
        if ($rewards['gems'] > 0) {
            $this->resourceModel->addGems($userId, $rewards['gems']);
        }
        
        if ($rewards['energy'] > 0) {
            $this->resourceModel->addEnergy($userId, $rewards['energy']);
        }
    }
    
    private function updateUserStreak(int $userId, int $streak, string $today): void {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_streak = ?, 
                last_daily_login = ?,
                total_daily_logins = total_daily_logins + 1
            WHERE id = ?
        ");
        $stmt->execute([$streak, $today, $userId]);
    }
    
    private function logReward(int $userId, string $date, int $streakDay, array $rewards): void {
        $stmt = $this->db->prepare("
            INSERT INTO daily_login_rewards (user_id, login_date, streak_day, gold_reward, gems_reward, energy_reward, bonus_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $date,
            $streakDay,
            $rewards['gold'],
            $rewards['gems'],
            $rewards['energy'],
            $rewards['bonus']
        ]);
    }
    
    private function getNextReward(int $streakDay): array {
        $day = (($streakDay - 1) % 7) + 1;
        return $this->calculateRewards($day);
    }
    
    private function getWeeklyProgress(int $streak): array {
        $currentDay = (($streak - 1) % 7) + 1;
        $progress = [];
        
        for ($i = 1; $i <= 7; $i++) {
            $progress[$i] = [
                'claimed' => $i < $currentDay,
                'current' => $i === $currentDay,
                'rewards' => self::STREAK_REWARDS[$i]
            ];
        }
        
        return $progress;
    }
    
    private function getRewardsSchedule(): array {
        return self::STREAK_REWARDS;
    }
}
