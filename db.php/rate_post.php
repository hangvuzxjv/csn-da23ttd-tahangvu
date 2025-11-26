<?php
// rate_post.php - Đánh giá bài viết
include 'db.php';
include 'session_manager.php';
header('Content-Type: application/json');

requireLogin();

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? null;
$rating = $data['rating'] ?? 0;

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

if (!$postId || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

try {
    // Insert hoặc update rating
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, post_id, rating) VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE rating = ?");
    $stmt->execute([$userId, $postId, $rating, $rating]);
    
    // Cập nhật tổng điểm trong bảng posts
    $stmt = $pdo->prepare("UPDATE posts SET 
                           rating_total = (SELECT SUM(rating) FROM ratings WHERE post_id = ?),
                           rating_count = (SELECT COUNT(*) FROM ratings WHERE post_id = ?)
                           WHERE id = ?");
    $stmt->execute([$postId, $postId, $postId]);
    
    echo json_encode(['success' => true, 'message' => '⭐ Cảm ơn bạn đã đánh giá!']);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
}
?>
