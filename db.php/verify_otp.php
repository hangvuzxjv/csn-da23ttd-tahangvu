<?php
// verify_otp.php - Xác thực OTP và đặt lại mật khẩu
include 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$otp = $data['otp'] ?? '';
$newPassword = $data['new_password'] ?? '';

if (empty($phone) || empty($otp) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin.']);
    exit;
}

// Validate mật khẩu
if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.']);
    exit;
}

try {
    // 1. Kiểm tra OTP
    $stmt = $pdo->prepare("
        SELECT id, phone 
        FROM otp_codes 
        WHERE phone = ? 
        AND otp = ? 
        AND purpose = 'reset_password'
        AND used = FALSE 
        AND expires_at > NOW()
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$phone, $otp]);
    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mã OTP không đúng hoặc đã hết hạn.']);
        exit;
    }

    // 2. Tìm user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản.']);
        exit;
    }

    // 3. Cập nhật mật khẩu
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    // 4. Đánh dấu OTP đã sử dụng
    $stmt = $pdo->prepare("UPDATE otp_codes SET used = TRUE WHERE id = ?");
    $stmt->execute([$otpRecord['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay bây giờ.'
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
    error_log("Database error: " . $e->getMessage());
}
?>
