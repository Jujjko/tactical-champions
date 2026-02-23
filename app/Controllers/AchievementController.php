<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Achievement;
use App\Models\UserAchievement;

class AchievementController extends Controller {
    private Achievement $achievementModel;
    private UserAchievement $userAchievementModel;
    
    public function __construct() {
        $this->achievementModel = new Achievement();
        $this->userAchievementModel = new UserAchievement();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $achievements = $this->achievementModel->getUserAchievements($userId);
        $completedCount = $this->achievementModel->getCompletedCount($userId);
        $totalPoints = $this->achievementModel->getTotalPoints($userId);
        
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
        
        if ($this->userAchievementModel->claimReward($userId, $achievementId)) {
            $this->jsonSuccess(['message' => 'Reward claimed!']);
        } else {
            $this->jsonError('Cannot claim reward', 400);
        }
    }
}
