<?php
// check_session.php - API để frontend kiểm tra trạng thái đăng nhập
include 'session_manager.php';
include 'db.php';

header('Content-Type: application/json');

// Kiểm tra session timeout
checkSessionTimeout();

if (isLoggedIn()) {
    $user = getCurrentUser();
    
    // Lấy thông tin đầy đủ từ database
    try {
        $stmt = $pdo->prepare("SELECT username, email, display_name, phone, avatar FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy số lượng bài viết
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE author_username = ?");
        $stmt->execute([$user['username']]);
        $postCount = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'isLoggedIn' => true,
            'username' => $userInfo['username'],
            'display_name' => $userInfo['display_name'] ?: $userInfo['username'],
            'email' => $userInfo['email'],
            'phone' => $userInfo['phone'],
            'avatar' => $userInfo['avatar'],
            'role' => $user['role'],
            'postCount' => $postCount,
            'csrfToken' => generateCSRFToken()
        ]);
    } catch (\PDOException $e) {
        echo json_encode([
            'success' => true,
            'isLoggedIn' => true,
            'username' => $user['username'],
            'display_name' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'postCount' => 0,
            'csrfToken' => generateCSRFToken()
        ]);
    }
} else {
    echo json_encode([
        'success' => true,
        'isLoggedIn' => false
    ]);
}
?>
