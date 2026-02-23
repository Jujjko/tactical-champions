<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Lootbox;
use App\Models\Resource;
use App\Models\Champion;
use App\Models\UserChampion;
use App\Models\ChampionShard;

class LootboxController extends Controller {
    private Lootbox $lootboxModel;
    private Resource $resourceModel;
    private Champion $championModel;
    private UserChampion $userChampionModel;
    private ChampionShard $shardModel;
    
    private const FIRST_DROP_SHARDS = ['min' => 5, 'max' => 10];
    private const DUPLICATE_SHARDS = [
        'bronze' => ['min' => 12, 'max' => 18],
        'silver' => ['min' => 15, 'max' => 22],
        'gold' => ['min' => 18, 'max' => 25],
        'diamond' => ['min' => 25, 'max' => 35]
    ];
    
    public function __construct() {
        $this->lootboxModel = new Lootbox();
        $this->resourceModel = new Resource();
        $this->championModel = new Champion();
        $this->userChampionModel = new UserChampion();
        $this->shardModel = new ChampionShard();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $counts = $this->lootboxModel->getLootboxCounts($userId);
        
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
        
        $lootbox = $this->lootboxModel->findById($lootboxId);
        
        if (!$lootbox || $lootbox['user_id'] !== $userId) {
            $this->json(['error' => 'Lootbox not found'], 404);
            return;
        }
        
        if ($lootbox['opened']) {
            $this->json(['error' => 'Already opened'], 400);
            return;
        }
        
        $rewards = $this->lootboxModel->openLootbox($lootboxId);
        
        $this->resourceModel->addGold($userId, $rewards['gold']);
        $this->resourceModel->addGems($userId, $rewards['gems']);
        
        $championData = null;
        $shardData = null;
        $isFirstDrop = false;
        
        if ($rewards['champion']) {
            $champion = $this->championModel->getRandomByRarity();
            if ($champion) {
                $alreadyOwned = $this->userChampionModel->hasChampion($userId, $champion['id']);
                $lootboxType = $lootbox['lootbox_type'] ?? 'bronze';
                
                if ($alreadyOwned) {
                    $shardAmount = random_int(
                        self::DUPLICATE_SHARDS[$lootboxType]['min'] ?? 12,
                        self::DUPLICATE_SHARDS[$lootboxType]['max'] ?? 18
                    );
                    $this->shardModel->addShards($userId, $champion['id'], $shardAmount);
                    $shardData = [
                        'champion_id' => $champion['id'],
                        'champion_name' => $champion['name'],
                        'amount' => $shardAmount,
                        'is_duplicate' => true
                    ];
                } else {
                    $this->userChampionModel->addChampionToUser($userId, $champion['id']);
                    $shardAmount = random_int(self::FIRST_DROP_SHARDS['min'], self::FIRST_DROP_SHARDS['max']);
                    $this->shardModel->addShards($userId, $champion['id'], $shardAmount);
                    $championData = $champion;
                    $shardData = [
                        'champion_id' => $champion['id'],
                        'champion_name' => $champion['name'],
                        'amount' => $shardAmount,
                        'is_duplicate' => false
                    ];
                    $isFirstDrop = true;
                }
            }
        }
        
        $this->json([
            'success' => true,
            'rewards' => $rewards,
            'champion' => $championData,
            'shards' => $shardData,
            'is_first_drop' => $isFirstDrop
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
        
        $counts = $this->lootboxModel->getLootboxCounts($userId);
        
        if ($counts[$type] < $count) {
            $count = $counts[$type];
        }
        
        if ($count <= 0) {
            $this->json(['error' => 'No lootboxes available'], 400);
            return;
        }
        
        $lootboxIds = $this->lootboxModel->getLootboxIdsByType($userId, $type, $count);
        $results = $this->lootboxModel->openMultiple($lootboxIds);
        
        $this->resourceModel->addGold($userId, $results['total_gold']);
        $this->resourceModel->addGems($userId, $results['total_gems']);
        
        $newChampions = [];
        $shardsWon = [];
        $firstDrops = 0;
        
        for ($i = 0; $i < $results['champions_won']; $i++) {
            $champion = $this->championModel->getRandomByRarity();
            if ($champion) {
                $alreadyOwned = $this->userChampionModel->hasChampion($userId, $champion['id']);
                
                if ($alreadyOwned) {
                    $shardAmount = random_int(
                        self::DUPLICATE_SHARDS[$type]['min'] ?? 12,
                        self::DUPLICATE_SHARDS[$type]['max'] ?? 18
                    );
                    $this->shardModel->addShards($userId, $champion['id'], $shardAmount);
                    $shardsWon[] = [
                        'champion_id' => $champion['id'],
                        'champion_name' => $champion['name'],
                        'amount' => $shardAmount,
                        'is_duplicate' => true
                    ];
                } else {
                    $this->userChampionModel->addChampionToUser($userId, $champion['id']);
                    $shardAmount = random_int(self::FIRST_DROP_SHARDS['min'], self::FIRST_DROP_SHARDS['max']);
                    $this->shardModel->addShards($userId, $champion['id'], $shardAmount);
                    $newChampions[] = $champion;
                    $shardsWon[] = [
                        'champion_id' => $champion['id'],
                        'champion_name' => $champion['name'],
                        'amount' => $shardAmount,
                        'is_duplicate' => false
                    ];
                    $firstDrops++;
                }
            }
        }
        
        $remainingCounts = $this->lootboxModel->getLootboxCounts($userId);
        
        $this->json([
            'success' => true,
            'opened' => $results['opened_count'],
            'rewards' => [
                'gold' => $results['total_gold'],
                'gems' => $results['total_gems']
            ],
            'new_champions' => $newChampions,
            'shards' => $shardsWon,
            'first_drops' => $firstDrops,
            'by_type' => $results['by_type'],
            'remaining' => $remainingCounts
        ]);
    }
}
