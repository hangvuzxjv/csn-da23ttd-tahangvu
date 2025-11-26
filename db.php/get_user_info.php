<?php
// get_user_info.php - Lấy thông tin user đầy đủ
include 'db.php';
include 'session_manager.php';
header('Content-Type: application/json');

requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, display_name, phone, avatar FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'username' => $user['username'],
            'email' => $user['email'],
            'display_name' => $user['display_name'],
            'phone' => $user['phone'],
            'avatar' => $user['avatar']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User không tồn tại.']);
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
}
?>
