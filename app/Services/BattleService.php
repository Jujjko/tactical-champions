<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Mission;
use App\Models\UserChampion;
use App\Models\Champion;
use App\Models\Battle;
use App\Models\Resource;
use App\Models\User;

class BattleService {
    private Mission $missionModel;
    private UserChampion $championModel;
    private Champion $championsModel;
    private Battle $battleModel;
    private Resource $resourceModel;
    private User $userModel;
    private BattleStateManager $stateManager;
    private ?QuestService $questService = null;
    private ?AchievementService $achievementService = null;
    
    public function __construct() {
        $this->missionModel = new Mission();
        $this->championModel = new UserChampion();
        $this->championsModel = new Champion();
        $this->battleModel = new Battle();
        $this->resourceModel = new Resource();
        $this->userModel = new User();
        $this->stateManager = new BattleStateManager();
        $this->questService = new QuestService();
        $this->achievementService = new AchievementService();
    }
    
    public function getBattlePreparationData(int $missionId, int $userId): array {
        $mission = $this->missionModel->findById($missionId);
        $user = $this->userModel->findById($userId);
        
        if (!$mission) {
            return ['error' => 'Mission not found'];
        }
        
        if ($user['level'] < $mission['required_level']) {
            return ['error' => 'Your level is too low for this mission'];
        }
        
        $this->resourceModel->regenerateEnergy($userId);
        
        return [
            'mission' => $mission,
            'champions' => $this->championModel->getUserChampions($userId),
            'resources' => $this->resourceModel->getUserResources($userId),
            'userLevel' => $user['level']
        ];
    }
    
    public function startBattle(int $missionId, array $selectedChampions, int $userId): array {
        $mission = $this->missionModel->findById($missionId);
        $user = $this->userModel->findById($userId);
        
        if (!$mission) {
            return ['error' => 'Mission not found', 'code' => 404];
        }
        
        if ($user['level'] < $mission['required_level']) {
            return ['error' => 'Level too low', 'code' => 403];
        }
        
        if (!$this->resourceModel->useEnergy($userId, $mission['energy_cost'])) {
            return ['error' => 'Not enough energy', 'code' => 400];
        }
        
        $this->questService->trackEnergySpent($userId, $mission['energy_cost']);
        
        $playerTeam = $this->buildPlayerTeam($selectedChampions, $userId);
        
        if (empty($playerTeam)) {
            return ['error' => 'No valid champions selected', 'code' => 400];
        }
        
        $enemyTeam = $this->generateEnemyTeam($mission);
        
        error_log("BattleService: Player team count: " . count($playerTeam) . ", Enemy team count: " . count($enemyTeam));
        
        $battleEngine = new BattleEngine();
        $battleEngine->initializeBattle($playerTeam, $enemyTeam);
        
        $state = $battleEngine->getState();
        error_log("BattleService: Battle state generated, player_team count: " . count($state['player_team'] ?? []));
        
        $saveResult = $this->stateManager->save($userId, [
            'mission_id' => $missionId,
            'battle_state' => $state,
            'start_time' => time()
        ]);
        error_log("BattleService: Save result: " . ($saveResult ? 'success' : 'failed'));
        
        return [
            'success' => true,
            'battle_id' => $missionId,
            'state' => $state
        ];
    }
    
    public function executeAction(array $params, int $userId): array {
        $battleState = $this->stateManager->get($userId);
        
        if (!$battleState) {
            return ['error' => 'No active battle', 'code' => 400];
        }
        
        $battleEngine = new BattleEngine();
        $battleEngine->setState($battleState['battle_state']);
        
        $result = $battleEngine->executeTurn(
            $params['action'] ?? 'attack',
            $params['attacker_id'] ?? null,
            $params['target_id'] ?? null,
            ($params['use_ability'] ?? '') === 'true'
        );
        
        if ($result['battle_ended']) {
            $rewards = $this->endBattle($battleEngine, $result['winner'], $userId);
            $result['rewards'] = $rewards;
            $result['mission'] = $this->missionModel->findById($battleState['mission_id']);
            return $result;
        }
        
        $aiResult = $battleEngine->executeAITurn();
        
        if ($aiResult['battle_ended']) {
            $rewards = $this->endBattle($battleEngine, $aiResult['winner'], $userId);
            $aiResult['rewards'] = $rewards;
            $aiResult['mission'] = $this->missionModel->findById($battleState['mission_id']);
            return $aiResult;
        }
        
        $battleState['battle_state'] = $battleEngine->getState();
        $this->stateManager->save($userId, $battleState);
        
        return $aiResult;
    }
    
