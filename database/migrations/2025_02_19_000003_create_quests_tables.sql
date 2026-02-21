-- Migration: Create quests system
-- Version: 2025_02_19_000003
-- Description: Daily and weekly quests for player retention

-- Quest definitions
CREATE TABLE IF NOT EXISTS quests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('daily', 'weekly', 'special') DEFAULT 'daily',
    category ENUM('battle', 'champion', 'social', 'progression', 'special') DEFAULT 'progression',
    requirement_type VARCHAR(50) NOT NULL,
    requirement_value INT NOT NULL,
    reward_gold INT DEFAULT 0,
    reward_gems INT DEFAULT 0,
    reward_experience INT DEFAULT 0,
    reward_battle_pass_xp INT DEFAULT 0,
    icon VARCHAR(50) DEFAULT 'scroll',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User quest progress
CREATE TABLE IF NOT EXISTS user_quests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quest_id INT NOT NULL,
    progress INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    claimed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_quest (quest_id),
    INDEX idx_completed (completed),
    INDEX idx_claimed (claimed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert daily quests
INSERT INTO quests (name, description, type, category, requirement_type, requirement_value, reward_gold, reward_gems, reward_experience, reward_battle_pass_xp, icon, sort_order) VALUES
-- Daily - Battle
('Daily Warrior', 'Complete 3 battles', 'daily', 'battle', 'battles', 3, 100, 0, 50, 100, 'sword', 1),
('Victorious Day', 'Win 2 battles', 'daily', 'battle', 'battles_won', 2, 150, 5, 75, 150, 'trophy', 2),
('Dungeon Explorer', 'Complete 5 missions', 'daily', 'battle', 'missions_completed', 5, 120, 0, 60, 120, 'map', 3),

-- Daily - Champion
('Champion Care', 'Upgrade a champion once', 'daily', 'champion', 'champion_upgrades', 1, 80, 0, 40, 80, 'star', 10),
('Equipment Master', 'Equip or unequip any item', 'daily', 'champion', 'equipment_changes', 1, 50, 0, 30, 60, 'shield', 11),

-- Daily - Social
('Social Butterfly', 'Send a friend request or challenge', 'daily', 'social', 'social_actions', 1, 60, 0, 35, 70, 'users', 20),

-- Daily - Progression
('Energy Spender', 'Use 50 energy', 'daily', 'progression', 'energy_spent', 50, 100, 0, 50, 100, 'zap', 30),
('Lootbox Opener', 'Open 1 lootbox', 'daily', 'progression', 'lootboxes_opened', 1, 75, 0, 40, 80, 'box', 31),

-- Weekly - Battle
('Weekly Warrior', 'Complete 25 battles', 'weekly', 'battle', 'battles', 25, 500, 25, 200, 500, 'sword', 100),
('Champion of the Week', 'Win 15 battles', 'weekly', 'battle', 'battles_won', 15, 750, 40, 300, 750, 'trophy', 101),
('Mission Marathon', 'Complete 20 missions', 'weekly', 'battle', 'missions_completed', 20, 600, 30, 250, 600, 'map', 102),

-- Weekly - Champion
('Champion Development', 'Upgrade champions 5 times', 'weekly', 'champion', 'champion_upgrades', 5, 400, 20, 150, 400, 'star', 110),

-- Weekly - Social
('Guild Contributor', 'Contribute to guild or join one', 'weekly', 'social', 'guild_actions', 1, 300, 15, 100, 300, 'flag', 120),
('Arena Fighter', 'Complete 5 PvP battles', 'weekly', 'social', 'pvp_battles', 5, 500, 30, 200, 500, 'swords', 121),

-- Weekly - Progression
('Energy Master', 'Use 300 energy', 'weekly', 'progression', 'energy_spent', 300, 450, 25, 180, 450, 'zap', 130),
('Treasure Hunter', 'Open 10 lootboxes', 'weekly', 'progression', 'lootboxes_opened', 10, 600, 35, 250, 600, 'box', 131);
