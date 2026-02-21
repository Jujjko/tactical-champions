<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\BattlePassSeason;
use App\Models\UserBattlePass;

class BattlePassController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $seasonModel = new BattlePassSeason();
        $userPassModel = new UserBattlePass();
        
        $season = $seasonModel->getActiveSeason();
        
        if (!$season) {
            $this->view('game/battle-pass', [
                'season' => null,
                'progress' => null,
                'rewards' => []
            ]);
            return;
        }
        
        $progress = $userPassModel->getOrCreate($userId, $season['id']);
        $rewards = $seasonModel->getRewards($season['id']);
        
        $this->view('game/battle-pass', [
            'season' => $season,
            'progress' => $progress,
            'rewards' => $rewards
        ]);
    }
    
    public function claim(string $level): void {
        $userId = Session::userId();
        $claimLevel = (int)$level;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $seasonModel = new BattlePassSeason();
        $userPassModel = new UserBattlePass();
        
        $season = $seasonModel->getActiveSeason();
        
        if (!$season) {
            $this->jsonError('No active season', 400);
            return;
        }
        
        $progress = $userPassModel->getProgress($userId, $season['id']);
        
        if (!$progress || $progress['level'] < $claimLevel) {
            $this->jsonError('Level not reached', 400);
            return;
        }
        
        $isPremium = (bool)($_POST['premium'] ?? false);
        
        if ($isPremium && !$progress['is_premium']) {
            $this->jsonError('Premium pass required', 400);
            return;
        }
        
        $rewards = $seasonModel->getRewards($season['id']);
        $reward = null;
        foreach ($rewards as $r) {
            if ($r['level'] === $claimLevel) {
                $reward = $r;
                break;
            }
        }
        
        if (!$reward) {
            $this->jsonError('Reward not found', 404);
            return;
        }
        
        $resourceModel = new \App\Models\Resource();
        
        if ($isPremium) {
            $type = $reward['premium_reward_type'];
            $value = $reward['premium_reward_value'];
        } else {
            $type = $reward['free_reward_type'];
            $value = $reward['free_reward_value'];
        }
        
        if (!$type) {
            $this->jsonError('No reward available', 400);
            return;
        }
        
        switch ($type) {
            case 'gold':
                $resourceModel->addGold($userId, $value);
                break;
            case 'gems':
                $resourceModel->addGems($userId, $value);
                break;
            case 'energy':
                $resourceModel->addEnergy($userId, $value);
                break;
            case 'lootbox':
                $resourceModel->addLootbox($userId, 'gold');
                break;
        }
        
        $this->jsonSuccess(['message' => 'Reward claimed!', 'type' => $type, 'value' => $value]);
    }
}
