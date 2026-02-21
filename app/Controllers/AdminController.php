<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\User;
use App\Models\Champion;
use App\Models\Mission;
use App\Models\Battle;
use App\Models\Equipment;
use App\Models\PvpBattle;
use App\Models\Resource;
use App\Services\AuditService;
use App\Services\AnalyticsService;
use App\Services\Logger;
use App\Services\SeasonService;

class AdminController extends Controller {
    private AuditService $auditService;
    
    public function __construct() {
        $this->auditService = new AuditService();
    }
    
    public function dashboard(): void {
        $userModel = new User();
        $battleModel = new Battle();
        $auditService = new AuditService();
        
        $leaderboard = $this->getLeaderboard(10);
        
        $this->view('admin/dashboard', [
            'totalUsers' => $userModel->count(),
            'totalBattles' => $battleModel->count(),
            'recentLogs' => $auditService->getRecentLogs(20),
            'leaderboard' => $leaderboard
        ]);
    }
    
    public function users(): void {
        $page = (int)($_GET['page'] ?? 1);
        $userModel = new User();
        
        $this->view('admin/users', $userModel->paginate($page, 20, 'id', 'DESC'));
    }
    
    public function toggleUser(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $userModel = new User();
        $user = $userModel->findById((int)$id);
        
        if (!$user) {
            $this->jsonError('User not found', 404);
            return;
        }
        
        $oldValue = (bool)$user['is_active'];
        $userModel->update((int)$id, [
            'is_active' => !$user['is_active']
        ]);
        
        $this->auditService->logToggle('user', (int)$id, 'is_active', $oldValue, !$oldValue);
        
        $this->jsonSuccess(['is_active' => !$oldValue]);
    }
    
    public function champions(): void {
        $page = (int)($_GET['page'] ?? 1);
        $championModel = new Champion();
        
        $this->view('admin/champions', $championModel->paginate($page, 20, 'tier', 'DESC'));
    }
    
    public function getChampion(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        $championModel = new Champion();
        $champion = $championModel->findById((int)$id);
        
        if (!$champion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $this->jsonSuccess($champion);
    }
    
    public function createChampion(): void {
        if (!Session::isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->redirectWithError('/admin/champions', 'Invalid request');
            return;
        }
        
        $validation = $this->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'tier' => 'required|in:common,rare,epic,legendary,mythic',
            'health' => 'required|integer|min_value:1',
            'attack' => 'required|integer|min_value:1',
            'defense' => 'required|integer|min_value:0',
            'speed' => 'required|integer|min_value:1'
        ]);
        
        if (!$validation['valid']) {
            $this->redirectWithError('/admin/champions', $validation['firstError']);
            return;
        }
        
        $championModel = new Champion();
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'tier' => $_POST['tier'],
            'base_health' => (int)$_POST['health'],
            'base_attack' => (int)$_POST['attack'],
            'base_defense' => (int)$_POST['defense'],
            'base_speed' => (int)$_POST['speed'],
            'special_ability' => htmlspecialchars($_POST['special_ability'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? '')
        ];
        
        $championId = $championModel->create($data);
        
        $this->auditService->logCreate('champion', $championId, $data);
        
