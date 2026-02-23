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
use App\Services\FusionService;
use App\Services\QuestService;
use App\Services\AscensionService;

class ChampionController extends Controller {
    private ChampionService $championService;
    private FusionService $fusionService;
    private QuestService $questService;
    private AscensionService $ascensionService;
    private UserChampion $userChampionModel;
    private Champion $championModel;
    private ChampionShard $championShardModel;
    private Resource $resourceModel;
    private UserEquipment $userEquipmentModel;
    
    public function __construct() {
        $this->championService = new ChampionService();
        $this->fusionService = new FusionService();
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
        
        $fusionEligible = [];
        foreach ($champions as $champion) {
            if ($champion['stars'] < 5) {
                $candidates = $this->fusionService->getFusionCandidates($userId, (int)$champion['id']);
                if (count($candidates) > 0) {
                    $fusionEligible[$champion['id']] = true;
                }
            }
        }
        
        $this->view('game/champions', [
            'champions' => $champions,
            'fusionEligible' => $fusionEligible,
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
        $fusionInfo = $this->fusionService->canFuse($userId, $championId);
        $fusionCandidates = $this->fusionService->getFusionCandidates($userId, $championId);
        $ascensionInfo = $this->ascensionService->getAscensionInfo($userId, $championId);
        
        $this->view('game/champion-detail', [
            'champion' => $champion,
            'resources' => $this->resourceModel->getUserResources($userId),
            'equipment' => $equipment,
            'equipmentStats' => $equipmentStats,
            'fusionInfo' => $fusionInfo,
            'fusionCandidates' => $fusionCandidates,
            'ascensionInfo' => $ascensionInfo,
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
    
    public function history(): void {
        $userId = Session::userId();
        
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        
        $battles = $this->battleModel->getUserBattles($userId, $perPage);
        $stats = $this->battleModel->getUserStats($userId);
        
        $this->view('game/battle-history', [
            'battles' => $battles,
            'stats' => $stats,
            'page' => $page
        ]);
    }
    
    public function fusion(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $champion = $this->userChampionModel->getChampionWithDetails($championId, $userId);
        
        if (!$champion) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $fusionCandidates = $this->fusionService->getFusionCandidates($userId, $championId);
        $fusionInfo = $this->fusionService->canFuse($userId, $championId);
        
        $this->view('game/champion-fusion', [
            'champion' => $champion,
            'fusionCandidates' => $fusionCandidates,
            'fusionInfo' => $fusionInfo,
            'resources' => $this->resourceModel->getUserResources($userId),
        ]);
    }
    
    public function doFusion(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $targetId = (int)($_POST['target_id'] ?? 0);
        $materialId = (int)($_POST['material_id'] ?? 0);
        
        if ($targetId <= 0 || $materialId <= 0) {
            $this->jsonError('Invalid parameters', 400);
            return;
        }
        
        if ($targetId === $materialId) {
            $this->jsonError('Cannot fuse champion with itself', 400);
            return;
        }
        
        $result = $this->fusionService->fuse($userId, $targetId, $materialId);
        
        if ($result['success']) {
            $this->questService->trackChampionUpgrade($userId);
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
