<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PvpRating;
use App\Models\UserChampion;
use Core\Database;
use Core\Session;

class MatchmakingService {
    private Database $db;
    private PvpRating $ratingModel;
    private UserChampion $championModel;
    private SeasonService $seasonService;
    
    private const RATING_RANGE = 150;
    private const QUEUE_TIMEOUT = 60;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->ratingModel = new PvpRating();
        $this->championModel = new UserChampion();
        $this->seasonService = new SeasonService();
    }
    
    public function findMatch(int $userId, ?int $championId = null): ?array {
        $season = $this->seasonService->getActiveSeason();
        
        if (!$season) {
            return null;
        }
        
        $myRating = $this->ratingModel->getOrCreateForUser($userId);
        $myChampions = $this->championModel->getUserChampions($userId);
        
        if (empty($myChampions)) {
            return null;
        }
        
        $selectedChampion = $championId 
            ? $this->championModel->getChampionWithDetails($championId, $userId)
            : $myChampions[0];
        
        $opponent = $this->findOpponentInRatingRange($userId, $myRating['rating'], $season['id']);
        
        if ($opponent) {
            return $this->prepareMatchData($opponent, $selectedChampion);
        }
        
        $opponent = $this->findAnyOpponent($userId, $season['id']);
        
        if ($opponent) {
            return $this->prepareMatchData($opponent, $selectedChampion);
        }
        
        return null;
    }
    
    private function findOpponentInRatingRange(int $userId, int $rating, int $seasonId): ?array {
        $minRating = max(0, $rating - self::RATING_RANGE);
        $maxRating = $rating + self::RATING_RANGE;
        
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.level, pr.rating, pr.wins, pr.losses
            FROM users u
            JOIN pvp_ratings pr ON u.id = pr.user_id
            WHERE u.id != ?
              AND u.is_active = 1
              AND u.deleted_at IS NULL
              AND pr.rating BETWEEN ? AND ?
            ORDER BY RAND()
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $minRating, $maxRating]);
        $opponent = $stmt->fetch();
        
        if (!$opponent) {
            return null;
        }
        
        $opponent['champions'] = $this->championModel->getUserChampions($opponent['id']);
        
        if (empty($opponent['champions'])) {
            return $this->findOpponentInRatingRange($userId, $rating, $seasonId);
        }
        
        return $opponent;
    }
    
    private function findAnyOpponent(int $userId, int $seasonId): ?array {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.level, pr.rating, pr.wins, pr.losses
            FROM users u
            JOIN pvp_ratings pr ON u.id = pr.user_id
            WHERE u.id != ?
              AND u.is_active = 1
              AND u.deleted_at IS NULL
            ORDER BY RAND()
            LIMIT 1
        ");
        
        $stmt->execute([$userId]);
        $opponent = $stmt->fetch();
        
        if (!$opponent) {
            return null;
        }
        
        $opponent['champions'] = $this->championModel->getUserChampions($opponent['id']);
        
        if (empty($opponent['champions'])) {
            return null;
        }
        
        return $opponent;
    }
    
    private function prepareMatchData(array $opponent, array $myChampion): array {
        $opponentChampion = $opponent['champions'][array_rand($opponent['champions'])];
        
        return [
            'opponent' => [
                'id' => $opponent['id'],
                'username' => $opponent['username'],
                'level' => $opponent['level'],
                'rating' => $opponent['rating'],
                'wins' => $opponent['wins'],
                'losses' => $opponent['losses'],
            ],
            'opponent_champion' => [
                'id' => $opponentChampion['id'],
                'name' => $opponentChampion['name'],
                'level' => $opponentChampion['level'],
                'tier' => $opponentChampion['tier'],
                'health' => $opponentChampion['health'],
                'attack' => $opponentChampion['attack'],
                'defense' => $opponentChampion['defense'],
                'speed' => $opponentChampion['speed'],
            ],
            'my_champion' => $myChampion,
        ];
    }
    
    public function getActiveSeason(): ?array {
        return $this->seasonService->getActiveSeason();
    }
    
    public function getLeaderboard(int $limit = 100): array {
        return $this->ratingModel->getLeaderboard($limit);
    }
    
    public function getUserRank(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) + 1 as rank
            FROM pvp_ratings pr1
            WHERE pr1.rating > (SELECT rating FROM pvp_ratings WHERE user_id = ?)
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function getRankName(int $rating): string {
        return match(true) {
            $rating < 1200 => 'Bronze',
            $rating < 1400 => 'Silver',
            $rating < 1600 => 'Gold',
            $rating < 1800 => 'Platinum',
            $rating < 2000 => 'Diamond',
            default => 'Master',
        };
    }
    
    public function getRankIcon(string $rankName): string {
        return match($rankName) {
            'Bronze' => '🥉',
            'Silver' => '🥈',
            'Gold' => '🥇',
            'Platinum' => '💎',
            'Diamond' => '💠',
            'Master' => '👑',
            default => '⭐',
        };
    }
}
