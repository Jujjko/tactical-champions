<?php
declare(strict_types=1);

namespace App\Services;

use Core\Config;
use Predis\Client;

class CacheService {
    private static ?CacheService $instance = null;
    private ?Client $redis = null;
    private bool $enabled = false;
    private array $localCache = [];
    private int $defaultTtl = 300;
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->defaultTtl = (int)Config::get('CACHE_TTL', 300);
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
            $this->enabled = true;
        } catch (\Exception $e) {
            $this->redis = null;
            $this->enabled = false;
        }
    }
    
    public function get(string $key): ?array {
        if (isset($this->localCache[$key])) {
            return $this->localCache[$key];
        }
        
        if ($this->enabled && $this->redis) {
            $data = $this->redis->get($this->prefix($key));
            if ($data) {
                $decoded = json_decode($data, true);
                $this->localCache[$key] = $decoded;
                return $decoded;
            }
        }
        
        return null;
    }
    
    public function set(string $key, array $data, ?int $ttl = null): bool {
        $this->localCache[$key] = $data;
        
        if ($this->enabled && $this->redis) {
            $ttl = $ttl ?? $this->defaultTtl;
            $this->redis->setex($this->prefix($key), $ttl, json_encode($data));
        }
        
        return true;
    }
    
    public function delete(string $key): bool {
        unset($this->localCache[$key]);
        
        if ($this->enabled && $this->redis) {
            $this->redis->del([$this->prefix($key)]);
        }
        
        return true;
    }
    
    public function forget(string $pattern): int {
        $count = 0;
        
        foreach ($this->localCache as $key => $_) {
            if (str_contains($key, $pattern)) {
                unset($this->localCache[$key]);
                $count++;
            }
        }
        
        if ($this->enabled && $this->redis) {
            $keys = $this->redis->keys($this->prefix($pattern) . '*');
            if (!empty($keys)) {
                $count += $this->redis->del($keys);
            }
        }
        
        return $count;
    }
    
    public function remember(string $key, callable $callback, ?int $ttl = null): array {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    public function isEnabled(): bool {
        return $this->enabled;
    }
    
    private function prefix(string $key): string {
        return "cache:{$key}";
    }
    
    public function flush(): void {
        $this->localCache = [];
        
        if ($this->enabled && $this->redis) {
            $keys = $this->redis->keys('cache:*');
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        }
    }
}
