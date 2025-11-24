<?php
// reset_password.php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$newPassword = $data['new_password'] ?? '';

if (empty($token) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu token hoặc mật khẩu mới.']);
    exit;
}

try {
    // 1. Kiểm tra Token và Thời hạn
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
        exit;
    }

    // 2. Mã hóa mật khẩu mới
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 3. Cập nhật mật khẩu và xóa token
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
    $stmt->execute([$passwordHash, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công!']);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>