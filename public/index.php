<?php
/**
 * ========================================
 * TACTICAL CHAMPIONS - GAME ENTRY POINT
 * ========================================
 * 
 * Main application entry point that initializes
 * the framework and dispatches requests.
 * 
 * @package TacticalChampions
 * @version 1.0.0
 */

declare(strict_types=1);

// Define application start time for performance monitoring
define('APP_START', microtime(true));

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// ========================================
// ERROR REPORTING CONFIGURATION
// ========================================

// Set error reporting based on environment
// This will be overridden by .env settings
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Global exception handler
set_exception_handler(function(Throwable $e) {
    error_log(sprintf(
        "[%s] %s in %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    
    if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title>';
        echo '<style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;}</style></head><body>';
        echo '<h1>Application Error</h1>';
        echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
        echo '<p>in ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</body></html>';
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title>';
        echo '<style>body{font-family:sans-serif;background:#1a1a2e;color:#eee;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}';
        echo '.container{text-align:center;padding:2rem;}</style></head><body>';
        echo '<div class="container"><h1>500</h1><p>Something went wrong. Please try again later.</p></div>';
        echo '</body></html>';
    }
});

// Global error handler (converts errors to exceptions)
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// ========================================
// COMPOSER AUTOLOADER
// ========================================

// Load Composer autoloader
if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
    die('
        <h1>Error: Dependencies Not Installed</h1>
        <p>Please run <code>composer install</code> to install dependencies.</p>
        <pre>composer install</pre>
    ');
}

require_once BASE_PATH . '/vendor/autoload.php';

// ========================================
// ENVIRONMENT CONFIGURATION
// ========================================

use Dotenv\Dotenv;
use Core\Router;
use Core\Database;
use Core\Session;

// Load environment variables
if (!file_exists(BASE_PATH . '/.env')) {
    die('
        <h1>Error: Environment File Missing</h1>
        <p>Please copy <code>.env.example</code> to <code>.env</code> and configure it.</p>
        <pre>cp .env.example .env</pre>
    ');
}

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Set error reporting based on environment
if ($_ENV['APP_DEBUG'] === 'false' || $_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// ========================================
// SESSION INITIALIZATION
// ========================================

try {
    // Configure session settings
    ini_set('session.cookie_httponly', $_ENV['SESSION_HTTPONLY'] ?? '1');
    ini_set('session.cookie_secure', $_ENV['SESSION_SECURE'] ?? '0');
    ini_set('session.cookie_samesite', $_ENV['SESSION_SAMESITE'] ?? 'Lax');
    ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME'] ?? '7200');
    
    // Start session
    Session::start();
} catch (Exception $e) {
    error_log('Session initialization failed: ' . $e->getMessage());
    die('Session initialization failed. Please check your configuration.');
}

// ========================================
// DATABASE INITIALIZATION
// ========================================

try {
    // Initialize database connection
    Database::getInstance();
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        die('
            <h1>Database Connection Failed</h1>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Please check your database configuration in <code>.env</code></p>
        ');
    } else {
        die('
            <h1>Service Unavailable</h1>
            <p>We are experiencing technical difficulties. Please try again later.</p>
        ');
    }
}

// ========================================
// MAINTENANCE MODE CHECK
// ========================================

if (isset($_ENV['MAINTENANCE_MODE']) && $_ENV['MAINTENANCE_MODE'] === 'true') {
    // Allow admin access during maintenance
    if (!Session::isAdmin()) {
        http_response_code(503);
        die('
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Maintenance Mode</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                        color: #f1f5f9;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        margin: 0;
                    }
                    .container {
                        text-align: center;
                        padding: 2rem;
                    }
                    h1 { font-size: 3rem; margin-bottom: 1rem; }
                    p { font-size: 1.2rem; color: #94a3b8; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>🛠️ Maintenance Mode</h1>
                    <p>' . htmlspecialchars($_ENV['MAINTENANCE_MESSAGE'] ?? 'We are currently performing maintenance.') . '</p>
                </div>
            </body>
            </html>
        ');
    }
}

// ========================================
// CONTAINER INITIALIZATION
// ========================================

use Core\Container;
use App\Providers\AppServiceProvider;

$container = Container::getInstance();
$container->singleton(Container::class, fn() => $container);

$serviceProvider = new AppServiceProvider();
$serviceProvider->register($container);

// ========================================
// ROUTER INITIALIZATION
// ========================================

try {
    // Initialize router
    $router = new Router();
    
    // Load routes configuration
    if (!file_exists(CONFIG_PATH . '/routes.php')) {
        throw new Exception('Routes configuration file not found');
    }
    
    require_once CONFIG_PATH . '/routes.php';
    
    // Dispatch the request
    $uri = $_SERVER['REQUEST_URI'];
    
    // Remove query string from URI
    if (($pos = strpos($uri, '?')) !== false) {
        $uri = substr($uri, 0, $pos);
    }
    
    // Remove base path if application is in subdirectory
    // Uncomment and adjust if your app is in a subdirectory like /tactical-champions/
    // $basePath = '/tactical-champions';
    // if (strpos($uri, $basePath) === 0) {
    //     $uri = substr($uri, strlen($basePath));
    // }
    
    $router->dispatch($uri);
    
} catch (Exception $e) {
    error_log('Routing error: ' . $e->getMessage());
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        echo '
            <h1>Routing Error</h1>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>
        ';
    } else {
        http_response_code(500);
        echo '
            <h1>Internal Server Error</h1>
            <p>Something went wrong. Please try again later.</p>
        ';
    }
}

// ========================================
// PERFORMANCE MONITORING (Development)
// ========================================

if ($_ENV['APP_DEBUG'] === 'true' && isset($_GET['debug'])) {
    $executionTime = microtime(true) - APP_START;
    $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
    
    echo '
        <div style="
            position: fixed;
            bottom: 0;
            right: 0;
            background: rgba(0,0,0,0.9);
            color: #0f0;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            border-radius: 5px 0 0 0;
            z-index: 9999;
        ">
            <div>Execution Time: ' . number_format($executionTime, 4) . 's</div>
            <div>Memory Usage: ' . number_format($memoryUsage, 2) . ' MB</div>
            <div>PHP Version: ' . PHP_VERSION . '</div>
        </div>
    ';
}

// ========================================
// END OF INDEX.PHP
// ========================================