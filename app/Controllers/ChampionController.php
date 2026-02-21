<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\UserChampion;
use App\Models\Champion;
use App\Models\Battle;
use App\Models\Resource;
use App\Models\UserEquipment;
use App\Services\ChampionService;
use App\Services\FusionService;
use App\Services\QuestService;

class ChampionController extends Controller {
    private ChampionService $championService;
    private FusionService $fusionService;
    private QuestService $questService;
    
    public function __construct() {
        $this->championService = new ChampionService();
        $this->fusionService = new FusionService();
        $this->questService = new QuestService();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $championModel = new UserChampion();
        
        $this->view('game/champions', [
            'champions' => $championModel->getUserChampions($userId)
        ]);
    }
    
    public function show(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $championModel = new UserChampion();
        $champion = $championModel->getChampionWithDetails($championId, $userId);
        
        if (!$champion) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $resourceModel = new Resource();
        $userEquipmentModel = new UserEquipment();
        
        $equipment = $userEquipmentModel->getChampionEquipment($championId);
        $equipmentStats = $userEquipmentModel->getTotalStats($championId);
        $fusionInfo = $this->fusionService->canFuse($userId, $championId);
        $fusionCandidates = $this->fusionService->getFusionCandidates($userId, $championId);
        
        $this->view('game/champion-detail', [
            'champion' => $champion,
            'resources' => $resourceModel->getUserResources($userId),
            'equipment' => $equipment,
            'equipmentStats' => $equipmentStats,
            'fusionInfo' => $fusionInfo,
            'fusionCandidates' => $fusionCandidates,
        ]);
    }
    
    public function upgrade(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $upgradeInfo = $this->championService->getUpgradeInfo($championId, $userId);
        
        if (!$upgradeInfo) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $resourceModel = new Resource();
        
        $this->view('game/champion-upgrade', [
            'upgradeInfo' => $upgradeInfo,
            'resources' => $resourceModel->getUserResources($userId)
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
        
        $battleModel = new Battle();
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        
        $battles = $battleModel->getUserBattles($userId, $perPage);
        $stats = $battleModel->getUserStats($userId);
        
        $this->view('game/battle-history', [
            'battles' => $battles,
            'stats' => $stats,
            'page' => $page
        ]);
    }
    
    public function fusion(string $id): void {
        $userId = Session::userId();
        $championId = (int)$id;
        
        $championModel = new UserChampion();
        $champion = $championModel->getChampionWithDetails($championId, $userId);
        
        if (!$champion) {
            $this->redirectWithError('/champions', 'Champion not found');
            return;
        }
        
        $fusionCandidates = $this->fusionService->getFusionCandidates($userId, $championId);
        $fusionInfo = $this->fusionService->canFuse($userId, $championId);
        $resourceModel = new Resource();
        
        $this->view('game/champion-fusion', [
            'champion' => $champion,
            'fusionCandidates' => $fusionCandidates,
            'fusionInfo' => $fusionInfo,
            'resources' => $resourceModel->getUserResources($userId),
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
}