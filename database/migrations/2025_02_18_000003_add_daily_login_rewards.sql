-- Migration: Add daily login rewards system
-- Version: 2025_02_18_000003
-- Description: Tracks daily logins, streaks, and rewards

-- Add streak columns to users table
ALTER TABLE users 
ADD COLUMN login_streak INT DEFAULT 0 AFTER last_login,
ADD COLUMN last_daily_login DATE NULL AFTER login_streak,
ADD COLUMN total_daily_logins INT DEFAULT 0 AFTER last_daily_login;

-- Create daily login rewards table for tracking claimed rewards
CREATE TABLE IF NOT EXISTS daily_login_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_date DATE NOT NULL,
    streak_day INT NOT NULL,
    gold_reward INT DEFAULT 0,
    gems_reward INT DEFAULT 0,
    energy_reward INT DEFAULT 0,
    bonus_type VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, login_date),
    INDEX idx_user (user_id),
    INDEX idx_date (login_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
