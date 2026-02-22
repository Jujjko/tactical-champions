<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Lootbox;
use App\Models\Resource;
use App\Models\Champion;
use App\Models\UserChampion;

class LootboxController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $lootboxModel = new Lootbox();
        $counts = $lootboxModel->getLootboxCounts($userId);
        
        $this->view('game/lootbox', [
            'counts' => $counts
        ]);
    }
    
    public function open(string $id): void {
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(['error' => 'Invalid request'], 403);
            return;
        }
        
        $userId = Session::userId();
        $lootboxId = (int)$id;
        
        $lootboxModel = new Lootbox();
        $resourceModel = new Resource();
        $championModel = new Champion();
        $userChampionModel = new UserChampion();
        
        $lootbox = $lootboxModel->findById($lootboxId);
        
        if (!$lootbox || $lootbox['user_id'] !== $userId) {
            $this->json(['error' => 'Lootbox not found'], 404);
            return;
        }
        
        if ($lootbox['opened']) {
            $this->json(['error' => 'Already opened'], 400);
            return;
        }
        
        $rewards = $lootboxModel->openLootbox($lootboxId);
        
        $resourceModel->addGold($userId, $rewards['gold']);
        $resourceModel->addGems($userId, $rewards['gems']);
        
        $championData = null;
        if ($rewards['champion']) {
            $champion = $championModel->getRandomByRarity();
            if ($champion) {
                $userChampionModel->addChampionToUser($userId, $champion['id']);
                $championData = $champion;
            }
        }
        
        $this->json([
            'success' => true,
            'rewards' => $rewards,
            'champion' => $championData
        ]);
    }
    
    public function openMultiple(): void {
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(['error' => 'Invalid request'], 403);
            return;
        }
        
        $userId = Session::userId();
        $type = $_POST['type'] ?? 'bronze';
        $count = min((int)($_POST['count'] ?? 1), 100);
        
        $lootboxModel = new Lootbox();
        $resourceModel = new Resource();
        $championModel = new Champion();
        $userChampionModel = new UserChampion();
        
        $counts = $lootboxModel->getLootboxCounts($userId);
        
        if ($counts[$type] < $count) {
            $count = $counts[$type];
        }
        
        if ($count <= 0) {
            $this->json(['error' => 'No lootboxes available'], 400);
            return;
        }
        
        $lootboxIds = $lootboxModel->getLootboxIdsByType($userId, $type, $count);
        $results = $lootboxModel->openMultiple($lootboxIds);
        
        $resourceModel->addGold($userId, $results['total_gold']);
        $resourceModel->addGems($userId, $results['total_gems']);
        
        $champions = [];
        for ($i = 0; $i < $results['champions_won']; $i++) {
            $champion = $championModel->getRandomByRarity();
            if ($champion) {
                $userChampionModel->addChampionToUser($userId, $champion['id']);
                $champions[] = $champion;
            }
        }
        
        $remainingCounts = $lootboxModel->getLootboxCounts($userId);
        
        $this->json([
            'success' => true,
            'opened' => $results['opened_count'],
            'rewards' => [
                'gold' => $results['total_gold'],
                'gems' => $results['total_gems']
            ],
            'champions' => $champions,
            'by_type' => $results['by_type'],
            'remaining' => $remainingCounts
        ]);
    }
}