-- Add icon column if not exists
ALTER TABLE champions ADD COLUMN IF NOT EXISTS icon VARCHAR(10) DEFAULT NULL AFTER image_url;

-- Update existing champions with icons (images can be changed via admin panel)
UPDATE champions SET icon = '⚔️' WHERE name = 'Warrior Knight';
UPDATE champions SET icon = '🗡️' WHERE name = 'Shadow Assassin';
UPDATE champions SET icon = '❄️' WHERE name = 'Ice Mage';
UPDATE champions SET icon = '🐉' WHERE name = 'Dragon Lord';
UPDATE champions SET icon = '🔥' WHERE name = 'Phoenix Queen';

-- Optional: Set placeholder images (replace with your own hero images)
-- UPDATE champions SET image_url = 'YOUR_IMAGE_URL' WHERE name = 'Warrior Knight';
