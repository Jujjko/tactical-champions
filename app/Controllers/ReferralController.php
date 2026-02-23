<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Referral;
use App\Models\Resource;

class ReferralController extends Controller {
    private Referral $referralModel;
    private Resource $resourceModel;
    
    public function __construct() {
        $this->referralModel = new Referral();
        $this->resourceModel = new Resource();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $code = $this->referralModel->getReferralCode($userId);
        if (!$code) {
            $code = $this->referralModel->createReferralCode($userId);
        }
        
        $referrals = $this->referralModel->getReferrals($userId);
        $referralCount = $this->referralModel->getReferralCount($userId);
        
        $this->view('game/referrals', [
            'code' => $code,
            'referrals' => $referrals,
            'referralCount' => $referralCount
        ]);
    }
    
    public function useCode(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $code = strtoupper(trim($_POST['code'] ?? ''));
        
        if (strlen($code) < 6) {
            $this->jsonError('Invalid code', 400);
            return;
        }
        
        if ($this->referralModel->useReferralCode($code, $userId)) {
            $this->resourceModel->addGold($userId, 500);
            $this->resourceModel->addGems($userId, 10);
            
            $this->jsonSuccess(['message' => 'Referral code applied! You received 500 gold and 10 gems!']);
        } else {
            $this->jsonError('Invalid or expired referral code', 400);
        }
    }
    
    public function claim(string $id): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $referralId = (int)$id;
        
        $referral = $this->referralModel->findByIdAndReferrer($referralId, $userId);
        
        if (!$referral) {
            $this->jsonError('Referral not found', 404);
            return;
        }
        
        if ($referral['referrer_reward_claimed']) {
            $this->jsonError('Reward already claimed', 400);
            return;
        }
        
        $rewards = $this->getRewardsForStatus($referral['status']);
        
        $this->resourceModel->addGold($userId, $rewards['gold']);
        $this->resourceModel->addGems($userId, $rewards['gems']);
        
        $this->referralModel->markRewardClaimed($referralId);
        
        $this->jsonSuccess(['message' => 'Reward claimed!', 'rewards' => $rewards]);
    }
    
    private function getRewardsForStatus(string $status): array {
        return match ($status) {
            'registered' => ['gold' => 100, 'gems' => 5],
            'level_5' => ['gold' => 250, 'gems' => 10],
            'level_10' => ['gold' => 500, 'gems' => 25],
            'completed' => ['gold' => 1000, 'gems' => 50],
            default => ['gold' => 0, 'gems' => 0]
        };
    }
}
