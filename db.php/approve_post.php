<?php
// db.php/approve_post.php - Xử lý phê duyệt/từ chối bài viết
include 'db.php'; 
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$postId = $data['post_id'] ?? null;
$action = $data['action'] ?? ''; // 'approve' hoặc 'reject'
$adminNote = $data['admin_note'] ?? null;
$adminUsername = $data['admin_username'] ?? ''; // Lấy từ phiên đăng nhập Admin

if (!$postId || empty($action) || empty($adminUsername)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết.']);
    exit;
}

try {
    // 1. KIỂM TRA QUYỀN ADMIN (Rất quan trọng)
    $stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    $user = $stmt->fetch();

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.']);
        exit;
    }
    
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