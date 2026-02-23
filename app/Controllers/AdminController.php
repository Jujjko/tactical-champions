<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\User;
use App\Models\Champion;
use App\Models\Mission;
use App\Models\Battle;
use App\Models\Equipment;
use App\Models\PvpBattle;
use App\Models\Resource;
use App\Models\Tournament;
use App\Services\AuditService;
use App\Services\AnalyticsService;
use App\Services\Logger;
use App\Services\SeasonService;
use App\Services\TournamentService;
use App\Services\ImageUploadService;

class AdminController extends Controller {
    private AuditService $auditService;
    private AnalyticsService $analyticsService;
    private Logger $logger;
    private SeasonService $seasonService;
    private User $userModel;
    private Champion $championModel;
    private Mission $missionModel;
    private Battle $battleModel;
    private Equipment $equipmentModel;
    private PvpBattle $pvpBattleModel;
    private Resource $resourceModel;
    private Tournament $tournamentModel;
    private ImageUploadService $imageUploadService;
    
    public function __construct() {
        $this->auditService = new AuditService();
        $this->analyticsService = new AnalyticsService();
        $this->logger = new Logger();
        $this->seasonService = new SeasonService();
        $this->userModel = new User();
        $this->championModel = new Champion();
        $this->missionModel = new Mission();
        $this->battleModel = new Battle();
        $this->equipmentModel = new Equipment();
        $this->pvpBattleModel = new PvpBattle();
        $this->resourceModel = new Resource();
        $this->tournamentModel = new Tournament();
        $this->imageUploadService = new ImageUploadService();
    }
    
    public function dashboard(): void {
        $leaderboard = $this->getLeaderboard(10);
        
        $this->view('admin/dashboard', [
            'totalUsers' => $this->userModel->count(),
            'totalBattles' => $this->battleModel->count(),
            'recentLogs' => $this->auditService->getRecentLogs(20),
            'leaderboard' => $leaderboard
        ]);
    }
    
    public function users(): void {
        $page = (int)($_GET['page'] ?? 1);
        $result = $this->userModel->paginate($page, 20, 'id', 'DESC');
        
        $this->view('admin/users', [
            'users' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'lastPage' => $result['last_page'],
            'hasMore' => $result['has_more']
        ]);
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
        
        $user = $this->userModel->findById((int)$id);
        
        if (!$user) {
            $this->jsonError('User not found', 404);
            return;
        }
        
        $oldValue = (bool)$user['is_active'];
        $this->userModel->update((int)$id, [
            'is_active' => !$user['is_active']
        ]);
        
        $this->auditService->logToggle('user', (int)$id, 'is_active', $oldValue, !$oldValue);
        
        $this->jsonSuccess(['is_active' => !$oldValue]);
    }
    
    public function champions(): void {
        $page = (int)($_GET['page'] ?? 1);
        $result = $this->championModel->paginate($page, 20, 'tier', 'DESC');
        
        $this->view('admin/champions', [
            'champions' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'lastPage' => $result['last_page'],
            'hasMore' => $result['has_more']
        ]);
    }
    
    public function getChampion(string $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        $champion = $this->championModel->findById((int)$id);
        
        if (!$champion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $this->jsonSuccess($champion);
    }
    
    public function createChampion(): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
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
            $this->jsonError($validation['firstError'], 400);
            return;
        }
        
        $imageUrl = htmlspecialchars($_POST['image_url'] ?? '');
        
        if (!empty($_FILES['image_file']['name'])) {
            $uploadedUrl = $this->imageUploadService->upload($_FILES['image_file'], 'champion');
            if ($uploadedUrl) {
                $imageUrl = $uploadedUrl;
            }
        }
        
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'tier' => $_POST['tier'],
            'base_health' => (int)$_POST['health'],
            'base_attack' => (int)$_POST['attack'],
            'base_defense' => (int)$_POST['defense'],
            'base_speed' => (int)$_POST['speed'],
            'special_ability' => htmlspecialchars($_POST['special_ability'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? ''),
            'image_url' => $imageUrl,
            'icon' => htmlspecialchars($_POST['icon'] ?? '')
        ];
        
