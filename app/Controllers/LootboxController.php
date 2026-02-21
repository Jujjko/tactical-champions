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
        $lootboxes = $lootboxModel->getUserLootboxes($userId);
        
        $this->view('game/lootbox', [
            'lootboxes' => $lootboxes
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
        
        // Award resources
        $resourceModel->addGold($userId, $rewards['gold']);
        $resourceModel->addGems($userId, $rewards['gems']);
        
        // Award champion if won
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
}