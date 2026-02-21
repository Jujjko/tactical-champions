CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('player', 'moderator', 'admin') DEFAULT 'player',
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resources table
CREATE TABLE user_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    gold INT DEFAULT 100,
    gems INT DEFAULT 0,
    energy INT DEFAULT 100,
    max_energy INT DEFAULT 100,
    last_energy_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Champion templates (base champions)
CREATE TABLE champions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    tier ENUM('common', 'rare', 'epic', 'legendary', 'mythic') DEFAULT 'common',
    base_health INT DEFAULT 100,
    base_attack INT DEFAULT 10,
    base_defense INT DEFAULT 5,
    base_speed INT DEFAULT 50,
    special_ability TEXT,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User's champion collection
CREATE TABLE user_champions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    champion_id INT NOT NULL,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    health INT NOT NULL,
    attack INT NOT NULL,
    defense INT NOT NULL,
    speed INT NOT NULL,
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (champion_id) REFERENCES champions(id) ON DELETE CASCADE,
    INDEX idx_user_champions (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Missions
CREATE TABLE missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('easy', 'medium', 'hard', 'expert') DEFAULT 'easy',
    required_level INT DEFAULT 1,
    energy_cost INT DEFAULT 10,
    gold_reward INT DEFAULT 50,
    experience_reward INT DEFAULT 25,
    lootbox_chance DECIMAL(5,2) DEFAULT 10.00,
    enemy_count INT DEFAULT 3,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mission completions
CREATE TABLE mission_completions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mission_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    gold_earned INT DEFAULT 0,
    experience_earned INT DEFAULT 0,
    lootbox_earned BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE,
    INDEX idx_user_completions (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lootboxes
CREATE TABLE user_lootboxes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lootbox_type ENUM('bronze', 'silver', 'gold', 'diamond') DEFAULT 'bronze',
    opened BOOLEAN DEFAULT FALSE,
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_lootboxes (user_id, opened)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Battle history
CREATE TABLE battles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mission_id INT NULL,
    result ENUM('victory', 'defeat') NOT NULL,
    duration_seconds INT,
    champions_used TEXT,
    rewards_earned TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE SET NULL,
    INDEX idx_user_battles (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Session management
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role, level) 
VALUES ('admin', 'admin@tacticalgame.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 99);

-- Insert default resources for admin
INSERT INTO user_resources (user_id, gold, gems) 
VALUES (1, 10000, 1000);

-- Insert sample champions
INSERT INTO champions (name, tier, base_health, base_attack, base_defense, base_speed, special_ability, description) VALUES
('Warrior Knight', 'common', 120, 15, 10, 45, 'Shield Bash: Stun enemy for 1 turn', 'A basic warrior with balanced stats'),
('Shadow Assassin', 'rare', 80, 25, 5, 80, 'Critical Strike: Double damage attack', 'Fast and deadly, but fragile'),
('Ice Mage', 'epic', 90, 20, 8, 60, 'Frost Nova: Freeze all enemies', 'Master of ice magic'),
('Dragon Lord', 'legendary', 200, 35, 20, 55, 'Dragon Breath: Massive AoE damage', 'Ancient dragon in human form'),
('Phoenix Queen', 'mythic', 150, 40, 25, 70, 'Rebirth: Resurrect with full health once', 'Immortal phoenix wielder');

-- Insert sample missions
INSERT INTO missions (name, description, difficulty, required_level, energy_cost, gold_reward, experience_reward, lootbox_chance, enemy_count) VALUES
('Forest Bandits', 'Clear the forest of common bandits', 'easy', 1, 10, 50, 25, 15.00, 3),
('Cave Exploration', 'Explore the mysterious cave', 'medium', 5, 15, 100, 50, 20.00, 4),
('Ancient Ruins', 'Investigate the ancient ruins', 'hard', 10, 20, 200, 100, 30.00, 5),
('Dragon Lair', 'Challenge the dragon in its lair', 'expert', 20, 30, 500, 250, 50.00, 6);