<?php
// forgot_password.php - Xử lý yêu cầu quên mật khẩu (Tạo Token)
include 'db.php'; 

header('Content-Type: application/json');

// Đọc dữ liệu JSON
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập Email.']);
    exit;
}

try {
    // 1. Kiểm tra Email tồn tại
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Tránh tiết lộ email tồn tại, nhưng vẫn trả về thông báo thành công
        // Đây là một biện pháp bảo mật tốt
        echo json_encode(['success' => true, 'message' => 'Nếu email của bạn tồn tại trong hệ thống, một liên kết đặt lại mật khẩu đã được gửi đi.']);
        exit;
    }

    // 2. Tạo Token Bảo mật và Thời gian hết hạn
    $token = bin2hex(random_bytes(50)); // Tạo token ngẫu nhiên
    $expires = date("Y-m-d H:i:s", time() + 3600); // Token hết hạn sau 1 giờ

    // 3. Lưu Token vào CSDL
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
    $stmt->execute([$token, $expires, $user['id']]);

    // 4. Gửi Email cho người dùng
    require_once 'send_email.php';
    
    $emailResult = sendPasswordResetEmail($email, $user['username'], $token);
    
    if ($emailResult['success']) {
        // Email gửi thành công
        echo json_encode([
            'success' => true, 
            'message' => 'Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.'
        ]);
    } else {
        // Lỗi gửi email nhưng vẫn log link để test
        error_log("Link đặt lại mật khẩu cho " . $user['username'] . ": " . SITE_URL . "/reset_password.html?token=" . $token);
        error_log("Lỗi gửi email: " . $emailResult['message']);
        
        // Vẫn trả về success để không tiết lộ lỗi
        echo json_encode([
            'success' => true, 
            'message' => 'Nếu email của bạn tồn tại, một liên kết đặt lại mật khẩu đã được gửi đến hộp thư của bạn.'
        ]);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>