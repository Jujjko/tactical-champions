<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Session;

class AdminMiddleware {
    public function handle(): bool {
        if (!Session::isModerator()) {
            header('Location: /dashboard');
            Session::flash('error', 'Unauthorized access');
            exit;
        }
        return true;
    }
}