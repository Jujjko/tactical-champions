<?php

declare(strict_types=1);

namespace Core;

use Core\Database;

class Validator {
    private array $errors = [];
    private ?Database $db = null;
    
    public function validate(array $data, array $rules): bool {
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            
            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule(string $field, mixed $value, string $rule, array $data = []): void {
        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            if (strlen((string) $value) < $min) {
                $this->errors[$field][] = "{$field} must be at least {$min} characters";
            }
        } elseif (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if (strlen((string) $value) > $max) {
                $this->errors[$field][] = "{$field} must not exceed {$max} characters";
            }
        } elseif ($rule === 'required') {
            if (empty($value) && $value !== '0') {
                $this->errors[$field][] = "{$field} is required";
            }
        } elseif ($rule === 'email') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = "{$field} must be a valid email";
            }
        } elseif ($rule === 'numeric') {
            if (!is_numeric($value)) {
                $this->errors[$field][] = "{$field} must be numeric";
            }
        } elseif ($rule === 'integer') {
            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                $this->errors[$field][] = "{$field} must be an integer";
            }
        } elseif ($rule === 'alpha') {
            if (!ctype_alpha((string)$value)) {
                $this->errors[$field][] = "{$field} must contain only letters";
            }
        } elseif ($rule === 'alphanumeric') {
            if (!ctype_alnum((string)$value)) {
                $this->errors[$field][] = "{$field} must contain only letters and numbers";
            }
        } elseif ($rule === 'confirmed') {
            $confirmField = $field . '_confirm';
            if (!isset($data[$confirmField]) || $value !== $data[$confirmField]) {
                $this->errors[$field][] = "{$field} confirmation does not match";
            }
        } elseif (str_starts_with($rule, 'in:')) {
            $allowed = explode(',', substr($rule, 3));
            if (!in_array($value, $allowed)) {
                $this->errors[$field][] = "{$field} must be one of: " . implode(', ', $allowed);
            }
        } elseif (str_starts_with($rule, 'regex:')) {
            $pattern = substr($rule, 6);
            if (!preg_match($pattern, (string) $value)) {
                $this->errors[$field][] = "{$field} format is invalid";
            }
        } elseif (str_starts_with($rule, 'between:')) {
            $params = explode(',', substr($rule, 8));
            $min = (int) $params[0];
            $max = (int) ($params[1] ?? $min);
            $len = strlen((string) $value);
            if ($len < $min || $len > $max) {
                $this->errors[$field][] = "{$field} must be between {$min} and {$max} characters";
            }
        } elseif (str_starts_with($rule, 'min_value:')) {
            $min = (int) substr($rule, 10);
            if ((int) $value < $min) {
                $this->errors[$field][] = "{$field} must be at least {$min}";
            }
        } elseif (str_starts_with($rule, 'max_value:')) {
            $max = (int) substr($rule, 10);
            if ((int) $value > $max) {
                $this->errors[$field][] = "{$field} must not exceed {$max}";
            }
        } elseif (str_starts_with($rule, 'unique:')) {
            $this->validateUnique($field, $value, substr($rule, 7), $data['id'] ?? null);
        } elseif (str_starts_with($rule, 'exists:')) {
            $this->validateExists($field, $value, substr($rule, 7));
        }
    }
    
    private function validateUnique(string $field, mixed $value, string $params, ?int $excludeId = null): void {
        $db = $this->getDb();
        
        $parts = explode(',', $params);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
        $queryParams = [$value];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $queryParams[] = $excludeId;
        }
        
        $sql .= " AND deleted_at IS NULL";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($queryParams);
        
        if ($stmt->fetchColumn() > 0) {
            $this->errors[$field][] = "{$field} is already taken";
        }
    }
    
    private function validateExists(string $field, mixed $value, string $table): void {
        $db = $this->getDb();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE id = ?");
        $stmt->execute([$value]);
        
        if ($stmt->fetchColumn() == 0) {
            $this->errors[$field][] = "Selected {$field} does not exist";
        }
    }
    
    private function getDb(): Database {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }
    
    public function errors(): array {
        return $this->errors;
    }
    
    public function firstError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }
    
    public function hasErrors(): bool {
        return !empty($this->errors);
    }
    
    public function getErrorsForField(string $field): array {
        return $this->errors[$field] ?? [];
    }
}