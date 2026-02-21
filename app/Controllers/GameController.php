<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\User;
use App\Models\Resource;
use App\Models\UserChampion;
use App\Models\Battle;
use App\Models\Tutorial;
use App\Services\DailyLoginService;

class GameController extends Controller {
    public function index(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('auth/login');
    }
    
    public function dashboard(): void {
        $userId = Session::userId();
        
        $userModel = new User();
        $resourceModel = new Resource();
        $championModel = new UserChampion();
        $battleModel = new Battle();
        $dailyLoginService = new DailyLoginService();
        $tutorialModel = new Tutorial();
        
        $resourceModel->regenerateEnergy($userId);
        
        $dailyLoginReward = Session::get('daily_login_reward');
        Session::remove('daily_login_reward');
        
        $this->view('game/dashboard', [
            'user' => $userModel->findById($userId),
            'resources' => $resourceModel->getUserResources($userId),
            'champions' => $championModel->getUserChampions($userId),
            'stats' => $battleModel->getUserStats($userId),
            'recentBattles' => $battleModel->getRecentBattles($userId, 5),
            'dailyLoginReward' => $dailyLoginReward,
            'loginStatus' => $dailyLoginService->getLoginStatus($userId),
            'tutorialCompleted' => $tutorialModel->hasCompletedAll($userId),
            'tutorialNextStep' => $tutorialModel->getNextStep($userId),
            'tutorialPercent' => $tutorialModel->getCompletionPercent($userId),
        ]);
    }
    
    public function inventory(): void {
        $userId = Session::userId();
        
        $championModel = new UserChampion();
        
        $this->view('game/inventory', [
            'champions' => $championModel->getUserChampions($userId)
        ]);
    }
}