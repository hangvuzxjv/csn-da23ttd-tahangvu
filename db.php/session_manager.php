<?php
// session_manager.php - Quản lý session an toàn

// Cấu hình session an toàn
ini_set('session.cookie_httponly', 1); // Ngăn JavaScript truy cập cookie
ini_set('session.use_only_cookies', 1); // Chỉ dùng cookie, không dùng URL
ini_set('session.cookie_secure', 0); // Set = 1 nếu dùng HTTPS
ini_set('session.cookie_samesite', 'Strict'); // Chống CSRF

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Yêu cầu đăng nhập, nếu không thì trả về lỗi 401
 */
function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Bạn cần đăng nhập để thực hiện hành động này.',
            'requireLogin' => true
        ]);
        exit;
    }
}

/**
 * Yêu cầu quyền admin
 */
function requireAdmin() {
    requireLogin();
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Bạn không có quyền truy cập. Chỉ dành cho Admin.'
        ]);
        exit;
    }
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'user'
    ];
}

/**
 * Đăng nhập user
 */
function loginUser($userId, $username, $email, $role = 'user') {
    // Regenerate session ID để chống session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Đăng xuất user
 */
function logoutUser() {
    // Xóa tất cả session variables
    $_SESSION = array();
    
    // Xóa session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Hủy session
    session_destroy();
}

/**
 * Kiểm tra session timeout (30 phút không hoạt động)
 */
function checkSessionTimeout($timeout = 1800) {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            logoutUser();
            return false;
        }
        // Cập nhật thời gian hoạt động
        $_SESSION['login_time'] = time();
    }
    return true;
}

/**
 * Tạo CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Yêu cầu CSRF token hợp lệ
 */
function requireCSRFToken() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'CSRF token không hợp lệ.'
        ]);
        exit;
    }
}
?>
