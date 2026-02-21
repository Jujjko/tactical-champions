<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Achievement;
use App\Models\UserAchievement;

class AchievementController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $achievementModel = new Achievement();
        $userAchievementModel = new UserAchievement();
        
        $achievements = $achievementModel->getUserAchievements($userId);
        $completedCount = $achievementModel->getCompletedCount($userId);
        $totalPoints = $achievementModel->getTotalPoints($userId);
        
        $byCategory = [];
        foreach ($achievements as $a) {
            $byCategory[$a['category']][] = $a;
        }
        
        $this->view('game/achievements', [
            'achievementsByCategory' => $byCategory,
            'completedCount' => $completedCount,
            'totalPoints' => $totalPoints,
            'totalCount' => count($achievements)
        ]);
    }
    
    public function claim(string $id): void {
        $userId = Session::userId();
        $achievementId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $userAchievementModel = new UserAchievement();
        
        if ($userAchievementModel->claimReward($userId, $achievementId)) {
            $this->jsonSuccess(['message' => 'Reward claimed!']);
        } else {
            $this->jsonError('Cannot claim reward', 400);
        }
    }
}
