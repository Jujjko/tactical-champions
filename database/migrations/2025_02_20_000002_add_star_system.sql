-- Migration: Add star system to champions
-- Version: 2025_02_20_000002
-- Description: Adds stars (1-5) to user_champions for fusion system

ALTER TABLE user_champions ADD COLUMN stars INT DEFAULT 1 AFTER speed;
ALTER TABLE user_champions ADD CONSTRAINT chk_stars CHECK (stars BETWEEN 1 AND 5);
CREATE INDEX idx_stars ON user_champions(stars);