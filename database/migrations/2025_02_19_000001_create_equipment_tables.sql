-- Migration: Create equipment tables
-- Version: 2025_02_19_000001
-- Description: Creates equipment system tables for weapons and armor

-- Equipment templates (base equipment definitions)
CREATE TABLE IF NOT EXISTS equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('weapon', 'armor', 'accessory') NOT NULL,
    slot ENUM('main_hand', 'off_hand', 'helmet', 'chest', 'gloves', 'boots', 'ring', 'amulet') NOT NULL,
    tier ENUM('common', 'rare', 'epic', 'legendary', 'mythic') DEFAULT 'common',
    health_bonus INT DEFAULT 0,
    attack_bonus INT DEFAULT 0,
    defense_bonus INT DEFAULT 0,
    speed_bonus INT DEFAULT 0,
    special_effect TEXT NULL,
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_type (type),
    INDEX idx_slot (slot),
    INDEX idx_tier (tier),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User equipment (owned equipment items)
CREATE TABLE IF NOT EXISTS user_equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    level INT DEFAULT 1,
    is_equipped BOOLEAN DEFAULT FALSE,
    equipped_to_champion_id INT NULL,
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (equipped_to_champion_id) REFERENCES user_champions(id) ON DELETE SET NULL,
    INDEX idx_user_equipment (user_id),
    INDEX idx_equipped (is_equipped),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample equipment
INSERT INTO equipment (name, type, slot, tier, health_bonus, attack_bonus, defense_bonus, speed_bonus, description) VALUES
-- Weapons
('Rusty Sword', 'weapon', 'main_hand', 'common', 0, 5, 0, 0, 'A basic sword for beginners'),
('Steel Blade', 'weapon', 'main_hand', 'rare', 0, 15, 2, 0, 'A reliable steel sword'),
('Shadow Dagger', 'weapon', 'main_hand', 'epic', 0, 25, 0, 10, 'A swift dagger that enhances speed'),
('Dragon Slayer', 'weapon', 'main_hand', 'legendary', 10, 40, 5, 0, 'A legendary blade forged in dragon fire'),
('Excalibur', 'weapon', 'main_hand', 'mythic', 50, 75, 20, 15, 'The legendary sword of kings'),

-- Shields (off-hand)
('Wooden Shield', 'weapon', 'off_hand', 'common', 10, 0, 5, 0, 'A simple wooden shield'),
('Iron Shield', 'weapon', 'off_hand', 'rare', 25, 0, 15, 0, 'A sturdy iron shield'),
('Tower Shield', 'weapon', 'off_hand', 'epic', 50, 0, 30, -5, 'A massive tower shield'),

-- Helmets
('Leather Cap', 'armor', 'helmet', 'common', 15, 0, 3, 0, 'Basic head protection'),
('Iron Helm', 'armor', 'helmet', 'rare', 30, 0, 10, 0, 'A solid iron helmet'),
('Dragon Helm', 'armor', 'helmet', 'legendary', 75, 5, 25, 0, 'Helmet crafted from dragon scales'),

-- Chest armor
('Cloth Robe', 'armor', 'chest', 'common', 20, 0, 5, 0, 'A simple cloth robe'),
('Chainmail', 'armor', 'chest', 'rare', 50, 0, 20, 0, 'Interlocking metal rings'),
('Plate Armor', 'armor', 'chest', 'epic', 100, 0, 40, -5, 'Heavy plate armor'),
('Phoenix Armor', 'armor', 'chest', 'mythic', 200, 20, 60, 10, 'Armor blessed by the phoenix'),

-- Gloves
('Cloth Gloves', 'armor', 'gloves', 'common', 5, 2, 2, 0, 'Basic hand protection'),
('Gauntlets', 'armor', 'gloves', 'rare', 15, 5, 10, 0, 'Metal gauntlets for battle'),

-- Boots
('Leather Boots', 'armor', 'boots', 'common', 10, 0, 2, 5, 'Simple leather boots'),
('Swift Boots', 'armor', 'boots', 'epic', 20, 0, 5, 20, 'Boots enchanted with swiftness'),

-- Accessories
('Copper Ring', 'accessory', 'ring', 'common', 5, 2, 2, 2, 'A simple copper ring'),
('Ruby Ring', 'accessory', 'ring', 'rare', 15, 10, 5, 5, 'A ring set with a ruby'),
('Amulet of Strength', 'accessory', 'amulet', 'epic', 25, 20, 10, 0, 'An amulet that enhances strength'),
('Phoenix Amulet', 'accessory', 'amulet', 'mythic', 100, 30, 30, 20, 'A powerful amulet blessed by the phoenix');
