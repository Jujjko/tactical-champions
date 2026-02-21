<?php
declare(strict_types=1);

namespace App\Services;

class RateLimiter {
    private string $storageDir;
    private int $maxAttempts;
    private int $lockoutTime;
    
    public function __construct(int $maxAttempts = 5, int $lockoutTime = 900) {
        $this->storageDir = __DIR__ . '/../../logs/rate_limits/';
        $this->maxAttempts = $maxAttempts;
        $this->lockoutTime = $lockoutTime;
        
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }
    
    public function tooManyAttempts(string $key): bool {
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
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function getRemainingAttempts(string $key): int {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $this->maxAttempts;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return $this->maxAttempts;
        }
        
        return max(0, $this->maxAttempts - $data['attempts']);
    }
    
    private function getFilePath(string $key): string {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->storageDir . $safeKey . '.json';
    }
}