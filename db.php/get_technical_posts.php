<?php
// get_technical_posts.php - Lấy bài viết kỹ thuật với filter
include 'db.php';
header('Content-Type: application/json');

$species = $_GET['species'] ?? '';
$stage = $_GET['stage'] ?? '';
$limit = $_GET['limit'] ?? 20;

try {
    $sql = "SELECT id, author_username, title, content, category, created_at, image_url 
            FROM posts 
            WHERE status = 'approved' 
            AND category = 'kinh-nghiem'";
    
    $params = [];
    
    // Filter theo loài
    if (!empty($species)) {
        $sql .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%$species%";
        $params[] = "%$species%";
    }
    
    // Filter theo giai đoạn
    if (!empty($stage)) {
        $sql .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%$stage%";
        $params[] = "%$stage%";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    $params[] = (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'count' => count($posts)
    ]);
    
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
}
?>
