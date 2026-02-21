<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Friend extends Model {
    protected string $table = 'friends';
    
    public function getFriends(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT f.*, u.username, u.level
            FROM {$this->table} f
            JOIN users u ON f.friend_id = u.id
            WHERE f.user_id = ? AND f.status = 'accepted'
            ORDER BY u.username
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getPendingRequests(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT f.*, u.username, u.level
            FROM {$this->table} f
            JOIN users u ON f.user_id = u.id
            WHERE f.friend_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getSentRequests(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT f.*, u.username, u.level
            FROM {$this->table} f
            JOIN users u ON f.friend_id = u.id
            WHERE f.user_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function sendRequest(int $userId, int $friendId): bool {
        if ($userId === $friendId) return false;
        
        $existing = $this->db->prepare("
            SELECT 1 FROM {$this->table} 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $existing->execute([$userId, $friendId, $friendId, $userId]);
        if ($existing->fetch()) return false;
        
        return (bool)$this->create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending'
        ]);
    }
    
    public function acceptRequest(int $userId, int $friendId): bool {
        $request = $this->db->prepare("
            SELECT id FROM {$this->table}
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        $request->execute([$friendId, $userId]);
        $requestId = $request->fetchColumn();
        
        if (!$requestId) return false;
        
        $this->update($requestId, ['status' => 'accepted']);
        
        $this->create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'accepted'
        ]);
        
        return true;
    }
    
    public function declineRequest(int $userId, int $friendId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table}
            WHERE user_id = ? AND friend_id = ? AND status = 'pending'
        ");
        return $stmt->execute([$friendId, $userId]);
    }
    
    public function removeFriend(int $userId, int $friendId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table}
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        return $stmt->execute([$userId, $friendId, $friendId, $userId]);
    }
    
    public function areFriends(int $userId, int $friendId): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM {$this->table}
            WHERE user_id = ? AND friend_id = ? AND status = 'accepted'
        ");
        $stmt->execute([$userId, $friendId]);
        return (bool)$stmt->fetch();
    }
    
    public function getFriendCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table}
            WHERE user_id = ? AND status = 'accepted'
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
