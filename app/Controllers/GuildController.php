<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Guild;
use App\Models\GuildMember;
use App\Models\User;
use App\Services\AuditService;

class GuildController extends Controller {
    private Guild $guildModel;
    private GuildMember $guildMemberModel;
    private User $userModel;
    private AuditService $auditService;
    
    public function __construct() {
        $this->guildModel = new Guild();
        $this->guildMemberModel = new GuildMember();
        $this->userModel = new User();
        $this->auditService = new AuditService();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $userGuild = $this->guildMemberModel->getUserGuild($userId);
        $recruitingGuilds = $this->guildModel->getRecruitingGuilds(20);
        $leaderboard = $this->guildModel->getLeaderboard(10);
        
        $this->view('game/guilds', [
            'userGuild' => $userGuild,
            'recruitingGuilds' => $recruitingGuilds,
            'leaderboard' => $leaderboard
        ]);
    }
    
    public function show(string $id): void {
        $userId = Session::userId();
        $guildId = (int)$id;
        
        $guild = $this->guildModel->findByIdWithMembers($guildId);
        
        if (!$guild) {
            $this->redirectWithError('/guilds', 'Guild not found');
            return;
        }
        
        $isMember = $this->guildMemberModel->isMemberOf($userId, $guildId);
        
        $this->view('game/guild-detail', [
            'guild' => $guild,
            'isMember' => $isMember
        ]);
    }
    
    public function create(): void {
        $userId = Session::userId();
        
        $existingGuild = $this->guildMemberModel->getUserGuild($userId);
        
        if ($existingGuild) {
            $this->jsonError('You are already in a guild', 400);
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $name = trim($_POST['name'] ?? '');
        $tag = trim($_POST['tag'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (strlen($name) < 3 || strlen($name) > 50) {
            $this->jsonError('Guild name must be 3-50 characters', 400);
            return;
        }
        
        if (strlen($tag) < 2 || strlen($tag) > 5) {
            $this->jsonError('Tag must be 2-5 characters', 400);
            return;
        }
        
        try {
            $guildId = $this->guildModel->createGuild($userId, $name, strtoupper($tag), $description);
            $this->auditService->logCreate('guild', $guildId, ['name' => $name, 'tag' => $tag]);
            $this->jsonSuccess(['guild_id' => $guildId, 'message' => 'Guild created!']);
        } catch (\PDOException $e) {
            $this->jsonError('Guild name or tag already taken', 400);
        }
    }
    
    public function join(string $id): void {
        $userId = Session::userId();
        $guildId = (int)$id;
        
        $existingGuild = $this->guildMemberModel->getUserGuild($userId);
        if ($existingGuild) {
            $this->jsonError('You are already in a guild', 400);
            return;
        }
        
        $guild = $this->guildModel->findById($guildId);
        if (!$guild || !$guild['is_recruiting']) {
            $this->jsonError('Guild is not recruiting', 400);
            return;
        }
        
        $user = $this->userModel->findById($userId);
        
        if ($user['level'] < $guild['min_level_req']) {
            $this->jsonError('Level requirement not met', 400);
            return;
        }
        
        if ($this->guildMemberModel->joinGuild($userId, $guildId)) {
            $this->jsonSuccess(['message' => 'Joined guild successfully!']);
        } else {
            $this->jsonError('Failed to join guild', 400);
        }
    }
    
    public function leave(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $userGuild = $this->guildMemberModel->getUserGuild($userId);
        
        if (!$userGuild) {
            $this->jsonError('You are not in a guild', 400);
            return;
        }
        
        if ($userGuild['role'] === 'leader') {
            $this->jsonError('Leader cannot leave. Transfer leadership or disband the guild.', 400);
            return;
        }
        
        $this->guildMemberModel->leaveGuild($userId);
        
        $this->jsonSuccess(['message' => 'Left guild successfully']);
    }
}
