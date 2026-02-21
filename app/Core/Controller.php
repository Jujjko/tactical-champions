<?php
declare(strict_types=1);

namespace Core;

abstract class Controller {
    protected function view(string $view, array $data = []): void {
        extract($data);
        require_once __DIR__ . "/../Views/{$view}.php";
    }
    
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function jsonSuccess(array $data = [], string $message = 'Success'): void {
        $this->json(array_merge(['success' => true, 'message' => $message], $data));
    }
    
    protected function jsonError(string $message, int $statusCode = 400): void {
        $this->json(['success' => false, 'error' => $message], $statusCode);
    }
    
    protected function redirect(string $path): void {
        header("Location: {$path}");
        exit;
    }
    
    protected function redirectWithSuccess(string $path, string $message): void {
        Session::flash('success', $message);
        $this->redirect($path);
    }
    
    protected function redirectWithError(string $path, string $message): void {
        Session::flash('error', $message);
        $this->redirect($path);
    }
    
    protected function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }
    
    protected function backWithSuccess(string $message): void {
        Session::flash('success', $message);
        $this->back();
    }
    
    protected function backWithError(string $message): void {
        Session::flash('error', $message);
        $this->back();
    }
    
    protected function validateCsrf(): bool {
        return Session::validateCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    }
    
    protected function requireCsrf(): void {
        if (!$this->validateCsrf()) {
            if ($this->isAjax()) {
                $this->jsonError('Invalid CSRF token', 403);
            }
            $this->redirectWithError('/login', 'Invalid request');
        }
    }
    
    protected function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    protected function validate(array $data, array $rules): array {
        $validator = new Validator();
        $isValid = $validator->validate($data, $rules);
        
        return [
            'valid' => $isValid,
            'errors' => $validator->errors(),
            'firstError' => $validator->firstError(array_key_first($validator->errors()) ?? '')
        ];
    }
}