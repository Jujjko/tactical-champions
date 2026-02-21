<?php
declare(strict_types=1);

namespace App\Services;

use Core\Config;
use Predis\Client;

class RedisRateLimiter {
    private ?Client $redis = null;
    private bool $useRedis = false;
    private string $storageDir;
    private int $maxAttempts;
    private int $lockoutTime;
    private string $prefix = 'rate_limit:';
    
    public function __construct(int $maxAttempts = 5, int $lockoutTime = 900) {
        $this->storageDir = __DIR__ . '/../../logs/rate_limits/';
        $this->maxAttempts = $maxAttempts;
        $this->lockoutTime = $lockoutTime;
        
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        
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
        } catch (\Exception $e) {
            $this->redis = null;
            $this->useRedis = false;
        }
    }
    
    public function tooManyAttempts(string $key): bool {
        if ($this->useRedis && $this->redis) {
            return $this->tooManyAttemptsRedis($key);
        }
        return $this->tooManyAttemptsFile($key);
    }
    
    private function tooManyAttemptsRedis(string $key): bool {
        $redisKey = $this->prefix . $key;
        $lockoutKey = $redisKey . ':locked';
        
        if ($this->redis->exists($lockoutKey)) {
            return true;
        }
        
        $attempts = (int)$this->redis->get($redisKey);
        
        if ($attempts >= $this->maxAttempts) {
            $this->redis->setex($lockoutKey, $this->lockoutTime, '1');
            return true;
        }
        
        return false;
    }
    
    private function tooManyAttemptsFile(string $key): bool {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return false;
        }
        
        if ($data['locked_until'] && time() < $data['locked_until']) {
            return true;
        }
        
        if ($data['locked_until'] && time() >= $data['locked_until']) {
            $this->clear($key);
            return false;
        }
        
        return false;
    }
    
    public function getRemainingLockoutTime(string $key): int {
        if ($this->useRedis && $this->redis) {
            $lockoutKey = $this->prefix . $key . ':locked';
            $ttl = $this->redis->ttl($lockoutKey);
            return max(0, $ttl);
        }
        
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data || !$data['locked_until']) {
            return 0;
        }
        
        return max(0, $data['locked_until'] - time());
    }
    
    public function hit(string $key): int {
        if ($this->useRedis && $this->redis) {
            return $this->hitRedis($key);
        }
        return $this->hitFile($key);
    }
    
    private function hitRedis(string $key): int {
        $redisKey = $this->prefix . $key;
        $lockoutKey = $redisKey . ':locked';
        
        $attempts = $this->redis->incr($redisKey);
        
        if ($attempts === 1) {
            $this->redis->expire($redisKey, $this->lockoutTime);
        }
        
        if ($attempts >= $this->maxAttempts) {
            $this->redis->setex($lockoutKey, $this->lockoutTime, '1');
        }
        
        return $attempts;
    }
    
    private function hitFile(string $key): int {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        } else {
            $data = ['attempts' => 0, 'first_attempt' => time(), 'locked_until' => null];
        }
        
        $data['attempts']++;
        $data['last_attempt'] = time();
        
        if ($data['attempts'] >= $this->maxAttempts && !$data['locked_until']) {
            $data['locked_until'] = time() + $this->lockoutTime;
        }
        
        file_put_contents($file, json_encode($data));
        
        return $data['attempts'];
    }
    
    public function clear(string $key): void {
        if ($this->useRedis && $this->redis) {
            $redisKey = $this->prefix . $key;
            $lockoutKey = $redisKey . ':locked';
            $this->redis->del([$redisKey, $lockoutKey]);
            return;
        }
        
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function getRemainingAttempts(string $key): int {
        $attempts = $this->getAttempts($key);
        return max(0, $this->maxAttempts - $attempts);
    }
    
    public function getAttempts(string $key): int {
        if ($this->useRedis && $this->redis) {
            $redisKey = $this->prefix . $key;
            return (int)$this->redis->get($redisKey);
        }
        
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        return $data['attempts'] ?? 0;
    }
    
    public function getDriver(): string {
        return $this->useRedis ? 'redis' : 'file';
    }
    
    private function getFilePath(string $key): string {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->storageDir . $safeKey . '.json';
    }
}
