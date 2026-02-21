-- Migration: PvP Rewards and Tournament System
-- Version: 2026_02_21_0002
-- Description: Tables for PvP rewards logging and tournament system

CREATE TABLE IF NOT EXISTS pvp_rewards_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    battle_id INT NOT NULL,
    gold_earned INT DEFAULT 0,
    gems_earned INT DEFAULT 0,
    shard_earned TINYINT(1) DEFAULT 0,
    item_earned VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (battle_id) REFERENCES pvp_battles(id) ON DELETE CASCADE,
    INDEX idx_pvp_rewards_user (user_id),
    INDEX idx_pvp_rewards_battle (battle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tournaments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('single_elimination', 'round_robin', 'swiss') DEFAULT 'single_elimination',
    max_players INT NOT NULL DEFAULT 8,
    current_players INT DEFAULT 0,
    entry_fee_gold INT DEFAULT 0,
    entry_fee_gems INT DEFAULT 0,
    min_rating INT DEFAULT 0,
    status ENUM('draft', 'open', 'full', 'ongoing', 'finished', 'cancelled') DEFAULT 'draft',
    round_current INT DEFAULT 0,
    round_total INT DEFAULT 3,
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    winner_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (winner_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tournament_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    user_id INT NOT NULL,
    seed INT NULL,
    eliminated TINYINT(1) DEFAULT 0,
    eliminated_round INT NULL,
    final_rank INT NULL,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_participant (tournament_id, user_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tournament (tournament_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tournament_matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    round INT NOT NULL,
    match_number INT NOT NULL,
    player1_id INT NULL,
    player2_id INT NULL,
    winner_id INT NULL,
    loser_id INT NULL,
    status ENUM('pending', 'ongoing', 'finished', 'bye') DEFAULT 'pending',
    battle_id INT NULL,
    scheduled_at DATETIME NULL,
    finished_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (player1_id) REFERENCES users(id),
    FOREIGN KEY (player2_id) REFERENCES users(id),
    FOREIGN KEY (winner_id) REFERENCES users(id),
    FOREIGN KEY (loser_id) REFERENCES users(id),
    INDEX idx_tournament (tournament_id),
    INDEX idx_round (tournament_id, round),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tournament_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tournament_id INT NOT NULL,
    place INT NOT NULL,
    gold INT DEFAULT 0,
    gems INT DEFAULT 0,
    lootbox_type VARCHAR(50) NULL,
    lootbox_count INT DEFAULT 0,
    title VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    UNIQUE KEY tournament_place (tournament_id, place)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tournaments (name, description, type, max_players, entry_fee_gold, status, start_time, round_total) VALUES
('Weekly Championship', 'Compete for glory and prizes!', 'single_elimination', 8, 500, 'open', DATE_ADD(NOW(), INTERVAL 1 DAY), 3),
('Bronze Cup', 'Tournament for new players', 'single_elimination', 16, 100, 'open', DATE_ADD(NOW(), INTERVAL 2 DAY), 4),
('Diamond League', 'Elite players only - min 1600 rating', 'single_elimination', 8, 1000, 'draft', DATE_ADD(NOW(), INTERVAL 7 DAY), 3);

INSERT INTO tournament_rewards (tournament_id, place, gold, gems, lootbox_type, lootbox_count) VALUES
(1, 1, 5000, 100, 'gold', 3),
(1, 2, 2500, 50, 'silver', 2),
(1, 3, 1000, 25, 'bronze', 1),
(2, 1, 2000, 50, 'silver', 2),
(2, 2, 1000, 25, 'bronze', 1),
(3, 1, 10000, 200, 'diamond', 5),
(3, 2, 5000, 100, 'gold', 3);