        $championId = $this->championModel->create($data);
        
        $this->auditService->logCreate('champion', $championId, $data);
        
        $this->jsonSuccess(['champion_id' => $championId, 'message' => 'Champion created successfully']);
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
        
        $oldChampion = $this->championModel->findById((int)$id);
        
        if (!$oldChampion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        error_log("FILES: " . json_encode($_FILES));
        error_log("POST: " . json_encode($_POST));
        
        $imageUrl = $oldChampion['image_url'] ?? '';
        
        if (!empty($_POST['image_url'])) {
            $imageUrl = $_POST['image_url'];
        }
        
        if (!empty($_FILES['image_file']['name'])) {
            $uploadedUrl = $this->imageUploadService->upload($_FILES['image_file'], 'champion');
            if ($uploadedUrl) {
                if (!empty($oldChampion['image_url']) && str_starts_with($oldChampion['image_url'], '/images/')) {
                    $this->imageUploadService->delete($oldChampion['image_url']);
                }
                $imageUrl = $uploadedUrl;
            } else {
                error_log("Image upload failed for champion {$id}");
            }
        }
        
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'tier' => $_POST['tier'],
            'base_health' => (int)$_POST['health'],
            'base_attack' => (int)$_POST['attack'],
            'base_defense' => (int)$_POST['defense'],
            'base_speed' => (int)$_POST['speed'],
            'special_ability' => htmlspecialchars($_POST['special_ability'] ?? ''),
            'description' => htmlspecialchars($_POST['description'] ?? ''),
            'image_url' => $imageUrl,
            'icon' => htmlspecialchars($_POST['icon'] ?? '')
        ];
        
        $this->championModel->update((int)$id, $data);
        $this->auditService->logUpdate('champion', (int)$id, $oldChampion, $data);
        
        $this->jsonSuccess(['champion_id' => (int)$id, 'image_url' => $imageUrl]);
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
        
        $champion = $this->championModel->findById((int)$id);
        
        if (!$champion) {
            $this->jsonError('Champion not found', 404);
            return;
        }
        
        $this->championModel->delete((int)$id);
        $this->auditService->logDelete('champion', (int)$id, $champion);
        
