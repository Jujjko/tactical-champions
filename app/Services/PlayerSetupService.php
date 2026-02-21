<?php
declare(strict_types=1);

namespace App\Services;

use Core\Database;
use App\Models\Champion;
use App\Models\UserChampion;
use App\Models\PvpRating;
use App\Services\CacheService;
use PDO;

class PlayerSetupService
{
    private PDO $db;
    private Champion $championModel;
    private UserChampion $userChampionModel;
    private PvpRating $pvpRatingModel;

    private const STARTER_GOLD = 500;
    private const STARTER_GEMS = 50;
    private const STARTER_ENERGY = 100;
    private const STARTER_MAX_ENERGY = 100;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->championModel = new Champion();
        $this->userChampionModel = new UserChampion();
        $this->pvpRatingModel = new PvpRating();
    }

    public function setupNewPlayer(int $userId): bool
    {
        try {
            $this->createInitialResources($userId);
            $this->grantStarterChampions($userId);
            $this->initializePvpRating($userId);
            $this->clearUserCache($userId);
            error_log("PlayerSetupService: Successfully set up player {$userId}");
            return true;
        } catch (\Exception $e) {
            error_log("PlayerSetupService failed for user {$userId}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    private function createInitialResources(int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_resources (user_id, gold, gems, energy, max_energy)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            self::STARTER_GOLD,
            self::STARTER_GEMS,
            self::STARTER_ENERGY,
            self::STARTER_MAX_ENERGY
        ]);
    }

    private function grantStarterChampions(int $userId): void
    {
        $starterChampions = $this->getStarterChampionIds();
        error_log("PlayerSetupService: Found " . count($starterChampions) . " starter champions for user {$userId}");

        foreach ($starterChampions as $championId) {
            $result = $this->userChampionModel->addChampionToUser($userId, $championId);
            error_log("PlayerSetupService: Added champion {$championId} to user {$userId}, result: {$result}");
        }
    }

    private function getStarterChampionIds(): array
    {
        $stmt = $this->db->prepare("
            SELECT id FROM champions 
            WHERE tier = 'common' AND deleted_at IS NULL 
            ORDER BY id ASC 
            LIMIT 2
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        error_log("PlayerSetupService: Found " . count($results) . " common champions in DB: " . implode(',', $results));

        if (empty($results)) {
            $this->createDefaultChampions();
            return $this->getStarterChampionIds();
        }

        return $results;
    }

    private function createDefaultChampions(): void
    {
        $defaultChampions = [
            [
                'name' => 'Warrior Knight',
                'tier' => 'common',
                'base_health' => 120,
                'base_attack' => 15,
                'base_defense' => 10,
                'base_speed' => 45,
                'special_ability' => 'Shield Bash: Stun enemy for 1 turn',
                'description' => 'A basic warrior with balanced stats'
            ],
            [
                'name' => 'Forest Archer',
                'tier' => 'common',
                'base_health' => 90,
                'base_attack' => 20,
                'base_defense' => 5,
                'base_speed' => 60,
                'special_ability' => 'Precise Shot: Increased critical chance',
                'description' => 'Swift archer from the ancient forests'
            ]
        ];

        foreach ($defaultChampions as $champion) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO champions 
                (name, tier, base_health, base_attack, base_defense, base_speed, special_ability, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $champion['name'],
                $champion['tier'],
                $champion['base_health'],
                $champion['base_attack'],
                $champion['base_defense'],
                $champion['base_speed'],
                $champion['special_ability'],
                $champion['description']
            ]);
        }
    }

    private function initializePvpRating(int $userId): void
    {
        $this->pvpRatingModel->getOrCreateForUser($userId);
    }

    private function clearUserCache(int $userId): void
    {
        $cache = CacheService::getInstance();
        $cache->delete("user:{$userId}:resources");
        $cache->delete("user:{$userId}:champions");
    }

    public function getStarterResources(): array
    {
        return [
            'gold' => self::STARTER_GOLD,
            'gems' => self::STARTER_GEMS,
            'energy' => self::STARTER_ENERGY,
            'max_energy' => self::STARTER_MAX_ENERGY
        ];
    }
}