    public function forfeitBattle(int $userId): array {
        $battleState = $this->stateManager->get($userId);
        
        if (!$battleState) {
            return ['error' => 'No active battle', 'code' => 400];
        }
        
        $battleEngine = new BattleEngine();
        $battleEngine->setState($battleState['battle_state']);
        $this->endBattle($battleEngine, 'defeat', $userId);
        
        return ['success' => true, 'message' => 'Battle forfeited'];
    }
    
    public function getBattleState(int $userId): ?array {
        return $this->stateManager->get($userId);
    }
    
    public function hasActiveBattle(int $userId): bool {
        return $this->stateManager->exists($userId);
    }
    
    private function buildPlayerTeam(array $selectedChampions, int $userId): array {
        $playerTeam = [];
        
        foreach ($selectedChampions as $champId) {
            $champ = $this->championModel->getChampionWithDetails((int)$champId, $userId);
            if ($champ) {
                $playerTeam[] = $champ;
            }
        }
        
        return $playerTeam;
    }
    
    private function generateEnemyTeam(array $mission): array {
        $allChampions = $this->championsModel->all();
        $enemyTeam = [];
        
        $multiplier = match($mission['difficulty']) {
            'easy' => 0.8,
            'medium' => 1.0,
            'hard' => 1.3,
            'expert' => 1.6,
            default => 1.0
        };
        
        for ($i = 0; $i < $mission['enemy_count']; $i++) {
            $randomChamp = $allChampions[array_rand($allChampions)];
            
            $enemyTeam[] = [
                'id' => $randomChamp['id'] . '_' . $i,
                'name' => $randomChamp['name'],
                'health' => (int)($randomChamp['base_health'] * $multiplier),
                'attack' => (int)($randomChamp['base_attack'] * $multiplier),
                'defense' => (int)($randomChamp['base_defense'] * $multiplier),
                'speed' => (int)($randomChamp['base_speed'] * $multiplier),
                'special_ability' => $randomChamp['special_ability'],
                'icon' => $randomChamp['icon'] ?? '👹',
                'image_url' => $randomChamp['image_url'] ?? ''
            ];
        }
        
        return $enemyTeam;
    }
    
    private function endBattle(BattleEngine $battleEngine, string $result, int $userId): array {
        $battleState = $this->stateManager->get($userId);
        $missionId = $battleState['mission_id'] ?? 0;
        $duration = time() - ($battleState['start_time'] ?? time());
        
        $mission = $missionId ? $this->missionModel->findById($missionId) : null;
        $rewards = [];
        
        $won = ($result === 'victory');
        
        try {
            $this->questService->trackBattle($userId, $won);
        } catch (\Exception $e) {
            error_log("Quest tracking error: " . $e->getMessage());
        }
        
        if ($won && $mission) {
            $rewards = $this->missionModel->completeMission($userId, $missionId);
            $this->resourceModel->addGold($userId, $rewards['gold'] ?? 0);
            $this->userModel->addExperience($userId, $rewards['experience'] ?? 0);
            
            if (!empty($rewards['lootbox'])) {
                $this->resourceModel->addLootbox($userId, 'bronze');
            }
            
            try {
                $this->questService->trackMission($userId);
                $this->achievementService->trackBattleWin($userId);
                $this->achievementService->trackMissionCompleted($userId);
                
                $user = $this->userModel->findById($userId);
                $this->achievementService->trackLevelUp($userId, $user['level'] ?? 1);
            } catch (\Exception $e) {
                error_log("Achievement tracking error: " . $e->getMessage());
            }
            
            $this->awardChampionExperience($battleEngine);
        }
        
        if ($missionId) {
            $this->battleModel->create([
                'user_id' => $userId,
                'mission_id' => $missionId,
                'result' => $result,
                'duration_seconds' => $duration,
                'champions_used' => json_encode($battleEngine->getState()['player_team'] ?? []),
                'rewards_earned' => json_encode($rewards)
            ]);
        }
        
        $this->stateManager->delete($userId);
        
        return $rewards;
    }
    
    private function awardChampionExperience(BattleEngine $battleEngine): void {
        $summary = $battleEngine->getBattleSummary();
        
        foreach ($summary['player_survivors'] as $champion) {
            if (isset($champion['id']) && is_numeric($champion['id'])) {
                $this->championModel->addExperience((int)$champion['id'], 25);
            }
        }
    }
}