        $this->redirectWithSuccess('/admin/champions', 'Champion created successfully');
    }
    
    public function updateChampion(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $championModel = new Champion();
        $oldChampion = $championModel->findById((int)$id);
        
        if (!$oldChampion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'tier' => $_POST['tier'],
            'base_health' => (int)$_POST['health'],
            'base_attack' => (int)$_POST['attack'],
            'base_defense' => (int)$_POST['defense'],
            'base_speed' => (int)$_POST['speed'],
            'special_ability' => htmlspecialchars($_POST['special_ability'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? '')
        ];
        
        $championModel->update((int)$id, $data);
        $this->auditService->logUpdate('champion', (int)$id, $oldChampion, $data);
        
        $this->jsonSuccess(['champion_id' => (int)$id]);
    }
    
    public function deleteChampion(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $championModel = new Champion();
        $champion = $championModel->findById((int)$id);
        
        if (!$champion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $championModel->delete((int)$id);
        $this->auditService->logDelete('champion', (int)$id, $champion);
        
        $this->jsonSuccess();
    }
    
    public function missions(): void {
        $page = (int)($_GET['page'] ?? 1);
        $missionModel = new Mission();
        
        $this->view('admin/missions', $missionModel->paginate($page, 20, 'id', 'DESC'));
    }
    
    public function saveMission(): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $missionModel = new Mission();
        $missionId = (int)($_POST['mission_id'] ?? 0);
        
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'description' => htmlspecialchars($_POST['description']),
            'difficulty' => $_POST['difficulty'],
            'required_level' => (int)$_POST['required_level'],
            'energy_cost' => (int)$_POST['energy_cost'],
            'enemy_count' => (int)$_POST['enemy_count'],
            'gold_reward' => (int)$_POST['gold_reward'],
            'experience_reward' => (int)$_POST['experience_reward'],
            'lootbox_chance' => (float)$_POST['lootbox_chance'],
            'is_active' => (int)$_POST['is_active']
        ];
        
        if ($missionId > 0) {
            $oldMission = $missionModel->findById($missionId);
            $missionModel->update($missionId, $data);
            $this->auditService->logUpdate('mission', $missionId, $oldMission, $data);
        } else {
            $missionId = $missionModel->create($data);
            $this->auditService->logCreate('mission', $missionId, $data);
        }
        
        $this->jsonSuccess(['mission_id' => $missionId]);
    }
    
    public function deleteMission(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $missionModel = new Mission();
        $mission = $missionModel->findById((int)$id);
        
        if (!$mission) {
            $this->jsonError('Mission not found', 404);
            return;
        }
        
        $missionModel->delete((int)$id);
        $this->auditService->logDelete('mission', (int)$id, $mission);
        
        $this->jsonSuccess();
    }
    
    public function toggleMission(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $missionModel = new Mission();
        $mission = $missionModel->findById((int)$id);
        
        if (!$mission) {
            $this->jsonError('Mission not found', 404);
            return;
        }
        
        $oldValue = (bool)$mission['is_active'];
        $missionModel->update((int)$id, [
            'is_active' => !$mission['is_active']
        ]);
        
        $this->auditService->logToggle('mission', (int)$id, 'is_active', $oldValue, !$oldValue);
        
        $this->jsonSuccess(['is_active' => !$oldValue]);
    }
    
    public function leaderboard(): void {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        
        $leaderboard = $this->getLeaderboard($perPage, $page);
        
        $this->view('admin/leaderboard', [
            'leaderboard' => $leaderboard,
            'page' => $page
        ]);
    }
    
    private function getLeaderboard(int $limit = 10, int $page = 1): array {
        $offset = ($page - 1) * $limit;
        
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT 
                u.id,
                u.username,
                u.level,
                u.experience,
                ur.gold,
                ur.gems,
                COUNT(DISTINCT uc.id) as champion_count,
                COUNT(b.id) as total_battles,
                SUM(CASE WHEN b.result = 'victory' THEN 1 ELSE 0 END) as victories,
                COALESCE(SUM(CASE WHEN b.result = 'victory' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(b.id), 0), 0) as win_rate
            FROM users u
            LEFT JOIN user_resources ur ON ur.user_id = u.id
            LEFT JOIN user_champions uc ON uc.user_id = u.id AND uc.deleted_at IS NULL
            LEFT JOIN battles b ON b.user_id = u.id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY u.level DESC, victories DESC, u.experience DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    public function equipment(): void {
        $page = (int)($_GET['page'] ?? 1);
        $equipmentModel = new Equipment();
        
        $this->view('admin/equipment', $equipmentModel->paginate($page, 20, 'tier', 'DESC'));
    }
    
    public function createEquipment(): void {
        if (!Session::isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->redirectWithError('/admin/equipment', 'Invalid request');
            return;
        }
        
        $equipmentModel = new Equipment();
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'type' => $_POST['type'],
            'slot' => $_POST['slot'],
            'tier' => $_POST['tier'],
            'health_bonus' => (int)($_POST['health_bonus'] ?? 0),
            'attack_bonus' => (int)($_POST['attack_bonus'] ?? 0),
            'defense_bonus' => (int)($_POST['defense_bonus'] ?? 0),
            'speed_bonus' => (int)($_POST['speed_bonus'] ?? 0),
            'special_effect' => htmlspecialchars($_POST['special_effect'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? '')
        ];
        
        $equipmentId = $equipmentModel->create($data);
        
        $this->auditService->logCreate('equipment', $equipmentId, $data);
        
        $this->redirectWithSuccess('/admin/equipment', 'Equipment created successfully');
    }
    
    public function updateEquipment(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $equipmentModel = new Equipment();
        $oldEquipment = $equipmentModel->findById((int)$id);
        
        if (!$oldEquipment) {
            $this->jsonError('Equipment not found', 404);
            return;
        }
        
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'type' => $_POST['type'],
            'slot' => $_POST['slot'],
            'tier' => $_POST['tier'],
            'health_bonus' => (int)($_POST['health_bonus'] ?? 0),
            'attack_bonus' => (int)($_POST['attack_bonus'] ?? 0),
            'defense_bonus' => (int)($_POST['defense_bonus'] ?? 0),
            'speed_bonus' => (int)($_POST['speed_bonus'] ?? 0),
            'special_effect' => htmlspecialchars($_POST['special_effect'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? '')
        ];
        
        $equipmentModel->update((int)$id, $data);
        $this->auditService->logUpdate('equipment', (int)$id, $oldEquipment, $data);
        
        $this->jsonSuccess(['equipment_id' => (int)$id]);
    }
    
    public function deleteEquipment(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $equipmentModel = new Equipment();
        $equipment = $equipmentModel->findById((int)$id);
        
        if (!$equipment) {
            $this->jsonError('Equipment not found', 404);
            return;
        }
        
        $equipmentModel->delete((int)$id);
        $this->auditService->logDelete('equipment', (int)$id, $equipment);
        
        $this->jsonSuccess();
    }
    
    public function pvpHistory(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $pvpBattleModel = new PvpBattle();
        
        $battles = $pvpBattleModel->getBattles($perPage, $offset);
        $stats = $pvpBattleModel->getStats();
        $topWinners = $pvpBattleModel->getRecentWinners(10);
        
        $this->view('admin/pvp-history', [
            'battles' => $battles,
            'stats' => $stats,
            'topWinners' => $topWinners,
            'page' => $page,
        ]);
    }
    
    public function bulkRewards(): void
    {
        $userModel = new User();
        $users = $userModel->where('is_active', true);
        
        $this->view('admin/bulk-rewards', [
            'users' => $users,
            'totalUsers' => count($users),
        ]);
    }
    
    public function sendBulkRewards(): void
    {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $goldAmount = (int)($_POST['gold'] ?? 0);
        $gemsAmount = (int)($_POST['gems'] ?? 0);
        $energyAmount = (int)($_POST['energy'] ?? 0);
        $lootboxType = $_POST['lootbox_type'] ?? null;
        $lootboxCount = (int)($_POST['lootbox_count'] ?? 0);
        
        $targetType = $_POST['target_type'] ?? 'all';
        $targetUsers = $_POST['target_users'] ?? [];
        
        if ($goldAmount < 0 || $gemsAmount < 0 || $energyAmount < 0) {
            $this->jsonError('Invalid reward amounts', 400);
            return;
        }
        
        $userModel = new User();
        $resourceModel = new Resource();
        
        if ($targetType === 'selected' && !empty($targetUsers)) {
            $userIds = is_array($targetUsers) ? $targetUsers : explode(',', $targetUsers);
        } elseif ($targetType === 'level_min') {
            $minLevel = (int)($_POST['min_level'] ?? 1);
            $users = $userModel->where('level', $minLevel, '>=');
            $userIds = array_column($users, 'id');
        } else {
            $users = $userModel->where('is_active', true);
            $userIds = array_column($users, 'id');
        }
        
        $recipients = 0;
        $resourceModel = new Resource();
        
        foreach ($userIds as $userId) {
            $userId = (int)$userId;
            $resources = $resourceModel->getUserResources($userId);
            
            if (!$resources) continue;
            
            $updateData = [];
            if ($goldAmount > 0) {
                $updateData['gold'] = $resources['gold'] + $goldAmount;
            }
            if ($gemsAmount > 0) {
                $updateData['gems'] = $resources['gems'] + $gemsAmount;
            }
            if ($energyAmount > 0) {
                $updateData['energy'] = min($resources['energy'] + $energyAmount, $resources['max_energy'] + $energyAmount);
                $updateData['max_energy'] = $resources['max_energy'];
            }
            
            if (!empty($updateData)) {
                $resourceModel->update($resources['id'], $updateData);
            }
            
            if ($lootboxCount > 0 && $lootboxType) {
                for ($i = 0; $i < $lootboxCount; $i++) {
                    $resourceModel->addLootbox($userId, $lootboxType);
                }
            }
            
            $recipients++;
        }
        
        $this->auditService->log('bulk_reward', 'user', null, [
            'recipients' => $recipients,
            'gold' => $goldAmount,
            'gems' => $gemsAmount,
            'energy' => $energyAmount,
            'lootbox_type' => $lootboxType,
            'lootbox_count' => $lootboxCount,
            'target_type' => $targetType,
        ]);
        
        $this->jsonSuccess([
            'recipients' => $recipients,
            'message' => "Rewards sent to {$recipients} users",
        ]);
    }
    
    public function analytics(): void
    {
        $analyticsService = new AnalyticsService();
        
        $dailyActive = $analyticsService->getDailyActiveUsers(7);
        $pageViews = $analyticsService->getEventCounts('page_view', 7);
        $battleStarts = $analyticsService->getEventCounts('battle_start', 7);
        $topEvents = $analyticsService->getTopEvents(10);
        $retention = $analyticsService->getRetentionData(30);
        
        $this->view('admin/analytics', [
            'dailyActive' => $dailyActive,
            'pageViews' => $pageViews,
            'battleStarts' => $battleStarts,
            'topEvents' => $topEvents,
            'retention' => $retention,
        ]);
    }
    
    public function logs(): void
    {
        $logger = new Logger();
        $date = $_GET['date'] ?? null;
        
        $logs = $logger->getLogs($date, 200);
        $files = $logger->getLogFiles();
        
        $this->view('admin/logs', [
            'logs' => $logs,
            'files' => $files,
            'selectedDate' => $date,
        ]);
    }
    
    public function seasons(): void
    {
        $seasonService = new SeasonService();
        
        $seasons = $seasonService->getSeasons(10);
        $activeSeason = $seasonService->getActiveSeason();
        
        $this->view('admin/seasons', [
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
        ]);
    }
    
    public function endSeason(): void
    {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $seasonId = (int)($_POST['season_id'] ?? 0);
        
        $seasonService = new SeasonService();
        $result = $seasonService->endSeason($seasonId);
        
        if ($result) {
            $this->auditService->log('end_season', 'pvp_season', $seasonId);
            $this->jsonSuccess(['message' => 'Season ended successfully']);
        } else {
            $this->jsonError('Failed to end season', 400);
        }
    }
    
    public function startSeason(): void
    {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $name = htmlspecialchars($_POST['name'] ?? '');
        $description = htmlspecialchars($_POST['description'] ?? '');
        $durationDays = (int)($_POST['duration_days'] ?? 30);
        
        if (empty($name)) {
            $this->jsonError('Season name is required', 400);
            return;
        }
        
        $seasonService = new SeasonService();
        $seasonId = $seasonService->startNewSeason($name, $description, $durationDays);
        
        $this->auditService->log('start_season', 'pvp_season', $seasonId, [
            'name' => $name,
            'duration_days' => $durationDays,
        ]);
        
        $this->jsonSuccess(['season_id' => $seasonId, 'message' => 'Season started successfully']);
    }
}