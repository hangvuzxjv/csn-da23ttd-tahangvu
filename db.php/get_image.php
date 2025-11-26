<?php
// get_image.php - Serve uploaded images securely
// Bảo mật: Chỉ cho phép đọc file trong thư mục uploads

$file = $_GET['file'] ?? '';

// Validate input
if (empty($file)) {
    http_response_code(400);
    die('Missing file parameter');
}

// Xử lý đường dẫn file
$file = str_replace('\\', '/', $file);

// Prevent directory traversal attack
if (strpos($file, '..') !== false) {
    http_response_code(400);
    die('Invalid file name');
}

// Xử lý các trường hợp đường dẫn khác nhau
// Case 1: uploads/avatars/file.jpg
// Case 2: db.php/uploads/avatars/file.jpg
// Case 3: avatars/file.jpg
// Case 4: file.jpg

// Loại bỏ prefix db.php/ nếu có
$file = preg_replace('#^db\.php/#', '', $file);

// Loại bỏ prefix uploads/ nếu có
$file = preg_replace('#^uploads/#', '', $file);

// Get absolute path to uploads directory
$uploads_dir = __DIR__ . '/uploads';

if (!is_dir($uploads_dir)) {
    // Thử tìm ở thư mục cha
    $uploads_dir = dirname(__DIR__) . '/uploads';
}

if (!is_dir($uploads_dir)) {
    http_response_code(500);
    die('Uploads directory not found: ' . $uploads_dir);
}

// Build full file path - giữ nguyên cấu trúc thư mục con (avatars/)
$filepath = $uploads_dir . '/' . $file;

// Check if file exists
if (!file_exists($filepath) || !is_file($filepath)) {
    // Fallback: Trả về ảnh mặc định thay vì 404
    $default_image = realpath(__DIR__ . '/../img/1.jpg');
    if ($default_image && file_exists($default_image)) {
        $filepath = $default_image;
    } else {
        http_response_code(404);
        die('File not found');
    }
}

// Validate file type (only images)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$file_extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    http_response_code(403);
    die('Invalid file type');
}

// Get MIME type
$mime = mime_content_type($filepath);

// Set headers
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output file
readfile($filepath);
exit;
?>
