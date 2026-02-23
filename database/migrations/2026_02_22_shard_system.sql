-- Shard System Migration
-- Multi-tier star system: White -> Blue -> Red -> Gold

-- 1. Tabela za shardove
CREATE TABLE IF NOT EXISTS champion_shards (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    champion_id BIGINT NOT NULL,
    amount INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_champion (user_id, champion_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (champion_id) REFERENCES champions(id) ON DELETE CASCADE
);

-- 2. Dodaj stars i star_tier u user_champions ako ne postoje
SET @dbname = DATABASE();
SET @tablename = 'user_champions';

-- Dodaj stars ako ne postoji
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'stars');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE user_champions ADD COLUMN stars INT NOT NULL DEFAULT 1 AFTER level', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Dodaj star_tier ako ne postoji
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'star_tier');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE user_champions ADD COLUMN star_tier VARCHAR(10) NOT NULL DEFAULT ''white'' AFTER stars', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Indexi za brzinu
CREATE INDEX IF NOT EXISTS idx_champion_shards_user ON champion_shards(user_id);
CREATE INDEX IF NOT EXISTS idx_champion_shards_champion ON champion_shards(champion_id);

-- 4. Ažuriraj postojeće zapise
UPDATE user_champions SET stars = 1 WHERE stars IS NULL OR stars = 0;
UPDATE user_champions SET star_tier = 'white' WHERE star_tier IS NULL OR star_tier = '';
