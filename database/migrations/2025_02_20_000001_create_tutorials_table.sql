-- Migration: Create tutorials table
-- Version: 2025_02_20_000001
-- Description: Tutorial system for new players

CREATE TABLE IF NOT EXISTS user_tutorials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tutorial_step VARCHAR(50) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY user_step (user_id, tutorial_step),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;