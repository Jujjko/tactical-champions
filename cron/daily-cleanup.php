<?php
/**
 * Daily Cleanup Cron Job
 * 
 * Run this script daily via cron:
 * 0 1 * * * php /path/to/cron/daily-cleanup.php
 * 
 * This script:
 * 1. Clears old logs
 * 2. Cleans up stale matchmaking queue entries
 * 3. Expires old challenges
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Logger;
use App\Models\ArenaQueue;
use Core\Database;

$logger = new Logger();

try {
    $logger->info('Daily cleanup cron started');
    
    $logger->clearOldLogs(30);
    $logger->info('Cleared logs older than 30 days');
    
    $arenaQueue = new ArenaQueue();
    $cleaned = $arenaQueue->cleanupStale(5);
    $logger->info("Cleaned {$cleaned} stale queue entries");
    
    $db = Database::getInstance();
    $db->prepare("
        UPDATE pvp_challenges 
        SET status = 'expired' 
        WHERE status = 'pending' AND expires_at < NOW()
    ")->execute();
    
    $expired = $db->prepare("SELECT ROW_COUNT() as count")->fetch();
    $logger->info("Expired {$expired['count']} old challenges");
    
} catch (\Throwable $e) {
    $logger->exception($e);
    exit(1);
}

$logger->info('Daily cleanup cron completed');
exit(0);