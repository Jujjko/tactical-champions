<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Mission;
use App\Models\User;
use App\Models\Resource;

class MissionController extends Controller {
    private Mission $missionModel;
    private User $userModel;
    private Resource $resourceModel;
    
    public function __construct() {
        $this->missionModel = new Mission();
        $this->userModel = new User();
        $this->resourceModel = new Resource();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $this->resourceModel->regenerateEnergy($userId);
        
        $user = $this->userModel->findById($userId);
        $missions = $this->missionModel->getAvailableMissions($user['level']);
        $resources = $this->resourceModel->getUserResources($userId);
        
        $this->view('game/missions', [
            'missions' => $missions,
            'resources' => $resources,
            'userLevel' => $user['level']
        ]);
    }
}
