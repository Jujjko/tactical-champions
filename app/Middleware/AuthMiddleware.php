<?php
// app/Middleware/AuthMiddleware.php
declare(strict_types=1);

namespace App\Middleware;

use Core\Session;

class AuthMiddleware {
    public function handle(): bool {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        return true;
    }
}