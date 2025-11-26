<?php
// send_sms.php - Helper functions để gửi SMS

require_once 'sms_config.php';

/**
 * Gửi SMS qua ESMS.vn
 */
function sendSMS_ESMS($phone, $message) {
    $url = 'http://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/';
    
    $data = [
        'ApiKey' => ESMS_API_KEY,
        'SecretKey' => ESMS_SECRET_KEY,
        'Phone' => $phone,
        'Content' => $message,
        'Brandname' => ESMS_BRANDNAME,
        'SmsType' => 2  // 2 = SMS Brandname, 4 = SMS Quảng cáo
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if (SMS_DEBUG) {
        error_log("ESMS Response: " . $response);
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode == 200 && isset($result['CodeResult']) && $result['CodeResult'] == 100) {
        return [
            'success' => true,
            'message' => 'SMS đã được gửi thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Lỗi gửi SMS: ' . ($result['ErrorMessage'] ?? 'Unknown error')
        ];
    }
}

/**
 * Gửi SMS qua Twilio
 */
function sendSMS_Twilio($phone, $message) {
    require_once '../vendor/autoload.php';
    
    use Twilio\Rest\Client;
    
    try {
        $client = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
        
        $client->messages->create(
            $phone,
            [
                'from' => TWILIO_PHONE_NUMBER,
                'body' => $message
            ]
        );
        
        return [
            'success' => true,
            'message' => 'SMS đã được gửi thành công!'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Lỗi gửi SMS: ' . $e->getMessage()
        ];
    }
}

/**
 * Gửi SMS qua SpeedSMS
 */
function sendSMS_SpeedSMS($phone, $message) {
    $url = 'https://api.speedsms.vn/index.php/sms/send';
    
    $data = [
        'to' => [$phone],
        'content' => $message,
        'sms_type' => 2,  // 2 = Brandname
        'sender' => ESMS_BRANDNAME
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . SPEEDSMS_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['status']) && $result['status'] == 'success') {
        return [
            'success' => true,
            'message' => 'SMS đã được gửi thành công!'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Lỗi gửi SMS: ' . ($result['message'] ?? 'Unknown error')
        ];
    }
}

/**
 * Gửi SMS (tự động chọn provider)
 */
function sendSMS($phone, $message) {
    // Chuẩn hóa số điện thoại
    $phone = normalizePhoneNumber($phone);
    
    if (!$phone) {
        return [
            'success' => false,
            'message' => 'Số điện thoại không hợp lệ'
        ];
    }
    
    // Chọn provider
    switch (SMS_PROVIDER) {
        case 'esms':
            return sendSMS_ESMS($phone, $message);
        case 'twilio':
            return sendSMS_Twilio($phone, $message);
        case 'speedsms':
            return sendSMS_SpeedSMS($phone, $message);
        default:
            return [
                'success' => false,
                'message' => 'SMS provider không được cấu hình'
            ];
    }
}

/**
 * Chuẩn hóa số điện thoại Việt Nam
 */
function normalizePhoneNumber($phone) {
    // Loại bỏ khoảng trắng, dấu gạch ngang
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    
    // Chuyển đổi số điện thoại Việt Nam
    if (preg_match('/^0(\d{9})$/', $phone, $matches)) {
        // 0912345678 -> 84912345678
        return '84' . $matches[1];
    } elseif (preg_match('/^\+?84(\d{9})$/', $phone, $matches)) {
        // +84912345678 hoặc 84912345678
        return '84' . $matches[1];
    }
    
    return false;
}

/**
 * Tạo mã OTP ngẫu nhiên
 */
function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Gửi OTP qua SMS
 */
function sendOTP($phone, $otp) {
    $message = str_replace(
        ['{otp}', '{minutes}', '{site_name}'],
        [$otp, OTP_EXPIRY_MINUTES, SITE_NAME],
        OTP_TEMPLATE
    );
    
    return sendSMS($phone, $message);
}

/**
 * Gửi SMS reset password
 */
function sendPasswordResetSMS($phone, $otp) {
    $message = "Ma xac thuc dat lai mat khau cua ban la: $otp. Ma co hieu luc trong " . OTP_EXPIRY_MINUTES . " phut. " . SITE_NAME;
    
    return sendSMS($phone, $message);
}
?>
