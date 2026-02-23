<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;

// Public routes
$router->get('/', 'GameController', 'index');
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/forgot-password', 'AuthController', 'showForgotPassword');
$router->post('/forgot-password', 'AuthController', 'sendResetLink');
$router->get('/reset-password/{token}', 'AuthController', 'showResetPassword');
$router->post('/reset-password', 'AuthController', 'resetPassword');

// Protected game routes
$router->get('/dashboard', 'GameController', 'dashboard', [AuthMiddleware::class]);
$router->get('/champions', 'ChampionController', 'index', [AuthMiddleware::class]);
$router->get('/champions/shards', 'ChampionController', 'shards', [AuthMiddleware::class]);
$router->get('/champions/{id}', 'ChampionController', 'show', [AuthMiddleware::class]);
$router->get('/champions/{id}/upgrade', 'ChampionController', 'upgrade', [AuthMiddleware::class]);
$router->post('/champions/{id}/upgrade', 'ChampionController', 'doUpgrade', [AuthMiddleware::class]);
$router->get('/champions/{id}/fusion', 'ChampionController', 'fusion', [AuthMiddleware::class]);
$router->post('/champions/fusion', 'ChampionController', 'doFusion', [AuthMiddleware::class]);
$router->post('/champions/{id}/ascend', 'ChampionController', 'ascend', [AuthMiddleware::class]);
$router->post('/champions/{id}/tier-up', 'ChampionController', 'tierUp', [AuthMiddleware::class]);
$router->get('/battle-history', 'ChampionController', 'history', [AuthMiddleware::class]);
$router->get('/missions', 'MissionController', 'index', [AuthMiddleware::class]);
$router->get('/lootbox', 'LootboxController', 'index', [AuthMiddleware::class]);
$router->post('/lootbox/{id}/open', 'LootboxController', 'open', [AuthMiddleware::class]);
$router->post('/lootbox/open-multiple', 'LootboxController', 'openMultiple', [AuthMiddleware::class]);
$router->get('/inventory', 'GameController', 'inventory', [AuthMiddleware::class]);

// Equipment routes
$router->get('/equipment', 'EquipmentController', 'index', [AuthMiddleware::class]);
$router->get('/equipment/{id}', 'EquipmentController', 'show', [AuthMiddleware::class]);
$router->post('/equipment/{id}/equip', 'EquipmentController', 'equip', [AuthMiddleware::class]);
$router->post('/equipment/{id}/unequip', 'EquipmentController', 'unequip', [AuthMiddleware::class]);

// Arena routes (PvP)
$router->get('/arena', 'ArenaController', 'index', [AuthMiddleware::class]);
$router->get('/arena/leaderboard', 'ArenaController', 'leaderboard', [AuthMiddleware::class]);
$router->post('/arena/challenge', 'ArenaController', 'challenge', [AuthMiddleware::class]);
$router->post('/arena/challenge/{id}/accept', 'ArenaController', 'acceptChallenge', [AuthMiddleware::class]);
$router->post('/arena/challenge/{id}/decline', 'ArenaController', 'declineChallenge', [AuthMiddleware::class]);
$router->post('/arena/queue/join', 'ArenaController', 'joinQueue', [AuthMiddleware::class]);
$router->post('/arena/queue/leave', 'ArenaController', 'leaveQueue', [AuthMiddleware::class]);
$router->get('/arena/queue/check', 'ArenaController', 'checkMatch', [AuthMiddleware::class]);

// PvP Arena routes
$router->get('/pvp', 'PvpController', 'index', [AuthMiddleware::class]);
$router->post('/pvp/find-match', 'PvpController', 'findMatch', [AuthMiddleware::class]);
$router->post('/pvp/start-battle', 'PvpController', 'startBattle', [AuthMiddleware::class]);
$router->get('/pvp/battle', 'PvpController', 'battle', [AuthMiddleware::class]);
$router->post('/pvp/action', 'PvpController', 'executeAction', [AuthMiddleware::class]);
$router->get('/pvp/leaderboard', 'PvpController', 'leaderboard', [AuthMiddleware::class]);
$router->get('/pvp/history', 'PvpController', 'history', [AuthMiddleware::class]);

// Guild routes
$router->get('/guilds', 'GuildController', 'index', [AuthMiddleware::class]);
$router->get('/guilds/{id}', 'GuildController', 'show', [AuthMiddleware::class]);
$router->post('/guilds/create', 'GuildController', 'create', [AuthMiddleware::class]);
$router->post('/guilds/{id}/join', 'GuildController', 'join', [AuthMiddleware::class]);
$router->post('/guilds/leave', 'GuildController', 'leave', [AuthMiddleware::class]);

// Achievement routes
$router->get('/achievements', 'AchievementController', 'index', [AuthMiddleware::class]);
$router->post('/achievements/{id}/claim', 'AchievementController', 'claim', [AuthMiddleware::class]);

// Shop routes
$router->get('/shop', 'ShopController', 'index', [AuthMiddleware::class]);
$router->post('/shop/{id}/purchase', 'ShopController', 'purchase', [AuthMiddleware::class]);

// Battle Pass routes
$router->get('/battle-pass', 'BattlePassController', 'index', [AuthMiddleware::class]);
$router->post('/battle-pass/claim/{level}', 'BattlePassController', 'claim', [AuthMiddleware::class]);

