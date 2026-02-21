<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class PvpRating extends Model {
    protected string $table = 'pvp_ratings';
    
    public function getOrCreateForUser(int $userId): array {
        $rating = $this->whereFirst('user_id', $userId);
        
        if (!$rating) {
            $this->create([
                'user_id' => $userId,
                'rating' => 1000,
                'wins' => 0,
                'losses' => 0
            ]);
            $rating = $this->whereFirst('user_id', $userId);
        }
        
        return $rating;
    }
    
    public function updateAfterBattle(int $userId, bool $won, int $ratingChange): void {
        $rating = $this->getOrCreateForUser($userId);
        
        $newRating = max(0, $rating['rating'] + $ratingChange);
        $newWins = $rating['wins'] + ($won ? 1 : 0);
        $newLosses = $rating['losses'] + ($won ? 0 : 1);
        
        $streak = $won ? $rating['current_streak'] + 1 : 0;
        
        $this->update($rating['id'], [
            'rating' => $newRating,
            'wins' => $newWins,
            'losses' => $newLosses,
            'highest_rating' => max($rating['highest_rating'], $newRating),
            'current_streak' => $streak,
            'best_streak' => max($rating['best_streak'], $streak)
        ]);
    }
    
    public function getLeaderboard(int $limit = 100): array {
        $stmt = $this->db->prepare("
            SELECT pr.*, u.username, u.level
            FROM {$this->table} pr
            JOIN users u ON pr.user_id = u.id
            ORDER BY pr.rating DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
