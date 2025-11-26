-- Setup Advanced Features Database Schema

-- 1. Thêm cột vào bảng posts
ALTER TABLE posts ADD COLUMN species VARCHAR(50) DEFAULT NULL COMMENT 'Loài: tôm, cá, trai, cua';
ALTER TABLE posts ADD COLUMN stage VARCHAR(50) DEFAULT NULL COMMENT 'Giai đoạn: giống, nuôi, thu hoạch';
ALTER TABLE posts ADD COLUMN views INT DEFAULT 0 COMMENT 'Số lượt xem';
ALTER TABLE posts ADD COLUMN rating_total INT DEFAULT 0 COMMENT 'Tổng điểm đánh giá';
ALTER TABLE posts ADD COLUMN rating_count INT DEFAULT 0 COMMENT 'Số lượt đánh giá';

-- 2. Bảng bookmarks (lưu bài viết)
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- 3. Bảng ratings (đánh giá bài viết)
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- 4. Bảng comments (bình luận)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Bảng badges (huy hiệu)
CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    requirement INT DEFAULT 0 COMMENT 'Số bài viết cần để đạt huy hiệu'
);

-- 6. Bảng user_badges (huy hiệu của user)
CREATE TABLE IF NOT EXISTS user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- 7. Thêm huy hiệu mẫu
INSERT INTO badges (name, description, icon, requirement) VALUES
('🌟 Người Mới', 'Đăng bài viết đầu tiên', '🌟', 1),
('🔥 Người Đóng Góp', 'Đăng 5 bài viết', '🔥', 5),
('💎 Chuyên Gia', 'Đăng 10 bài viết', '💎', 10),
('👑 Bậc Thầy', 'Đăng 20 bài viết', '👑', 20);

-- 8. Bảng price_tracking (theo dõi giá)
CREATE TABLE IF NOT EXISTS price_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    species VARCHAR(50) NOT NULL COMMENT 'Loài thủy sản',
    price DECIMAL(10,2) NOT NULL COMMENT 'Giá (VNĐ/kg)',
    location VARCHAR(100) DEFAULT 'Trà Vinh',
    recorded_at DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Thêm dữ liệu giá mẫu
INSERT INTO price_tracking (species, price, location, recorded_at) VALUES
('Tôm Thẻ', 180000, 'Trà Vinh', '2025-11-20'),
('Tôm Thẻ', 185000, 'Trà Vinh', '2025-11-21'),
('Tôm Thẻ', 190000, 'Trà Vinh', '2025-11-22'),
('Cá Tra', 32000, 'Trà Vinh', '2025-11-20'),
('Cá Tra', 33000, 'Trà Vinh', '2025-11-21'),
('Cá Tra', 31000, 'Trà Vinh', '2025-11-22');