// Referral routes
$router->get('/referrals', 'ReferralController', 'index', [AuthMiddleware::class]);
$router->post('/referrals/use', 'ReferralController', 'useCode', [AuthMiddleware::class]);
$router->post('/referrals/{id}/claim', 'ReferralController', 'claim', [AuthMiddleware::class]);

// Friends routes
$router->get('/friends', 'FriendController', 'index', [AuthMiddleware::class]);
$router->get('/friends/search', 'FriendController', 'search', [AuthMiddleware::class]);
$router->post('/friends/add', 'FriendController', 'add', [AuthMiddleware::class]);
$router->post('/friends/{id}/accept', 'FriendController', 'accept', [AuthMiddleware::class]);
$router->post('/friends/{id}/decline', 'FriendController', 'decline', [AuthMiddleware::class]);
$router->post('/friends/{id}/remove', 'FriendController', 'remove', [AuthMiddleware::class]);

// Quest routes
$router->get('/quests', 'QuestController', 'index', [AuthMiddleware::class]);
$router->post('/quests/{id}/claim', 'QuestController', 'claim', [AuthMiddleware::class]);
$router->post('/quests/claim-all', 'QuestController', 'claimAll', [AuthMiddleware::class]);

// Tutorial routes
$router->get('/tutorial', 'TutorialController', 'index', [AuthMiddleware::class]);
$router->post('/tutorial/{step}/complete', 'TutorialController', 'complete', [AuthMiddleware::class]);
$router->post('/tutorial/skip', 'TutorialController', 'skip', [AuthMiddleware::class]);
$router->get('/tutorial/status', 'TutorialController', 'status', [AuthMiddleware::class]);

// Battle routes (replace old battle routes)
$router->get('/battle/prepare/{id}', 'BattleController', 'prepare', [AuthMiddleware::class]);
$router->post('/battle/start', 'BattleController', 'start', [AuthMiddleware::class]);
$router->get('/battle/arena', 'BattleController', 'arena', [AuthMiddleware::class]);
$router->post('/battle/action', 'BattleController', 'action', [AuthMiddleware::class]);
$router->get('/battle/state', 'BattleController', 'state', [AuthMiddleware::class]);
$router->post('/battle/forfeit', 'BattleController', 'forfeit', [AuthMiddleware::class]);


// Admin routes
$router->get('/admin', 'AdminController', 'dashboard', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users', 'AdminController', 'users', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/{id}/toggle', 'AdminController', 'toggleUser', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/champions', 'AdminController', 'champions', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/champions/{id}', 'AdminController', 'getChampion', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/champions/create', 'AdminController', 'createChampion', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/champions/{id}/update', 'AdminController', 'updateChampion', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/champions/{id}/delete', 'AdminController', 'deleteChampion', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/missions', 'AdminController', 'missions', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/missions/save', 'AdminController', 'saveMission', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/missions/{id}/delete', 'AdminController', 'deleteMission', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/missions/{id}/toggle', 'AdminController', 'toggleMission', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/leaderboard', 'AdminController', 'leaderboard', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/equipment', 'AdminController', 'equipment', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/equipment/create', 'AdminController', 'createEquipment', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/equipment/{id}/update', 'AdminController', 'updateEquipment', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/equipment/{id}/delete', 'AdminController', 'deleteEquipment', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/pvp-history', 'AdminController', 'pvpHistory', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/bulk-rewards', 'AdminController', 'bulkRewards', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/bulk-rewards/send', 'AdminController', 'sendBulkRewards', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/analytics', 'AdminController', 'analytics', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/logs', 'AdminController', 'logs', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/seasons', 'AdminController', 'seasons', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/seasons/end', 'AdminController', 'endSeason', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/seasons/start', 'AdminController', 'startSeason', [AuthMiddleware::class, AdminMiddleware::class]);

// Season routes
$router->get('/season', 'SeasonController', 'index', [AuthMiddleware::class]);
$router->post('/season/rewards/{id}/claim', 'SeasonController', 'claimReward', [AuthMiddleware::class]);

// Leaderboard routes
$router->get('/leaderboard', 'LeaderboardController', 'index');
$router->get('/leaderboard/pvp', 'LeaderboardController', 'pvp');
$router->get('/api/leaderboard', 'LeaderboardController', 'api');

// Tournament routes
$router->get('/tournaments', 'TournamentController', 'index', [AuthMiddleware::class]);
$router->get('/tournaments/{id}', 'TournamentController', 'view', [AuthMiddleware::class]);
$router->post('/tournaments/{id}/join', 'TournamentController', 'join', [AuthMiddleware::class]);
$router->post('/tournaments/{id}/leave', 'TournamentController', 'leave', [AuthMiddleware::class]);
$router->get('/tournaments/{id}/bracket', 'TournamentController', 'bracket', [AuthMiddleware::class]);
$router->get('/tournaments/{tournamentId}/match/{matchId}', 'TournamentController', 'match', [AuthMiddleware::class]);
$router->post('/tournaments/match/{matchId}/result', 'TournamentController', 'reportResult', [AuthMiddleware::class]);
$router->get('/admin/tournaments', 'AdminController', 'tournaments', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/tournaments/create', 'AdminController', 'createTournament', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/tournaments/create', 'AdminController', 'createTournament', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/tournaments/{id}/start', 'AdminController', 'startTournament', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/tournaments/{id}/cancel', 'AdminController', 'cancelTournament', [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/tournaments/{id}/rewards', 'AdminController', 'editTournamentRewards', [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/tournaments/{id}/rewards', 'AdminController', 'editTournamentRewards', [AuthMiddleware::class, AdminMiddleware::class]);