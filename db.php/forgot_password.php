<?php
// forgot_password.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Dùng cho trường hợp có lỗi

// Thay đổi đường dẫn đến PHPMailer nếu cần
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập Email.']);
    exit;
}

try {
    // 1. Kiểm tra Email có tồn tại
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Tạo Token và Thời hạn
        $token = bin2hex(random_bytes(50)); // Tạo token ngẫu nhiên
        $expiry = date('Y-m-d H:i:s', time() + 3600); // Hết hạn sau 1 giờ

        // 3. Lưu Token vào DB
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);

        // BƯỚC 4: Gửi Email
            $mail = new PHPMailer(true);

            // Cấu hình SMTP
            $mail->isSMTP(); // Thiết lập để sử dụng SMTP
            $mail->Host       = 'smtp.gmail.com'; // Server SMTP của Gmail
            $mail->SMTPAuth   = true; // Bật xác thực SMTP
            $mail->Username   = 'YOUR_GMAIL_ADDRESS'; // Thay bằng ĐỊA CHỈ EMAIL GMAIL của bạn (ví dụ: myemail@gmail.com)
            $mail->Password   = 'YOUR_APP_PASSWORD'; // Thay bằng MẬT KHẨU ỨNG DỤNG (16 ký tự) đã tạo ở trên
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Dùng SSL (hoặc TLS nếu dùng port 587)
            $mail->Port       = 465; // Port chuẩn cho SSL

            // Cấu hình Nội dung Email
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('no-reply@yourdomain.com', 'Hệ thống Quản lý Người dùng');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Yeu cau Dat lai Mat khau';
            $resetLink = "http://localhost/my_project/reset_password_form.html?token=" . $token; 
            $mail->Body    = "Bạn nhận được email này vì đã yêu cầu đặt lại mật khẩu. Nhấn vào liên kết dưới đây để tiếp tục: <a href='{$resetLink}'>Đặt lại Mật khẩu</a>. Liên kết sẽ hết hạn sau 1 giờ.";

            $mail->send();
    }
    
    // Gửi phản hồi thành công (kể cả khi email không tồn tại để bảo mật)
    echo json_encode(['success' => true, 'message' => 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Lỗi gửi Email. Mailer Error: {$mail->ErrorInfo}"]);
}
?>