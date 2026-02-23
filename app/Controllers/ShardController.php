<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\UserChampion;
use App\Models\ChampionShard;
use App\Services\AscensionService;

class ShardController extends Controller {
    private ChampionShard $championShardModel;
    private UserChampion $userChampionModel;
    private AscensionService $ascensionService;
    
    public function __construct() {
        $this->championShardModel = new ChampionShard();
        $this->userChampionModel = new UserChampion();
        $this->ascensionService = new AscensionService();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $shards = $this->championShardModel->getAllForUser($userId);
        $champions = $this->userChampionModel->getUserChampionsWithShards($userId);
        
        $this->view('game/shards', [
            'shards' => $shards,
            'champions' => $champions
        ]);
    }
    
    public function show(int $id): void {
        $userId = Session::userId();
        $userChampionId = (int)$id;
        
        $champion = $this->userChampionModel->getChampionWithDetails($userChampionId, $userId);
        
        if (!$champion) {
            $this->redirect('/shards');
            return;
        }
        
        $shards = $this->championShardModel->getAmount($userId, $champion['champion_id']);
        
        $canAscend = $this->ascensionService->canAscend($userId, $userChampionId);
        $required = $this->ascensionService->getRequiredShards(
            $champion['tier'],
            $champion['stars'],
            $champion['star_tier']
        );
        
        $tierInfo = $this->ascensionService->getTierInfo($champion['tier'], $champion['star_tier']);
        $totalLevel = $this->userChampionModel->getTotalLevel($userId, $champion['champion_id']);
        
        $baseChampion = $this->championModel->findById($champion['champion_id']);
        $baseStats = $this->championModel->getBaseStats($champion['champion_id']);
        
        $multiplier = $this->ascensionService->getMultiplier($champion['star_tier']);
        $levelMultiplier = $this->ascensionService->getLevelMultiplier($champion['level']);
        
        $this->view('game/champion-detail', [
            'champion' => $champion,
            'currentShards' => $shards,
            'required' => $required,
            'canAscend' => $canAscend,
            'totalLevel' => $totalLevel,
            'baseStats' => $baseStats,
            'starInfo' => $starInfo,
            'baseChampion' => $baseChampion,
            'levelMultiplier' => $levelMultiplier,
        ]);
    }
    
    public function ascend(int $id): void {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $userId = Session::userId();
        $result = $this->ascensionService->ascend($userId, $userChampionId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function shards(): void {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $userId = Session::userId();
        
        $shards = $this->championShardModel->getAllForUser($userId);
        
        $this->view('game/shards', [
            'shards' => $shards
        ]);
    }
}
