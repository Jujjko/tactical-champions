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
    private User $userModel;
    private Resource $resourceModel;
    private UserChampion $userChampionModel;
    private Battle $battleModel;
    private Tutorial $tutorialModel;
    private DailyLoginService $dailyLoginService;
    
    public function __construct() {
        $this->userModel = new User();
        $this->resourceModel = new Resource();
        $this->userChampionModel = new UserChampion();
        $this->battleModel = new Battle();
        $this->tutorialModel = new Tutorial();
        $this->dailyLoginService = new DailyLoginService();
    }
    
    public function index(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('auth/login');
    }
    
    public function dashboard(): void {
        $userId = Session::userId();
        
        $this->resourceModel->regenerateEnergy($userId);
        
        $dailyLoginReward = Session::get('daily_login_reward');
        Session::remove('daily_login_reward');
        
        $this->view('game/dashboard', [
            'user' => $this->userModel->findById($userId),
            'resources' => $this->resourceModel->getUserResources($userId),
            'champions' => $this->userChampionModel->getUserChampions($userId),
            'stats' => $this->battleModel->getUserStats($userId),
            'recentBattles' => $this->battleModel->getRecentBattles($userId, 5),
            'dailyLoginReward' => $dailyLoginReward,
            'loginStatus' => $this->dailyLoginService->getLoginStatus($userId),
            'tutorialCompleted' => $this->tutorialModel->hasCompletedAll($userId),
            'tutorialNextStep' => $this->tutorialModel->getNextStep($userId),
            'tutorialPercent' => $this->tutorialModel->getCompletionPercent($userId),
        ]);
    }
    
    public function inventory(): void {
        $userId = Session::userId();
        
        $this->view('game/inventory', [
            'champions' => $this->userChampionModel->getUserChampions($userId)
        ]);
    }
}
