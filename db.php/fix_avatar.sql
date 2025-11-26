-- Fix avatar column nếu chưa có
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL;

-- Kiểm tra avatar của user
SELECT id, username, avatar FROM users;

-- Reset avatar về NULL nếu cần test lại
-- UPDATE users SET avatar = NULL WHERE id = YOUR_USER_ID;
