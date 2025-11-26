-- Tạo bảng lưu OTP
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    purpose VARCHAR(50) NOT NULL COMMENT 'reset_password, verify_phone, etc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_phone (phone),
    INDEX idx_otp (otp),
    INDEX idx_expires (expires_at)
);

-- Thêm cột phone vào bảng users nếu chưa có
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_phone (phone);
