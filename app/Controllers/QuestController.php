<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Quest;
use App\Models\UserQuest;
use App\Models\Resource;

class QuestController extends Controller {
    private Quest $questModel;
    private UserQuest $userQuestModel;
    private Resource $resourceModel;
    
    public function __construct() {
        $this->questModel = new Quest();
        $this->userQuestModel = new UserQuest();
        $this->resourceModel = new Resource();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $this->userQuestModel->initializeUserQuests($userId);
        
        $dailyQuests = $this->userQuestModel->getUserDailyQuests($userId);
        $weeklyQuests = $this->userQuestModel->getUserWeeklyQuests($userId);
        $unclaimedCount = $this->userQuestModel->getUnclaimedCount($userId);
        
        $resources = $this->resourceModel->getUserResources($userId);
        
        $this->view('game/quests', [
            'dailyQuests' => $dailyQuests,
            'weeklyQuests' => $weeklyQuests,
            'unclaimedCount' => $unclaimedCount,
            'resources' => $resources
        ]);
    }
    
    public function claim(string $id): void {
        $userId = Session::userId();
        $userQuestId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->userQuestModel->claimReward($userId, $userQuestId);
        
        if (!$result['success']) {
            $this->jsonError($result['error'] ?? 'Failed to claim reward', 400);
            return;
        }
        
        $rewards = $result['rewards'];
        
        if ($rewards['gold'] > 0) {
            $this->resourceModel->addGold($userId, $rewards['gold']);
        }
        if ($rewards['gems'] > 0) {
            $this->resourceModel->addGems($userId, $rewards['gems']);
        }
        
        $this->jsonSuccess([
            'message' => 'Reward claimed!',
            'rewards' => $rewards
        ]);
    }
    
    public function claimAll(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $unclaimedQuests = $this->userQuestModel->getUnclaimedQuests($userId);
        
        $totalGold = 0;
        $totalGems = 0;
        $claimed = 0;
        
        foreach ($unclaimedQuests as $q) {
            $result = $this->userQuestModel->claimReward($userId, $q['id']);
            if ($result['success']) {
                $totalGold += $result['rewards']['gold'] ?? 0;
                $totalGems += $result['rewards']['gems'] ?? 0;
                $claimed++;
            }
        }
        
        if ($totalGold > 0) {
            $this->resourceModel->addGold($userId, $totalGold);
        }
        if ($totalGems > 0) {
            $this->resourceModel->addGems($userId, $totalGems);
        }
        
        $this->jsonSuccess([
            'message' => "Claimed {$claimed} quest rewards!",
            'gold' => $totalGold,
            'gems' => $totalGems
        ]);
    }
}
