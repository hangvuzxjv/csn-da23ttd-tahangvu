<?php
// db.php/approve_post.php - Xử lý phê duyệt/từ chối bài viết
include 'db.php';
include 'session_manager.php';
header('Content-Type: application/json');

// Yêu cầu quyền admin
requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);

$postId = $data['post_id'] ?? null;
$action = $data['action'] ?? ''; // 'approve' hoặc 'reject'
$adminNote = $data['admin_note'] ?? null;

// Lấy username từ session (BẢO MẬT)
$currentUser = getCurrentUser();
$adminUsername = $currentUser['username'];

if (!$postId || empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết.']);
    exit;
}

try {
    
    $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
    $message = ($action === 'approve') ? 'Bài viết đã được phê duyệt.' : 'Bài viết đã bị từ chối.';

    // 2. CẬP NHẬT TRẠNG THÁI VÀ GHI CHÚ CỦA ADMIN
    $stmt = $pdo->prepare("
        UPDATE posts 
        SET status = ?, admin_note = ?, approved_by_admin = ? 
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $adminNote, $adminUsername, $postId]);

    echo json_encode(['success' => true, 'message' => $message]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>