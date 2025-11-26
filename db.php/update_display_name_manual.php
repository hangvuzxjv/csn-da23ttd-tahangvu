<?php
// update_display_name_manual.php - Cập nhật display_name thủ công
include 'db.php';
include 'session_manager.php';

requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Cập nhật display_name
try {
    $stmt = $pdo->prepare("UPDATE users SET display_name = ? WHERE id = ?");
    $stmt->execute(['phuc huynh', $userId]);
    
    echo "✅ Đã cập nhật display_name thành 'phuc huynh'<br>";
    echo "Vui lòng đăng xuất và đăng nhập lại để thấy thay đổi.";
    
} catch (\PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
