<?php
declare(strict_types=1);

namespace App\Services;

use Core\Config;
use Core\Database;
use Core\Session;
use Predis\Client;

class BattleStateManager {
    private ?Client $redis = null;
    private bool $useRedis = false;
    private bool $useDatabase = true;
    private Database $db;
    private int $ttl = 3600;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->ttl = (int)Config::get('BATTLE_TIMEOUT', 3600);
        $this->initializeRedis();
    }
    
    private function initializeRedis(): void {
        if (Config::get('REDIS_ENABLED', 'false') !== 'true') {
            return;
        }
        
        try {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host' => Config::get('REDIS_HOST', '127.0.0.1'),
                'port' => (int)Config::get('REDIS_PORT', 6379),
                'password' => Config::get('REDIS_PASSWORD') ?: null,
                'database' => (int)Config::get('REDIS_DATABASE', 0),
            ]);
            
            $this->redis->ping();
            $this->useRedis = true;
            $this->useDatabase = false;
        } catch (\Exception $e) {
            $this->redis = null;
            $this->useRedis = false;
        }
    }
    
    public function save(int $userId, array $battleState): bool {
        $key = $this->getKey($userId);
        $data = json_encode($battleState);
        
        error_log("BattleStateManager::save - userId: {$userId}, useRedis: " . ($this->useRedis ? 'true' : 'false') . ", useDatabase: " . ($this->useDatabase ? 'true' : 'false'));
        
        if ($this->useRedis && $this->redis) {
            $this->redis->setex($key, $this->ttl, $data);
            return true;
        }
        
        if ($this->useDatabase) {
            return $this->saveToDatabase($userId, $battleState);
        }
        
        Session::set('battle_state', $battleState);
        return true;
    }
    
    public function get(int $userId): ?array {
        $key = $this->getKey($userId);
        
        if ($this->useRedis && $this->redis) {
            $data = $this->redis->get($key);
            return $data ? json_decode($data, true) : null;
        }
        
        if ($this->useDatabase) {
            return $this->getFromDatabase($userId);
        }
        
        return Session::get('battle_state');
    }
    
    public function delete(int $userId): bool {
        $key = $this->getKey($userId);
        
        if ($this->useRedis && $this->redis) {
            $this->redis->del([$key]);
            return true;
        }
        
        if ($this->useDatabase) {
            return $this->deleteFromDatabase($userId);
        }
        
        Session::remove('battle_state');
        return true;
    }
    
    public function exists(int $userId): bool {
        return $this->get($userId) !== null;
    }
    
    public function getDriver(): string {
        if ($this->useRedis) {
            return 'redis';
        }
        if ($this->useDatabase) {
            return 'database';
        }
        return 'session';
    }
    
    public function cleanup(): int {
        if (!$this->useDatabase) {
            return 0;
        }
        
        $stmt = $this->db->prepare("
            DELETE FROM battle_states 
            WHERE updated_at < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$this->ttl]);
        
        return $stmt->rowCount();
    }
    
    private function getKey(int $userId): string {
        return "battle_state:{$userId}";
    }
    
    private function saveToDatabase(int $userId, array $battleState): bool {
        $existingId = $this->getExistingId($userId);
        $data = json_encode($battleState);
        
        if ($data === false) {
            error_log("BattleStateManager: JSON encode failed for user {$userId}");
            return false;
        }
        
        error_log("BattleStateManager: Saving state for user {$userId}, data length: " . strlen($data));
        
        try {
            if ($existingId) {
                $stmt = $this->db->prepare("
                    UPDATE battle_states 
                    SET state = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $result = $stmt->execute([$data, $existingId]);
                error_log("BattleStateManager: Update result: " . ($result ? 'success' : 'failed'));
                return $result;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO battle_states (user_id, mission_id, state, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            
            $result = $stmt->execute([
                $userId,
                $battleState['mission_id'] ?? null,
                $data
            ]);
            error_log("BattleStateManager: Insert result: " . ($result ? 'success' : 'failed'));
            return $result;
        } catch (\Exception $e) {
            error_log("BattleStateManager: Exception saving state: " . $e->getMessage());
            return false;
        }
    }
    
    private function getFromDatabase(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT state FROM battle_states 
            WHERE user_id = ? 
            AND updated_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$userId, $this->ttl]);
        $result = $stmt->fetch();
        
        if (!$result) {
            error_log("BattleStateManager: No battle state found for user {$userId}");
            return null;
        }
        
        $decoded = json_decode($result['state'], true);
        error_log("BattleStateManager: Retrieved state for user {$userId}, has battle_state: " . (isset($decoded['battle_state']) ? 'yes' : 'no'));
        
        return $decoded;
    }
    
    private function deleteFromDatabase(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM battle_states WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    private function getExistingId(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT id FROM battle_states WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['id'] : null;
    }
}
