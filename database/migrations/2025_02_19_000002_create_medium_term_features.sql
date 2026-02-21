-- Migration: Create medium-term feature tables
-- Version: 2025_02_19_000002
-- Description: Creates tables for PvP Arena, Guilds, Achievements, Shop, Battle Pass, Referrals, Friends

-- ============================================
-- PvP ARENA
-- ============================================

CREATE TABLE IF NOT EXISTS arena_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    champion_id INT NOT NULL,
    rating INT DEFAULT 1000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (champion_id) REFERENCES user_champions(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pvp_challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    challenger_id INT NOT NULL,
    challenger_champion_id INT NOT NULL,
    defender_id INT NOT NULL,
    defender_champion_id INT NULL,
    status ENUM('pending', 'accepted', 'declined', 'completed', 'expired') DEFAULT 'pending',
    winner_id INT NULL,
    rewards_gold INT DEFAULT 100,
    rewards_gems INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (challenger_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (defender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_challenger (challenger_id),
    INDEX idx_defender (defender_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pvp_battles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attacker_id INT NOT NULL,
    attacker_champion_id INT NOT NULL,
    defender_id INT NOT NULL,
    defender_champion_id INT NOT NULL,
    winner_id INT NOT NULL,
    loser_id INT NOT NULL,
    attacker_rating_change INT DEFAULT 0,
    defender_rating_change INT DEFAULT 0,
    duration_seconds INT,
    battle_log TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attacker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (defender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_attacker (attacker_id),
    INDEX idx_defender (defender_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pvp_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    rating INT DEFAULT 1000,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    highest_rating INT DEFAULT 1000,
    current_streak INT DEFAULT 0,
    best_streak INT DEFAULT 0,
    season INT DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_rating (rating DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- GUILDS / CLANS
-- ============================================

CREATE TABLE IF NOT EXISTS guilds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    tag VARCHAR(5) NOT NULL UNIQUE,
    description TEXT,
    leader_id INT NOT NULL,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    gold_treasury INT DEFAULT 0,
    gems_treasury INT DEFAULT 0,
    max_members INT DEFAULT 50,
    icon VARCHAR(255) DEFAULT 'sword',
    banner_color VARCHAR(7) DEFAULT '#6366f1',
    is_recruiting BOOLEAN DEFAULT TRUE,
    min_level_req INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_name (name),
    INDEX idx_level (level),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS guild_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    guild_id INT NOT NULL,
    user_id INT NOT NULL UNIQUE,
    role ENUM('leader', 'officer', 'veteran', 'member', 'recruit') DEFAULT 'recruit',
    contribution_gold INT DEFAULT 0,
    contribution_gems INT DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_guild (guild_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS guild_invites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    guild_id INT NOT NULL,
    inviter_id INT NOT NULL,
    invitee_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'expired') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (inviter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invitee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_invitee (invitee_id, status),
    INDEX idx_guild (guild_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- ACHIEVEMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('battle', 'champion', 'social', 'progression', 'special') DEFAULT 'progression',
    icon VARCHAR(50) DEFAULT 'trophy',
    requirement_type VARCHAR(50) NOT NULL,
    requirement_value INT NOT NULL,
    reward_gold INT DEFAULT 0,
    reward_gems INT DEFAULT 0,
    reward_experience INT DEFAULT 0,
    is_hidden BOOLEAN DEFAULT FALSE,
    rarity ENUM('common', 'rare', 'epic', 'legendary') DEFAULT 'common',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_type (requirement_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    progress INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY user_achievement (user_id, achievement_id),
    INDEX idx_user (user_id),
    INDEX idx_completed (completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- IN-GAME SHOP
-- ============================================

CREATE TABLE IF NOT EXISTS shop_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('gems', 'gold', 'energy', 'special', 'cosmetic') DEFAULT 'special',
    item_type VARCHAR(50) NOT NULL,
    item_value INT NOT NULL,
    price_gems INT DEFAULT 0,
    price_gold INT DEFAULT 0,
    icon VARCHAR(50) DEFAULT 'box',
    is_featured BOOLEAN DEFAULT FALSE,
    is_limited BOOLEAN DEFAULT FALSE,
    limited_quantity INT NULL,
    sold_count INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shop_item_id INT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    total_gems INT DEFAULT 0,
    total_gold INT DEFAULT 0,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shop_item_id) REFERENCES shop_items(id) ON DELETE SET NULL,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BATTLE PASS
-- ============================================

CREATE TABLE IF NOT EXISTS battle_pass_seasons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    level_count INT DEFAULT 50,
    is_active BOOLEAN DEFAULT FALSE,
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_dates (starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS battle_pass_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    season_id INT NOT NULL,
    level INT NOT NULL,
    free_reward_type ENUM('gold', 'gems', 'energy', 'lootbox') NULL,
    free_reward_value INT DEFAULT 0,
    premium_reward_type ENUM('gold', 'gems', 'energy', 'lootbox') NULL,
    premium_reward_value INT DEFAULT 0,
    FOREIGN KEY (season_id) REFERENCES battle_pass_seasons(id) ON DELETE CASCADE,
    UNIQUE KEY season_level (season_id, level),
    INDEX idx_season (season_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_battle_passes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    season_id INT NOT NULL,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    is_premium BOOLEAN DEFAULT FALSE,
    premium_purchased_at TIMESTAMP NULL,
    last_claimed_free_level INT DEFAULT 0,
    last_claimed_premium_level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (season_id) REFERENCES battle_pass_seasons(id) ON DELETE CASCADE,
    UNIQUE KEY user_season (user_id, season_id),
    INDEX idx_user (user_id),
    INDEX idx_season (season_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- REFERRALS
-- ============================================

CREATE TABLE IF NOT EXISTS referral_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    uses INT DEFAULT 0,
    max_uses INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    referrer_id INT NOT NULL,
    referee_id INT NOT NULL UNIQUE,
    status ENUM('registered', 'level_5', 'level_10', 'completed') DEFAULT 'registered',
    referrer_reward_claimed BOOLEAN DEFAULT FALSE,
    referee_reward_claimed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referrer (referrer_id),
    INDEX idx_referee (referee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- FRIENDS SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS friends (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY friendship (user_id, friend_id),
    INDEX idx_user (user_id),
    INDEX idx_friend (friend_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DEFAULT DATA
-- ============================================

INSERT INTO achievements (name, description, category, icon, requirement_type, requirement_value, reward_gold, reward_gems, rarity) VALUES
('First Victory', 'Win your first battle', 'battle', 'sword', 'battles_won', 1, 100, 0, 'common'),
('Battle Hardened', 'Win 10 battles', 'battle', 'shield', 'battles_won', 10, 500, 5, 'common'),
('War Veteran', 'Win 50 battles', 'battle', 'medal', 'battles_won', 50, 2000, 20, 'rare'),
('War Master', 'Win 100 battles', 'battle', 'star', 'battles_won', 100, 5000, 50, 'epic'),
('Champion Collector', 'Own 5 champions', 'champion', 'box', 'champions_owned', 5, 200, 0, 'common'),
('Champion Master', 'Own 20 champions', 'champion', 'star', 'champions_owned', 20, 1000, 10, 'rare'),
('Rising Star', 'Reach level 10', 'progression', 'chart', 'player_level', 10, 500, 5, 'common'),
('Experienced', 'Reach level 25', 'progression', 'target', 'player_level', 25, 2000, 20, 'rare'),
('Veteran', 'Reach level 50', 'progression', 'gem', 'player_level', 50, 10000, 100, 'epic'),
('Friendly', 'Add 5 friends', 'social', 'users', 'friends_added', 5, 200, 0, 'common'),
('Popular', 'Add 20 friends', 'social', 'users', 'friends_added', 20, 1000, 10, 'rare');

INSERT INTO shop_items (name, description, category, item_type, item_value, price_gems, price_gold, icon, sort_order) VALUES
('Gold Pouch', '1000 Gold', 'gold', 'gold', 1000, 10, 0, 'coins', 10),
('Gold Sack', '5000 Gold', 'gold', 'gold', 5000, 45, 0, 'coins', 11),
('Gold Chest', '15000 Gold', 'gold', 'gold', 15000, 120, 0, 'coins', 12),
('Energy Drink', '50 Energy', 'energy', 'energy', 50, 5, 0, 'zap', 20),
('Energy Boost', '100 Energy', 'energy', 'energy', 100, 9, 0, 'zap', 21),
('Full Energy', 'Full Energy Refill', 'energy', 'energy_refill', 1, 15, 0, 'zap', 22),
('Bronze Lootbox', 'Contains random champion', 'special', 'lootbox_bronze', 1, 0, 500, 'box', 30),
('Silver Lootbox', 'Higher drop rate', 'special', 'lootbox_silver', 1, 20, 0, 'box', 31),
('Gold Lootbox', 'Best drop rate', 'special', 'lootbox_gold', 1, 50, 0, 'box', 32);