        $this->jsonSuccess();
    }
    
    public function missions(): void {
        $page = (int)($_GET['page'] ?? 1);
        $result = $this->missionModel->paginate($page, 20, 'id', 'DESC');
        
        $this->view('admin/missions', [
            'missions' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'lastPage' => $result['last_page']
        ]);
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
            $oldMission = $this->missionModel->findById($missionId);
            $this->missionModel->update($missionId, $data);
            $this->auditService->logUpdate('mission', $missionId, $oldMission, $data);
        } else {
            $missionId = $this->missionModel->create($data);
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
        
        $mission = $this->missionModel->findById((int)$id);
        
        if (!$mission) {
            $this->jsonError('Mission not found', 404);
            return;
        }
        
        $this->missionModel->delete((int)$id);
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
        
        $mission = $this->missionModel->findById((int)$id);
        
        if (!$mission) {
            $this->jsonError('Mission not found', 404);
            return;
        }
        
        $oldValue = (bool)$mission['is_active'];
        $this->missionModel->update((int)$id, [
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
        
        $db = Database::getInstance();
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
        $result = $this->equipmentModel->paginate($page, 20, 'tier', 'DESC');
        
        $this->view('admin/equipment', [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'lastPage' => $result['last_page']
        ]);
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
        
        $equipmentId = $this->equipmentModel->create($data);
        
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
        
        $oldEquipment = $this->equipmentModel->findById((int)$id);
        
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
        
        $this->equipmentModel->update((int)$id, $data);
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
        
        $equipment = $this->equipmentModel->findById((int)$id);
        
        if (!$equipment) {
            $this->jsonError('Equipment not found', 404);
            return;
        }
        
        $this->equipmentModel->delete((int)$id);
        $this->auditService->logDelete('equipment', (int)$id, $equipment);
        
        $this->jsonSuccess();
    }
    
    public function pvpHistory(): void {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $battles = $this->pvpBattleModel->getBattles($perPage, $offset);
        $stats = $this->pvpBattleModel->getStats();
        $topWinners = $this->pvpBattleModel->getRecentWinners(10);
        
        $this->view('admin/pvp-history', [
            'battles' => $battles,
            'stats' => $stats,
            'topWinners' => $topWinners,
            'page' => $page,
        ]);
    }
    
    public function bulkRewards(): void {
        $users = $this->userModel->where('is_active', true);
        
        $this->view('admin/bulk-rewards', [
            'users' => $users,
            'totalUsers' => count($users),
        ]);
    }
    
    public function sendBulkRewards(): void {
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
        
        if ($targetType === 'selected' && !empty($targetUsers)) {
            $userIds = is_array($targetUsers) ? $targetUsers : explode(',', $targetUsers);
        } elseif ($targetType === 'level_min') {
            $minLevel = (int)($_POST['min_level'] ?? 1);
            $users = $this->userModel->where('level', $minLevel, '>=');
            $userIds = array_column($users, 'id');
        } else {
            $users = $this->userModel->where('is_active', true);
            $userIds = array_column($users, 'id');
        }
        
        $recipients = 0;
        
        foreach ($userIds as $userId) {
            $userId = (int)$userId;
            $resources = $this->resourceModel->getUserResources($userId);
            
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
                $this->resourceModel->update($resources['id'], $updateData);
            }
            
            if ($lootboxCount > 0 && $lootboxType) {
                for ($i = 0; $i < $lootboxCount; $i++) {
                    $this->resourceModel->addLootbox($userId, $lootboxType);
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
    
    public function analytics(): void {
        $dailyActive = $this->analyticsService->getDailyActiveUsers(7);
        $pageViews = $this->analyticsService->getEventCounts('page_view', 7);
        $battleStarts = $this->analyticsService->getEventCounts('battle_start', 7);
        $topEvents = $this->analyticsService->getTopEvents(10);
        $retention = $this->analyticsService->getRetentionData(30);
        
        $this->view('admin/analytics', [
            'dailyActive' => $dailyActive,
            'pageViews' => $pageViews,
            'battleStarts' => $battleStarts,
            'topEvents' => $topEvents,
            'retention' => $retention,
        ]);
    }
    
    public function logs(): void {
        $date = $_GET['date'] ?? null;
        
        $logs = $this->logger->getLogs($date, 200);
        $files = $this->logger->getLogFiles();
        
        $this->view('admin/logs', [
            'logs' => $logs,
            'files' => $files,
            'selectedDate' => $date,
        ]);
    }
    
    public function seasons(): void {
        $seasons = $this->seasonService->getSeasons(10);
        $activeSeason = $this->seasonService->getActiveSeason();
        
        $this->view('admin/seasons', [
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
        ]);
    }
    
    public function endSeason(): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $seasonId = (int)($_POST['season_id'] ?? 0);
        
        $result = $this->seasonService->endSeason($seasonId);
        
        if ($result) {
            $this->auditService->log('end_season', 'pvp_season', $seasonId);
            $this->jsonSuccess(['message' => 'Season ended successfully']);
        } else {
            $this->jsonError('Failed to end season', 400);
        }
    }
    
    public function startSeason(): void {
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
        
        $seasonId = $this->seasonService->startNewSeason($name, $description, $durationDays);
        
        $this->auditService->log('start_season', 'pvp_season', $seasonId, [
            'name' => $name,
            'duration_days' => $durationDays,
        ]);
        
        $this->jsonSuccess(['season_id' => $seasonId, 'message' => 'Season started successfully']);
    }
    
    public function tournaments(): void {
        $tournaments = $this->tournamentModel->db->query("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as participant_count
            FROM tournaments t
            ORDER BY t.created_at DESC
        ")->fetchAll();
        
        $this->view('admin/tournaments', [
            'tournaments' => $tournaments,
        ]);
    }
    
    public function createTournament(): void {
        if (!Session::isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('admin/tournament-form', [
                'csrf_token' => Session::csrfToken(),
            ]);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->redirectWithError('/admin/tournaments', 'Invalid request');
            return;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $maxPlayers = (int)($_POST['max_players'] ?? 8);
        $entryFeeGold = (int)($_POST['entry_fee_gold'] ?? 0);
        $entryFeeGems = (int)($_POST['entry_fee_gems'] ?? 0);
        $minRating = (int)($_POST['min_rating'] ?? 0);
        $startTime = $_POST['start_time'] ?? null;
        
        if (empty($name)) {
            $this->redirectWithError('/admin/tournaments', 'Name is required');
            return;
        }
        
        $tournamentService = new TournamentService();
        $tournamentId = $tournamentService->createTournament([
            'name' => $name,
            'description' => $description,
            'max_players' => $maxPlayers,
            'entry_fee_gold' => $entryFeeGold,
            'entry_fee_gems' => $entryFeeGems,
            'min_rating' => $minRating,
            'start_time' => $startTime,
        ], Session::userId());
        
        $this->auditService->log('create_tournament', 'tournaments', $tournamentId, $_POST);
        
        $this->redirectWithSuccess('/admin/tournaments', 'Tournament created');
    }
    
    public function startTournament(int $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $tournamentService = new TournamentService();
        $success = $tournamentService->generateBracket($id);
        
        if ($success) {
            $this->auditService->log('start_tournament', 'tournaments', $id);
            $this->jsonSuccess(['message' => 'Tournament started']);
        } else {
            $this->jsonError('Failed to start tournament', 400);
        }
    }
    
    public function cancelTournament(int $id): void {
        if (!Session::isAdmin()) {
            $this->jsonError('Unauthorized', 403);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $success = $this->tournamentModel->update($id, ['status' => 'cancelled']);
        
        if ($success) {
            $this->auditService->log('cancel_tournament', 'tournaments', $id);
            $this->jsonSuccess(['message' => 'Tournament cancelled']);
        } else {
            $this->jsonError('Failed to cancel tournament', 400);
        }
    }
    
    public function editTournamentRewards(int $id): void {
        if (!Session::isAdmin()) {
            $this->redirect('/admin');
            return;
        }
        
        $tournament = $this->tournamentModel->getWithRewards($id);
        
        if (!$tournament) {
            $this->redirect('/admin/tournaments');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('admin/tournament-rewards', [
                'tournament' => $tournament,
                'csrf_token' => Session::csrfToken(),
            ]);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->redirectWithError('/admin/tournaments', 'Invalid request');
            return;
        }
        
        $db = Database::getInstance();
        $db->prepare("DELETE FROM tournament_rewards WHERE tournament_id = ?")->execute([$id]);
        
        $places = $_POST['places'] ?? [];
        foreach ($places as $place => $data) {
            if (!empty($data['gold']) || !empty($data['gems']) || !empty($data['lootbox_type'])) {
                $stmt = $db->prepare("
                    INSERT INTO tournament_rewards (tournament_id, place, gold, gems, lootbox_type, lootbox_count)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id,
                    (int)$place,
                    (int)($data['gold'] ?? 0),
                    (int)($data['gems'] ?? 0),
                    $data['lootbox_type'] ?? null,
                    (int)($data['lootbox_count'] ?? 0),
                ]);
            }
        }
        
        $this->auditService->log('edit_tournament_rewards', 'tournaments', $id);
        $this->redirectWithSuccess('/admin/tournaments', 'Rewards updated');
    }
}
