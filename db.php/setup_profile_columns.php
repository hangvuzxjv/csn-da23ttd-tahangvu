<?php
// setup_profile_columns.php - Tự động thêm cột vào database
include 'db.php';

try {
    // Kiểm tra và thêm cột display_name
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'display_name'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) DEFAULT NULL AFTER username");
        echo "✅ Đã thêm cột display_name<br>";
    } else {
        echo "✓ Cột display_name đã tồn tại<br>";
    }

    // Kiểm tra và thêm cột phone
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER email");
        echo "✅ Đã thêm cột phone<br>";
    } else {
        echo "✓ Cột phone đã tồn tại<br>";
    }

    // Kiểm tra và thêm cột avatar
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER email");
        echo "✅ Đã thêm cột avatar<br>";
    } else {
        echo "✓ Cột avatar đã tồn tại<br>";
    }

    // Cập nhật display_name cho user chưa có
    $pdo->exec("UPDATE users SET display_name = username WHERE display_name IS NULL");
    echo "✅ Đã cập nhật display_name cho các user<br>";

    echo "<br><strong style='color: green;'>✅ HOÀN THÀNH! Bạn có thể đóng trang này và quay lại profile.</strong>";

} catch (\PDOException $e) {
    echo "<strong style='color: red;'>❌ LỖI: " . $e->getMessage() . "</strong>";
}
?>
