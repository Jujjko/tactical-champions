<?php
declare(strict_types=1);

namespace Core;

use PDO;

abstract class Model {
    protected PDO $db;
    protected string $table;
    protected bool $softDeletes = false;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findById(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findByIdWithTrashed(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function all(): array {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        return $this->db->query($sql)->fetchAll();
    }
    
    public function allWithTrashed(): array {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll();
    }
    
    public function onlyTrashed(): array {
        if (!$this->softDeletes) {
            return [];
        }
        
        return $this->db->query("SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL")->fetchAll();
    }
    
    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id', string $orderDir = 'DESC'): array {
        $offset = ($page - 1) * $perPage;
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
        
        $whereClause = $this->softDeletes ? "WHERE deleted_at IS NULL" : "";
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderBy} {$orderDir} LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        $total = $this->count();
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int)ceil($total / $perPage),
            'has_more' => $page < ceil($total / $perPage)
        ];
    }
    
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        $where = [];
        
        if ($this->softDeletes) {
            $where[] = "deleted_at IS NULL";
        }
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        
        return (int)$stmt->fetchColumn();
    }
    
    public function create(array $data): int {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);
        
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function delete(int $id): bool {
        if ($this->softDeletes) {
            return $this->softDelete($id);
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function softDelete(int $id): bool {
        if (!$this->softDeletes) {
            return $this->delete($id);
        }
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function forceDelete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function restore(int $id): bool {
        if (!$this->softDeletes) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function trashed(int $id): bool {
        if (!$this->softDeletes) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT deleted_at FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() !== null;
    }
    
    public function where(string $column, mixed $value, string $operator = '='): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
    
    public function whereFirst(string $column, mixed $value, string $operator = '='): ?array {
        $results = $this->where($column, $value, $operator);
        return $results[0] ?? null;
    }
}