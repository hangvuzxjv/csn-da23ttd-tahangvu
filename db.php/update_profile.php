<?php
// update_profile.php - Cập nhật thông tin profile (avatar + tên hiển thị)
include 'db.php';
include 'session_manager.php';
header('Content-Type: application/json');

// Yêu cầu đăng nhập
requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Lấy dữ liệu từ POST
$displayName = $_POST['display_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$avatarFile = $_FILES['avatar'] ?? null;

// Validate
if (empty($displayName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên hiển thị.']);
    exit;
}

// Validate phone (nếu có)
if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ (10-11 số).']);
    exit;
}

$avatarPath = null;

// Xử lý upload avatar
if ($avatarFile && $avatarFile['error'] == UPLOAD_ERR_OK) {
    $uploadDir = realpath(__DIR__ . '/../uploads/avatars/');
    
    // Tạo thư mục nếu chưa có
    if (!$uploadDir) {
        mkdir(__DIR__ . '/../uploads/avatars/', 0755, true);
        $uploadDir = realpath(__DIR__ . '/../uploads/avatars/');
    }
    
    $fileExt = strtolower(pathinfo($avatarFile['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExt, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Chỉ cho phép file ảnh (JPG, PNG, GIF).']);
        exit;
    }
    
    $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . '/' . $newFileName;
    
    if (move_uploaded_file($avatarFile['tmp_name'], $targetPath)) {
        $avatarPath = 'avatars/' . $newFileName;
    }
}

try {
    // Cập nhật display_name, phone và avatar (nếu có)
    if ($avatarPath) {
        $stmt = $pdo->prepare("UPDATE users SET display_name = ?, phone = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$displayName, $phone, $avatarPath, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET display_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$displayName, $phone, $userId]);
    }

    // Cập nhật session (hiển thị display_name thay vì username)
    $_SESSION['display_name'] = $displayName;

    echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>
