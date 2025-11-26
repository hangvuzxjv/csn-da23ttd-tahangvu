<?php
// logout.php - Đăng xuất an toàn
include 'session_manager.php';
header('Content-Type: application/json');

logoutUser();

echo json_encode([
    'success' => true,
    'message' => 'Đăng xuất thành công!'
]);
?>
