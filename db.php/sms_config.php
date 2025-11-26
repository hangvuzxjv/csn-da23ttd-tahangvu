<?php
// sms_config.php - Cấu hình SMS

// Chọn provider: 'esms', 'twilio', 'speedsms'
define('SMS_PROVIDER', 'esms');

// ===== ESMS.VN Configuration =====
define('ESMS_API_KEY', 'your-api-key');        // API Key từ eSMS
define('ESMS_SECRET_KEY', 'your-secret-key');  // Secret Key từ eSMS
define('ESMS_BRANDNAME', 'THUYSANTV');         // Tên thương hiệu (max 11 ký tự)

// ===== Twilio Configuration =====
define('TWILIO_ACCOUNT_SID', 'your-account-sid');
define('TWILIO_AUTH_TOKEN', 'your-auth-token');
define('TWILIO_PHONE_NUMBER', '+1234567890');  // Số điện thoại Twilio

// ===== SpeedSMS Configuration =====
define('SPEEDSMS_ACCESS_TOKEN', 'your-access-token');

// ===== General Settings =====
define('SITE_NAME', 'Thủy Sản Trà Vinh');
define('OTP_EXPIRY_MINUTES', 5);  // OTP hết hạn sau 5 phút
define('SMS_DEBUG', true);         // true = log debug, false = tắt

// Template SMS
define('OTP_TEMPLATE', 'Ma xac thuc cua ban la: {otp}. Ma co hieu luc trong {minutes} phut. {site_name}');
?>
