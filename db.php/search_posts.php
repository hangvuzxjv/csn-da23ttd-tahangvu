<?php
// search_posts.php - API tìm kiếm bài viết
include 'db.php';
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$province = $_GET['province'] ?? '';

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập từ khóa tìm kiếm.']);
    exit;
}

try {
    // Tìm kiếm trong title và content, chỉ lấy bài đã duyệt
    $sql = "SELECT id, author_username, title, content, category, created_at, image_url 
            FROM posts 
            WHERE status = 'approved' 
            AND (title LIKE ? OR content LIKE ?)
            ORDER BY created_at DESC
            LIMIT 50";
    
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'query' => $query,
        'count' => count($posts)
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server: Không thể tìm kiếm.']);
}
?>
