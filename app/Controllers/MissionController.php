<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Mission;
use App\Models\User;
use App\Models\Resource;

class MissionController extends Controller {
    public function index(): void {
        $userId = Session::userId();
        
        $userModel = new User();
        $missionModel = new Mission();
        $resourceModel = new Resource();
        
        $resourceModel->regenerateEnergy($userId);
        
        $user = $userModel->findById($userId);
        $missions = $missionModel->getAvailableMissions($user['level']);
        $resources = $resourceModel->getUserResources($userId);
        
        $this->view('game/missions', [
            'missions' => $missions,
            'resources' => $resources,
            'userLevel' => $user['level']
        ]);
    }
}