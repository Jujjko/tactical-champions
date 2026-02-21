<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Core\Session;

class AuditService {
    private AuditLog $auditLog;
    
    public function __construct() {
        $this->auditLog = new AuditLog();
    }
    
    public function log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): int {
        return $this->auditLog->log($action, $entityType, $entityId, $oldValues, $newValues);
    }
    
    public function logCreate(string $entityType, int $entityId, array $data): int {
        return $this->auditLog->logCreate($entityType, $entityId, $this->sanitizeData($data));
    }
    
    public function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues): int {
        return $this->auditLog->logUpdate(
            $entityType, 
            $entityId, 
            $this->sanitizeData($oldValues),
            $this->sanitizeData($newValues)
        );
    }
    
    public function logDelete(string $entityType, int $entityId, array $oldValues): int {
        return $this->auditLog->logDelete($entityType, $entityId, $this->sanitizeData($oldValues));
    }
    
    public function logToggle(string $entityType, int $entityId, string $field, bool $oldValue, bool $newValue): int {
        return $this->log(
            'toggle',
            $entityType,
            $entityId,
            [$field => $oldValue],
            [$field => $newValue]
        );
    }
    
    public function logLogin(int $userId, bool $success, ?string $reason = null): int {
        return $this->auditLog->log(
            $success ? 'login_success' : 'login_failed',
            'user',
            $userId,
            null,
            ['success' => $success, 'reason' => $reason]
        );
    }
    
    public function logLogout(int $userId): int {
        return $this->auditLog->logLogout($userId);
    }
    
    public function logPasswordReset(int $userId, bool $requested = true): int {
        return $this->log(
            $requested ? 'password_reset_requested' : 'password_reset_completed',
            'user',
            $userId
        );
    }
    
    public function logBattleAction(int $userId, int $battleId, string $action, array $details = []): int {
        return $this->log($action, 'battle', $battleId, null, $details);
    }
    
    public function logLootboxOpen(int $userId, int $lootboxId, array $rewards): int {
        return $this->log(
            'lootbox_opened',
            'lootbox',
            $lootboxId,
            null,
            $rewards
        );
    }
    
    public function logChampionAcquired(int $userId, int $championId, string $source): int {
        return $this->log(
            'champion_acquired',
            'champion',
            $championId,
            null,
            ['source' => $source, 'user_id' => $userId]
        );
    }
    
    private function sanitizeData(array $data): array {
        $sensitive = ['password', 'password_hash', 'token', 'csrf_token', 'api_key', 'secret'];
        
        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }
        
        return $data;
    }
    
    public function getRecentLogs(int $limit = 100): array {
        return $this->auditLog->getRecentLogs($limit);
    }
    
    public function getUserLogs(int $userId, int $limit = 50): array {
        return $this->auditLog->getUserLogs($userId, $limit);
    }
    
    public function getEntityLogs(string $entityType, int $entityId): array {
        return $this->auditLog->getEntityLogs($entityType, $entityId);
    }
}
