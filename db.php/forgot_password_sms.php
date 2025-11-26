<?php
// forgot_password_sms.php - Quên mật khẩu qua SMS
include 'db.php';
require_once 'send_sms.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';

if (empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số điện thoại.']);
    exit;
}

// Chuẩn hóa số điện thoại
$normalizedPhone = normalizePhoneNumber($phone);
if (!$normalizedPhone) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ.']);
    exit;
}

try {
    // 1. Kiểm tra số điện thoại tồn tại
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        // Không tiết lộ số điện thoại có tồn tại hay không
        echo json_encode([
            'success' => true, 
            'message' => 'Nếu số điện thoại của bạn tồn tại trong hệ thống, mã OTP đã được gửi.'
        ]);
        exit;
    }

    // 2. Tạo mã OTP
    $otp = generateOTP(6);  // Mã 6 số
    $expiresAt = date("Y-m-d H:i:s", time() + (OTP_EXPIRY_MINUTES * 60));

    // 3. Lưu OTP vào database
    $stmt = $pdo->prepare("INSERT INTO otp_codes (phone, otp, purpose, expires_at) VALUES (?, ?, 'reset_password', ?)");
    $stmt->execute([$phone, $otp, $expiresAt]);

    // 4. Gửi SMS
    $smsResult = sendPasswordResetSMS($normalizedPhone, $otp);

    if ($smsResult['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến số điện thoại của bạn. Vui lòng kiểm tra tin nhắn.'
        ]);
    } else {
        // Log lỗi nhưng vẫn trả về success
        error_log("Lỗi gửi SMS: " . $smsResult['message']);
        error_log("OTP cho số $phone: $otp");
        
        echo json_encode([
            'success' => true,
            'message' => 'Nếu số điện thoại của bạn tồn tại, mã OTP đã được gửi.'
        ]);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
    error_log("Database error: " . $e->getMessage());
}
?>
