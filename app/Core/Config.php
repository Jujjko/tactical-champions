<?php
declare(strict_types=1);

namespace Core;

class Config {
    private static array $cache = [];
    
    public static function get(string $key, mixed $default = null): mixed {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $value = $_ENV[$key] ?? $default;
        
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if (is_string($value) && is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        self::$cache[$key] = $value;
        return $value;
    }
    
    public static function set(string $key, mixed $value): void {
        self::$cache[$key] = $value;
        $_ENV[$key] = $value;
    }
    
    public static function has(string $key): bool {
        return isset($_ENV[$key]) || isset(self::$cache[$key]);
    }
    
    public static function app(string $key, mixed $default = null): mixed {
        return self::get("APP_{$key}", $default);
    }
    
    public static function db(string $key, mixed $default = null): mixed {
        return self::get("DB_{$key}", $default);
    }
    
    public static function appUrl(): string {
        return self::get('APP_URL', 'http://localhost');
    }
    
    public static function appName(): string {
        return self::get('APP_NAME', 'Tactical Champions');
    }
    
    public static function isDebug(): bool {
        return self::get('APP_DEBUG', false) === true;
    }
    
    public static function isProduction(): bool {
        return self::get('APP_ENV', 'local') === 'production';
    }
    
    public static function sessionLifetime(): int {
        return (int)self::get('SESSION_LIFETIME', 7200);
    }
    
    public static function maxLoginAttempts(): int {
        return (int)self::get('MAX_LOGIN_ATTEMPTS', 5);
    }
    
    public static function lockoutTime(): int {
        return (int)self::get('LOCKOUT_TIME', 900);
    }
}