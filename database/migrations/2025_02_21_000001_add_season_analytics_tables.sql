-- Migration: Add season rewards and analytics tables
-- Version: 2025_02_21_000001
-- Description: Tables for season rewards, analytics events, and PvP seasons

-- PvP Seasons
CREATE TABLE IF NOT EXISTS pvp_seasons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT FALSE,
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    rewards_distributed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_dates (starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Season Rewards (auto-distributed)
CREATE TABLE IF NOT EXISTS season_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    season_id INT NOT NULL,
    user_id INT NOT NULL,
    final_rank INT NOT NULL,
    final_rating INT NOT NULL,
    reward_gold INT DEFAULT 0,
    reward_gems INT DEFAULT 0,
    reward_lootboxes INT DEFAULT 0,
    claimed BOOLEAN DEFAULT FALSE,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (season_id) REFERENCES pvp_seasons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY season_user (season_id, user_id),
    INDEX idx_user (user_id),
    INDEX idx_claimed (claimed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Analytics Events
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_category VARCHAR(50) NOT NULL,
    event_data JSON NULL,
    session_id VARCHAR(128) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (event_type),
    INDEX idx_category (event_category),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert first season
INSERT INTO pvp_seasons (name, description, is_active, starts_at, ends_at) VALUES
('Season 1: Dawn of Champions', 'The first competitive season of Tactical Champions', TRUE, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));