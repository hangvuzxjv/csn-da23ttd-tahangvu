<?php
// bookmark_post.php - Lưu/Bỏ lưu bài viết
include 'db.php';
include 'session_manager.php';
header('Content-Type: application/json');

requireLogin();

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? null;
$action = $data['action'] ?? 'add'; // 'add' hoặc 'remove'

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu ID bài viết.']);
    exit;
}

try {
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO bookmarks (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$userId, $postId]);
        echo json_encode(['success' => true, 'message' => '💾 Đã lưu bài viết!']);
    } else {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$userId, $postId]);
        echo json_encode(['success' => true, 'message' => '🗑️ Đã bỏ lưu bài viết!']);
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
}
?>
