<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\UserChampion;
use App\Models\ChampionShard;
use App\Models\Champion;
use App\Models\Resource;
use App\Models\UserEquipment;
use App\Services\ChampionService;
use App\Services\QuestService;
use App\Services\AscensionService;

class ChampionController extends Controller {
    private ChampionService $championService;
    private QuestService $questService;
    private AscensionService $ascensionService;
    private UserChampion $userChampionModel;
    private Champion $championModel;
    private ChampionShard $championShardModel;
    private Resource $resourceModel;
    private UserEquipment $userEquipmentModel;
    
    public function __construct() {
        $this->championService = new ChampionService();
        $this->questService = new QuestService();
        $this->ascensionService = new AscensionService();
        $this->userChampionModel = new UserChampion();
        $this->championModel = new Champion();
        $this->championShardModel = new ChampionShard();
        $this->resourceModel = new Resource();
        $this->userEquipmentModel = new UserEquipment();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $champions = $this->userChampionModel->getUserChampions($userId);
        
        $this->view('game/champions', [
            'champions' => $champions,
        ]);
    }
    
    public function show(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $champion = $this->userChampionModel->getChampionWithDetails($championId, $userId);
        
        if (!$champion) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $equipment = $this->userEquipmentModel->getChampionEquipment($championId);
        $equipmentStats = $this->userEquipmentModel->getTotalStats($championId);
        $ascensionInfo = $this->ascensionService->getAscensionInfo($userId, $championId);
        $duplicates = $this->userChampionModel->getChampionsByType($userId, $champion['champion_id']);
        
        $this->view('game/champion-detail', [
            'champion' => $champion,
            'resources' => $this->resourceModel->getUserResources($userId),
            'equipment' => $equipment,
            'equipmentStats' => $equipmentStats,
            'ascensionInfo' => $ascensionInfo,
            'duplicateCount' => count($duplicates),
        ]);
    }
    
    public function ascend(string $id): void {
        $userId = Session::userId();
        $userChampionId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->ascensionService->ascend($userId, $userChampionId);
        
        if ($result['success']) {
            $this->questService->trackChampionUpgrade($userId);
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function tierUp(string $id): void {
        $userId = Session::userId();
        $userChampionId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->ascensionService->tierUp($userId, $userChampionId);
        
        if ($result['success']) {
            $this->questService->trackChampionUpgrade($userId);
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function upgrade(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $upgradeInfo = $this->championService->getUpgradeInfo($championId, $userId);
        
        if (!$upgradeInfo) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $this->view('game/champion-upgrade', [
            'upgradeInfo' => $upgradeInfo,
            'resources' => $this->resourceModel->getUserResources($userId)
        ]);
    }
    
    public function doUpgrade(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->championService->upgradeWithGold($championId, $userId);
        
        if ($result['success']) {
            $this->questService->trackChampionUpgrade($userId);
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function convertDuplicate(string $id): void {
        $userId = Session::userId();
        $userChampionId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->ascensionService->convertDuplicateToShards($userId, $userChampionId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
    
    public function shards(): void {
        $userId = Session::userId();
        
        $shards = $this->championShardModel->getAllForUser($userId);
        
        $this->view('game/shards', [
            'shards' => $shards,
        ]);
    }
}
