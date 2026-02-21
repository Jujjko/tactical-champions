<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Services\BattleService;

class BattleController extends Controller {
    private BattleService $battleService;
    
    public function __construct() {
        $this->battleService = new BattleService();
    }
    
    public function prepare(string $id): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $data = $this->battleService->getBattlePreparationData((int)$id, Session::userId());
        
        if (isset($data['error'])) {
            $this->redirectWithError('/missions', $data['error']);
            return;
        }
        
        $this->view('game/battle-prepare', $data);
    }
    
    public function start(): void {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $selectedChampions = $this->getSelectedChampions();
        
        if (empty($selectedChampions) || count($selectedChampions) > 5) {
            $this->jsonError('Select 1-5 champions', 400);
            return;
        }
        
        $result = $this->battleService->startBattle(
            (int)($_POST['mission_id'] ?? 0),
            $selectedChampions,
            Session::userId()
        );
        
        if (isset($result['error'])) {
            $this->jsonError($result['error'], $result['code'] ?? 400);
            return;
        }
        
        $this->json($result);
    }
    
    public function arena(): void {
        $userId = Session::userId();
        
        if (!$this->battleService->hasActiveBattle($userId)) {
            $this->redirectWithError('/missions', 'No active battle');
            return;
        }
        
        $this->view('game/battle-arena');
    }
    
    public function action(): void {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->battleService->executeAction($_POST, Session::userId());
        
        if (isset($result['error'])) {
            $this->jsonError($result['error'], $result['code'] ?? 400);
            return;
        }
        
        $this->json($result);
    }
    
    public function state(): void {
        $userId = Session::userId();
        $battleState = $this->battleService->getBattleState($userId);
        
        if (!$battleState) {
            $this->jsonError('No active battle', 400);
            return;
        }
        
        $this->json($battleState['battle_state'] ?? $battleState);
    }
    
    public function forfeit(): void {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->battleService->forfeitBattle(Session::userId());
        
        if (isset($result['error'])) {
            $this->jsonError($result['error'], $result['code'] ?? 400);
            return;
        }
        
        $this->jsonSuccess($result, 'Battle forfeited');
    }
    
    private function getSelectedChampions(): array {
        $selectedChampions = $_POST['champions'] ?? [];
        
        if (!is_array($selectedChampions)) {
            $selectedChampions = [$selectedChampions];
        }
        
        return array_filter($selectedChampions);
    }
}