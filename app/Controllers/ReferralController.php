<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Referral;
use App\Models\User;
use App\Models\Resource;

class ReferralController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $referralModel = new Referral();
        
        $code = $referralModel->getReferralCode($userId);
        if (!$code) {
            $code = $referralModel->createReferralCode($userId);
        }
        
        $referrals = $referralModel->getReferrals($userId);
        $referralCount = $referralModel->getReferralCount($userId);
        
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
        
        $referralModel = new Referral();
        
        if ($referralModel->useReferralCode($code, $userId)) {
            $resourceModel = new Resource();
            $resourceModel->addGold($userId, 500);
            $resourceModel->addGems($userId, 10);
            
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
        
        $referralModel = new Referral();
        $stmt = $this->db ?? \Core\Database::getInstance()->getConnection();
        $stmt = $stmt->prepare("SELECT * FROM referrals WHERE id = ? AND referrer_id = ?");
        $stmt->execute([$referralId, $userId]);
        $referral = $stmt->fetch();
        
        if (!$referral) {
            $this->jsonError('Referral not found', 404);
            return;
        }
        
        if ($referral['referrer_reward_claimed']) {
            $this->jsonError('Reward already claimed', 400);
            return;
        }
        
        $rewards = $this->getRewardsForStatus($referral['status']);
        
        $resourceModel = new Resource();
        $resourceModel->addGold($userId, $rewards['gold']);
        $resourceModel->addGems($userId, $rewards['gems']);
        
        $stmt = $stmt->prepare("UPDATE referrals SET referrer_reward_claimed = TRUE WHERE id = ?");
        $stmt->execute([$referralId]);
        
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
