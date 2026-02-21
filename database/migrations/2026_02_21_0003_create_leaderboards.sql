-- Migration: Leaderboards cache table
-- Version: 2026_02_21_0003
-- Description: Optimized leaderboard caching for fast retrieval

CREATE TABLE IF NOT EXISTS leaderboards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('global_level', 'global_wins', 'pvp_rank', 'season', 'guild') NOT NULL,
    user_id INT NOT NULL,
    score BIGINT NOT NULL DEFAULT 0,
    `rank` INT NOT NULL DEFAULT 0,
    season_id INT NULL,
    guild_id INT NULL,
    additional_data JSON NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type_score (type, score DESC),
    INDEX idx_type_rank (type, `rank`),
    INDEX idx_user_type (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leaderboard_snapshots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('global_level', 'global_wins', 'pvp_rank', 'season') NOT NULL,
    season_id INT NULL,
    snapshot_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_created (type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
