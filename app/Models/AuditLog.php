<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Session;

class AuditLog extends Model {
    protected string $table = 'audit_log';
    
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): int {
        return $this->create([
            'user_id' => Session::userId(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public function logCreate(string $entityType, int $entityId, array $data): int {
        return $this->log('create', $entityType, $entityId, null, $data);
    }
    
    public function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues): int {
        return $this->log('update', $entityType, $entityId, $oldValues, $newValues);
    }
    
    public function logDelete(string $entityType, int $entityId, array $oldValues): int {
        return $this->log('delete', $entityType, $entityId, $oldValues, null);
    }
    
    public function logLogin(int $userId, bool $success): int {
        return $this->log(
            $success ? 'login_success' : 'login_failed',
            'user',
            $userId,
            null,
            ['success' => $success]
        );
    }
    
    public function logLogout(int $userId): int {
        return $this->log('logout', 'user', $userId);
    }
    
    public function getUserLogs(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getEntityLogs(string $entityType, int $entityId): array {
        $stmt = $this->db->prepare("
            SELECT al.*, u.username 
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity_type = ? AND al.entity_id = ?
            ORDER BY al.created_at DESC
        ");
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll();
    }
    
    public function getRecentLogs(int $limit = 100): array {
        $stmt = $this->db->prepare("
            SELECT al.*, u.username 
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}