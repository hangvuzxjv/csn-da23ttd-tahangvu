<?php
// get_price_chart.php - Lấy dữ liệu giá thủy sản
include 'db.php';
header('Content-Type: application/json');

$species = $_GET['species'] ?? 'Tôm Thẻ';
$days = $_GET['days'] ?? 30;

try {
    $stmt = $pdo->prepare("SELECT species, price, location, recorded_at 
                           FROM price_tracking 
                           WHERE species = ? 
                           AND recorded_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                           ORDER BY recorded_at ASC");
    $stmt->execute([$species, $days]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'species' => $species
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi Server.']);
}
?>
