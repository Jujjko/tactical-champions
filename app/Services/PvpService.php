<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PvpBattle;
use App\Models\PvpRating;
use App\Models\PvpRewardsLog;
use App\Models\UserChampion;
use App\Models\Resource;
use Core\Database;
use Core\Session;

class PvpService {
    private Database $db;
    private PvpBattle $battleModel;
    private PvpRating $ratingModel;
    private UserChampion $championModel;
    private PvpRewardsLog $rewardsLog;
    private SeasonService $seasonService;
    private ?AchievementService $achievementService = null;
    
    private const MIN_RATING_GAIN = 10;
    private const MAX_RATING_GAIN = 35;
    private const BASE_RATING_GAIN = 25;
    private const RATING_FLOOR = 800;
    
    private const VICTORY_GOLD_MIN = 250;
    private const VICTORY_GOLD_MAX = 450;
    private const VICTORY_GEMS_MIN = 12;
    private const VICTORY_GEMS_MAX = 28;
    private const VICTORY_SHARD_CHANCE = 35;
    private const VICTORY_ITEM_CHANCE = 12;
    
    private const DEFEAT_GOLD_MIN = 80;
    private const DEFEAT_GOLD_MAX = 160;
    private const DEFEAT_GEMS_MIN = 5;
    private const DEFEAT_GEMS_MAX = 12;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->battleModel = new PvpBattle();
        $this->ratingModel = new PvpRating();
        $this->championModel = new UserChampion();
        $this->rewardsLog = new PvpRewardsLog();
        $this->seasonService = new SeasonService();
        $this->achievementService = new AchievementService();
    }
    
    public function startPvpBattle(int $attackerId, int $defenderId, int $attackerChampionId, int $defenderChampionId): array {
        $attackerChampion = $this->championModel->getChampionWithDetails($attackerChampionId, $attackerId);
        $defenderChampion = $this->championModel->getChampionWithDetails($defenderChampionId, $defenderId);
        
        if (!$attackerChampion || !$defenderChampion) {
            return ['success' => false, 'error' => 'Invalid champion'];
        }
        
        $battleEngine = new BattleEngine();
        
        $attackerTeam = [$this->prepareChampionForBattle($attackerChampion, true)];
        $defenderTeam = [$this->prepareChampionForBattle($defenderChampion, false)];
        
        $battleEngine->initializeBattle($attackerTeam, $defenderTeam);
        $battleEngine->setAsPvp($defenderId, $defenderChampion['name'] ?? 'Opponent');
        
        return [
            'success' => true,
            'battle_state' => $battleEngine->getState(),
        ];
    }
    
    private function prepareChampionForBattle(array $champion, bool $isPlayer): array {
        return [
            'id' => $champion['id'],
            'name' => $champion['name'],
            'health' => $champion['health'],
            'attack' => $champion['attack'],
            'defense' => $champion['defense'],
            'speed' => $champion['speed'],
            'special_ability' => $champion['special_ability'] ?? null,
            'is_player' => $isPlayer,
        ];
    }
    
    public function processBattleResult(int $attackerId, int $defenderId, string $result, int $duration, ?string $battleLog = null): array {
        $attackerRating = $this->ratingModel->getOrCreateForUser($attackerId);
        $defenderRating = $this->ratingModel->getOrCreateForUser($defenderId);
        
        $ratingChange = $this->calculateRatingChange(
            $attackerRating['rating'],
            $defenderRating['rating'],
            $result === 'victory'
        );
        
        if ($result === 'victory') {
            $winnerId = $attackerId;
            $loserId = $defenderId;
            $attackerChange = $ratingChange;
            $defenderChange = -$ratingChange;
        } elseif ($result === 'defeat') {
            $winnerId = $defenderId;
            $loserId = $attackerId;
            $attackerChange = -$ratingChange;
            $defenderChange = $ratingChange;
        } else {
            $winnerId = null;
            $loserId = null;
            $attackerChange = 0;
            $defenderChange = 0;
        }
        
        $this->updateRating($attackerId, $result === 'victory', $attackerChange);
        $this->updateRating($defenderId, $result === 'defeat', $defenderChange);
        
        $battleId = $this->battleModel->createBattle([
            'attacker_id' => $attackerId,
            'defender_id' => $defenderId,
            'winner_id' => $winnerId,
            'loser_id' => $loserId,
            'result' => $result,
            'duration_seconds' => $duration,
            'attacker_rating_change' => $attackerChange,
            'defender_rating_change' => $defenderChange,
            'battle_log' => $battleLog,
        ]);
        
        $rewards = $this->givePvpRewards($attackerId, $defenderId, $result, $battleId);
        
        if ($result === 'victory') {
            $this->achievementService->trackPvpWin($attackerId);
        }
        
        return [
            'success' => true,
            'winner_id' => $winnerId,
            'attacker_rating_change' => $attackerChange,
            'defender_rating_change' => $defenderChange,
            'new_attacker_rating' => $attackerRating['rating'] + $attackerChange,
            'new_defender_rating' => $defenderRating['rating'] + $defenderChange,
            'rewards' => $rewards,
            'battle_id' => $battleId,
        ];
    }
    
    public function givePvpRewards(int $userId, int $opponentId, string $result, int $battleId): array {
        $gold = 0;
        $gems = 0;
        $shard = false;
        $item = null;
        
        if ($result === 'victory') {
            $gold = rand(self::VICTORY_GOLD_MIN, self::VICTORY_GOLD_MAX);
            $gems = rand(self::VICTORY_GEMS_MIN, self::VICTORY_GEMS_MAX);
            $shard = rand(1, 100) <= self::VICTORY_SHARD_CHANCE;
            $item = rand(1, 100) <= self::VICTORY_ITEM_CHANCE ? 'rare_chest' : null;
        } else {
            $gold = rand(self::DEFEAT_GOLD_MIN, self::DEFEAT_GOLD_MAX);
            $gems = rand(self::DEFEAT_GEMS_MIN, self::DEFEAT_GEMS_MAX);
        }
        
        $this->rewardsLog->create([
            'user_id' => $userId,
            'battle_id' => $battleId,
            'gold_earned' => $gold,
            'gems_earned' => $gems,
            'shard_earned' => $shard ? 1 : 0,
            'item_earned' => $item,
        ]);
        
        $resourceModel = new Resource();
        $resourceModel->addGold($userId, $gold);
        $resourceModel->addGems($userId, $gems);
        
        if ($shard) {
            $resourceModel->addLootbox($userId, 'silver');
        }
        
        if ($item) {
            $resourceModel->addLootbox($userId, 'gold');
        }
        
        return [
            'gold' => $gold,
            'gems' => $gems,
            'shard' => $shard,
            'item' => $item,
        ];
    }
    
    public function getUserRewardsHistory(int $userId, int $limit = 50): array {
        return $this->rewardsLog->getUserRewards($userId, $limit);
    }
    
    public function getUserTotalRewards(int $userId): array {
        return $this->rewardsLog->getTotalRewards($userId);
    }
    
    public function calculateRatingChange(int $winnerRating, int $loserRating, bool $attackerWon): int {
        $ratingDiff = $winnerRating - $loserRating;
        
        $change = self::BASE_RATING_GAIN - (int)($ratingDiff / 20);
        
        $change = max(self::MIN_RATING_GAIN, min(self::MAX_RATING_GAIN, $change));
        
        return $change;
    }
    
    private function updateRating(int $userId, bool $won, int $change): void {
        $this->ratingModel->updateAfterBattle($userId, $won, $change);
    }
    
    public function getUserStats(int $userId): array {
        $rating = $this->ratingModel->getOrCreateForUser($userId);
        $rank = (new MatchmakingService())->getUserRank($userId);
        $rankName = (new MatchmakingService())->getRankName($rating['rating']);
        
        return [
            'rating' => $rating['rating'],
            'rank' => $rank,
            'rank_name' => $rankName,
            'wins' => $rating['wins'],
            'losses' => $rating['losses'],
            'win_rate' => $rating['wins'] + $rating['losses'] > 0 
                ? round($rating['wins'] / ($rating['wins'] + $rating['losses']) * 100, 1) 
                : 0,
            'highest_rating' => $rating['highest_rating'],
            'current_streak' => $rating['current_streak'],
            'best_streak' => $rating['best_streak'],
        ];
    }
    
    public function getBattleHistory(int $userId, int $limit = 20): array {
        return $this->battleModel->getUserBattles($userId, $limit);
    }
    
    public function canAffordPvp(int $userId): bool {
        $resources = (new \App\Models\Resource())->getUserResources($userId);
        return $resources && $resources['energy'] >= 15;
    }
    
    public function deductPvpEnergy(int $userId): bool {
        return (new \App\Models\Resource())->useEnergy($userId, 15);
    }
}
