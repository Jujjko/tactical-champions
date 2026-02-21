<?php
/**
 * Season Rewards Cron Job
 * 
 * Run this script daily via cron:
 * 0 0 * * * php /path/to/cron/season-rewards.php
 * 
 * This script:
 * 1. Checks if current season has ended
 * 2. Distributes rewards to all participants
 * 3. Resets ratings for new season
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\SeasonService;
use App\Services\Logger;

$logger = new Logger();
$seasonService = new SeasonService();

try {
    $logger->info('Season rewards cron started');
    
    $result = $seasonService->checkAndEndSeason();
    
    if ($result) {
        $logger->info('Season ended and rewards distributed');
    } else {
        $logger->debug('No season to end');
    }
    
} catch (\Throwable $e) {
    $logger->exception($e);
    exit(1);
}

$logger->info('Season rewards cron completed');
exit(0);