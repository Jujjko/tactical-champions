<?php
declare(strict_types=1);

namespace App\Services;

use Core\Database;

class AnalyticsService
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function track(
        string $eventType,
        string $category,
        ?int $userId = null,
        array $data = [],
        ?string $sessionId = null
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO analytics_events 
            (user_id, event_type, event_category, event_data, session_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $eventType,
            $category,
            !empty($data) ? json_encode($data) : null,
            $sessionId ?? ($_COOKIE['session_id'] ?? null),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
    
    public function pageView(string $page, ?int $userId = null): void
    {
        $this->track('page_view', 'navigation', $userId, [
            'page' => $page,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
        ]);
    }
    
    public function battleStart(int $userId, int $missionId, int $championId): void
    {
        $this->track('battle_start', 'gameplay', $userId, [
            'mission_id' => $missionId,
            'champion_id' => $championId,
        ]);
    }
    
    public function battleEnd(int $userId, string $result, int $duration, array $rewards): void
    {
        $this->track('battle_end', 'gameplay', $userId, [
            'result' => $result,
            'duration_seconds' => $duration,
            'gold_earned' => $rewards['gold'] ?? 0,
            'exp_earned' => $rewards['experience'] ?? 0,
        ]);
    }
    
    public function pvpBattle(int $userId, string $result, int $ratingChange): void
    {
        $this->track('pvp_battle', 'pvp', $userId, [
            'result' => $result,
            'rating_change' => $ratingChange,
        ]);
    }
    
    public function lootboxOpen(int $userId, string $type, string $result): void
    {
        $this->track('lootbox_open', 'economy', $userId, [
            'lootbox_type' => $type,
            'result' => $result,
        ]);
    }
    
    public function championUpgrade(int $userId, int $championId, int $newLevel): void
    {
        $this->track('champion_upgrade', 'progression', $userId, [
            'champion_id' => $championId,
            'new_level' => $newLevel,
        ]);
    }
    
    public function championFusion(int $userId, int $championId, int $newStars): void
    {
        $this->track('champion_fusion', 'progression', $userId, [
            'champion_id' => $championId,
            'new_stars' => $newStars,
        ]);
    }
    
    public function shopPurchase(int $userId, int $itemId, string $currency, int $amount): void
    {
        $this->track('shop_purchase', 'economy', $userId, [
            'item_id' => $itemId,
            'currency' => $currency,
            'amount' => $amount,
        ]);
    }
    
    public function questComplete(int $userId, int $questId, string $questType): void
    {
        $this->track('quest_complete', 'engagement', $userId, [
            'quest_id' => $questId,
            'quest_type' => $questType,
        ]);
    }
    
    public function login(int $userId, bool $isNewUser = false): void
    {
        $this->track('login', 'auth', $userId, [
            'is_new_user' => $isNewUser,
        ]);
    }
    
    public function logout(int $userId): void
    {
        $this->track('logout', 'auth', $userId);
    }
    
    public function getEventCounts(string $eventType, int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM analytics_events
            WHERE event_type = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$eventType, $days]);
        return $stmt->fetchAll();
    }
    
    public function getDailyActiveUsers(int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(DISTINCT user_id) as users
            FROM analytics_events
            WHERE user_id IS NOT NULL
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
    
    public function getTopEvents(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT event_type, event_category, COUNT(*) as count
            FROM analytics_events
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY event_type, event_category
            ORDER BY count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getRetentionData(int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(u.created_at) as cohort_date,
                COUNT(DISTINCT u.id) as total_users,
                COUNT(DISTINCT CASE WHEN DATE(ae.created_at) = DATE(u.created_at) THEN u.id END) as day_0,
                COUNT(DISTINCT CASE WHEN DATEDIFF(DATE(ae.created_at), DATE(u.created_at)) = 1 THEN u.id END) as day_1,
                COUNT(DISTINCT CASE WHEN DATEDIFF(DATE(ae.created_at), DATE(u.created_at)) = 7 THEN u.id END) as day_7,
                COUNT(DISTINCT CASE WHEN DATEDIFF(DATE(ae.created_at), DATE(u.created_at)) = 30 THEN u.id END) as day_30
            FROM users u
            LEFT JOIN analytics_events ae ON u.id = ae.user_id
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(u.created_at)
            ORDER BY cohort_date DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}