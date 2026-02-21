<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Quest;
use App\Models\UserQuest;
use App\Models\Resource;

class QuestController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $userQuestModel = new UserQuest();
        $userQuestModel->initializeUserQuests($userId);
        
        $dailyQuests = $userQuestModel->getUserDailyQuests($userId);
        $weeklyQuests = $userQuestModel->getUserWeeklyQuests($userId);
        $unclaimedCount = $userQuestModel->getUnclaimedCount($userId);
        
        $resourceModel = new Resource();
        $resources = $resourceModel->getUserResources($userId);
        
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
        
        $userQuestModel = new UserQuest();
        $result = $userQuestModel->claimReward($userId, $userQuestId);
        
        if (!$result['success']) {
            $this->jsonError($result['error'] ?? 'Failed to claim reward', 400);
            return;
        }
        
        $rewards = $result['rewards'];
        
        $resourceModel = new Resource();
        if ($rewards['gold'] > 0) {
            $resourceModel->addGold($userId, $rewards['gold']);
        }
        if ($rewards['gems'] > 0) {
            $resourceModel->addGems($userId, $rewards['gems']);
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
        
        $userQuestModel = new UserQuest();
        
        $stmt = $this->db->prepare("
            SELECT uq.id
            FROM user_quests uq
            JOIN quests q ON uq.quest_id = q.id
            WHERE uq.user_id = ? AND uq.completed = TRUE AND uq.claimed = FALSE
        ");
        $stmt->execute([$userId]);
        $quests = $stmt->fetchAll();
        
        $totalGold = 0;
        $totalGems = 0;
        $claimed = 0;
        
        foreach ($quests as $q) {
            $result = $userQuestModel->claimReward($userId, $q['id']);
            if ($result['success']) {
                $totalGold += $result['rewards']['gold'];
                $totalGems += $result['rewards']['gems'];
                $claimed++;
            }
        }
        
        $resourceModel = new Resource();
        if ($totalGold > 0) {
            $resourceModel->addGold($userId, $totalGold);
        }
        if ($totalGems > 0) {
            $resourceModel->addGems($userId, $totalGems);
        }
        
        $this->jsonSuccess([
            'message' => "Claimed {$claimed} quest rewards!",
            'gold' => $totalGold,
            'gems' => $totalGems
        ]);
    }
}
