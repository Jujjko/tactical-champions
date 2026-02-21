<?php
declare(strict_types=1);

namespace Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }
    
    public static function destroy(): void {
        session_destroy();
    }
    
    public static function flash(string $key, mixed $value): void {
        $_SESSION['flash'][$key] = $value;
    }
    
    public static function getFlash(string $key): mixed {
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    
    public static function userId(): ?int {
        return self::get('user_id');
    }
    
    public static function isLoggedIn(): bool {
        return self::has('user_id');
    }
    
    public static function userRole(): ?string {
        return self::get('user_role');
    }
    
    public static function isAdmin(): bool {
        return self::userRole() === 'admin';
    }
    
    public static function isModerator(): bool {
        return in_array(self::userRole(), ['admin', 'moderator']);
    }
    
    public static function csrfToken(): string {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }
    
    public static function validateCsrfToken(?string $token): bool {
        if (!$token || !self::has('csrf_token')) {
            return false;
        }
        return hash_equals(self::get('csrf_token'), $token);
    }
    
    public static function regenerateId(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}