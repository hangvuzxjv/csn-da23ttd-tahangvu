<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không có file được tải lên']);
    exit;
}

$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
    exit;
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Kích thước file không được vượt quá 5MB']);
    exit;
}

// Create uploads directory if not exists
$upload_dir = __DIR__ . '/uploads/avatars/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Đường dẫn lưu vào database (relative path)
$db_filepath = 'uploads/avatars/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file']);
    exit;
}

// Delete old avatar if exists
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $user['avatar']) {
    // Xây dựng đường dẫn đầy đủ từ relative path
    $old_avatar_path = __DIR__ . '/' . $user['avatar'];
    if (file_exists($old_avatar_path)) {
        unlink($old_avatar_path);
    }
}

// Update database với đường dẫn relative
$stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
$stmt->bind_param("si", $db_filepath, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Cập nhật avatar thành công',
        'avatar_url' => $db_filepath
    ]);
} else {
    // Delete uploaded file if database update fails
    unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật database']);
}

$stmt->close();
$conn->close();
?>
