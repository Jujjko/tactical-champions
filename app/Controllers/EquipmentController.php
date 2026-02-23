<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\UserEquipment;
use App\Models\Equipment;
use App\Models\UserChampion;
use App\Services\QuestService;

class EquipmentController extends Controller {
    private UserEquipment $userEquipmentModel;
    private Equipment $equipmentModel;
    private UserChampion $userChampionModel;
    private QuestService $questService;
    
    public function __construct() {
        $this->userEquipmentModel = new UserEquipment();
        $this->equipmentModel = new Equipment();
        $this->userChampionModel = new UserChampion();
        $this->questService = new QuestService();
    }
    
    public function index(): void {
        $userId = Session::userId();
        $typeFilter = $_GET['type'] ?? null;
        
        $this->view('game/equipment', [
            'equipment' => $this->userEquipmentModel->getUserEquipment($userId),
            'typeFilter' => $typeFilter
        ]);
    }
    
    public function show(string $id): void {
        $userId = Session::userId();
        $userEquipmentId = (int)$id;
        
        $equipment = $this->userEquipmentModel->findById($userEquipmentId);
        
        if (!$equipment || $equipment['user_id'] !== $userId) {
            $this->redirectWithError('/equipment', 'Equipment not found');
            return;
        }
        
        $equipmentDetails = $this->equipmentModel->findById($equipment['equipment_id']);
        $champions = $this->userChampionModel->getUserChampions($userId);
        
        $this->view('game/equipment-detail', [
            'userEquipment' => $equipment,
            'equipment' => $equipmentDetails,
            'champions' => $champions
        ]);
    }
    
    public function equip(string $id): void {
        $userId = Session::userId();
        $userEquipmentId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $championId = (int)($_POST['champion_id'] ?? 0);
        
        if ($championId <= 0) {
            $this->jsonError('Invalid champion selected', 400);
            return;
        }
        
        $result = $this->userEquipmentModel->equipToChampion($userEquipmentId, $championId, $userId);
        
        if ($result) {
            $this->questService->trackEquipmentChange($userId);
            $this->jsonSuccess(['message' => 'Equipment equipped successfully']);
        } else {
            $this->jsonError('Failed to equip item', 400);
        }
    }
    
    public function unequip(string $id): void {
        $userId = Session::userId();
        $userEquipmentId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->userEquipmentModel->unequip($userEquipmentId, $userId);
        
        if ($result) {
            $this->questService->trackEquipmentChange($userId);
            $this->jsonSuccess(['message' => 'Equipment unequipped successfully']);
        } else {
            $this->jsonError('Failed to unequip item', 400);
        }
    }
}
