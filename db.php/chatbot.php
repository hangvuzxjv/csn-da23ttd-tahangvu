<?php
// chatbot.php - AI Chatbot cho kỹ thuật thủy sản
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($data['message'] ?? ''));

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Tin nhắn trống.']);
    exit;
}

// Knowledge base - Cơ sở tri thức về thủy sản
$responses = [
    // Về tôm
    'tôm' => 'Nuôi tôm cần chú ý: 1) Chất lượng nước (pH 7.5-8.5, độ mặn 15-25‰), 2) Thức ăn chất lượng cao, 3) Quản lý mật độ thả nuôi phù hợp.',
    'tôm thẻ' => 'Tôm thẻ chân trắng thích hợp nuôi ở độ mặn 0.5-35‰, nhiệt độ 26-30°C. Chu kỳ nuôi 90-120 ngày.',
    'tôm sú' => 'Tôm sú cần môi trường nước mặn, pH 7.8-8.7, nhiệt độ 28-32°C. Cần quản lý chặt chẽ để tránh bệnh.',
    
    // Về cá
    'cá' => 'Nuôi cá cần: 1) Nguồn nước sạch, 2) Thức ăn đầy đủ dinh dưỡng, 3) Theo dõi sức khỏe thường xuyên.',
    'cá tra' => 'Cá tra nuôi trong nước ngọt, nhiệt độ 26-30°C, pH 6.5-8.5. Thức ăn chính là cám viên công nghiệp.',
    'cá basa' => 'Cá basa tương tự cá tra, nhưng tăng trưởng nhanh hơn. Cần quản lý chất lượng nước tốt.',
    
    // Về bệnh
    'bệnh' => 'Các bệnh thường gặp: 1) Bệnh đốm trắng (tôm), 2) Bệnh gan tụy cấp (cá), 3) Bệnh do vi khuẩn. Cần phòng ngừa bằng cách quản lý môi trường.',
    'bệnh tôm' => 'Bệnh đốm trắng là nguy hiểm nhất. Triệu chứng: tôm bơi lờ đờ, vỏ mềm, đốm trắng trên mai. Cần cách ly ngay.',
    'bệnh cá' => 'Bệnh thường gặp: xuất huyết, thối vây, nấm. Điều trị bằng thuốc kháng sinh theo chỉ dẫn bác sĩ thú y.',
    
    // Về thức ăn
    'thức ăn' => 'Thức ăn cần đủ protein (25-40%), lipid (6-12%), vitamin và khoáng chất. Cho ăn 3-4 lần/ngày.',
    'protein' => 'Hàm lượng protein: Tôm giống 35-40%, tôm thương phẩm 25-30%, Cá 28-35% tùy loài.',
    
    // Về nước
    'nước' => 'Chất lượng nước quan trọng nhất: pH, DO (oxy hòa tan), ammonia, nitrite, nhiệt độ, độ mặn.',
    'ph' => 'pH lý tưởng: Tôm 7.5-8.5, Cá nước ngọt 6.5-8.5, Cá nước mặn 7.8-8.5.',
    'oxy' => 'Oxy hòa tan (DO) cần > 4mg/L. Dưới 2mg/L sẽ gây chết hàng loạt.',
    'ammonia' => 'Ammonia độc hại, cần < 0.1mg/L. Cao hơn sẽ gây stress và chết.',
    
    // Về ao nuôi
    'ao' => 'Ao nuôi cần: 1) Vị trí thoáng, 2) Đáy bằng phẳng, 3) Hệ thống cấp thoát nước tốt, 4) Diện tích phù hợp.',
    'chuẩn bị ao' => 'Chuẩn bị ao: 1) Phơi đáy 7-10 ngày, 2) Bón vôi 1-2 tấn/ha, 3) Bón phân hữu cơ, 4) Ngâm nước và ủ 5-7 ngày.',
    
    // Về giống
    'giống' => 'Chọn giống khỏe mạnh, kích thước đồng đều, không dị tật. Nguồn gốc rõ ràng từ trại giống uy tín.',
    'thả giống' => 'Thả giống vào buổi sáng sớm hoặc chiều mát. Cần thuần nước 15-20 phút trước khi thả.',
    
    // Về thu hoạch
    'thu hoạch' => 'Thu hoạch khi đạt kích thước thương phẩm. Nên thu vào buổi sáng sớm, tránh stress cho thủy sản.',
    
    // Về giá
    'giá' => 'Giá thủy sản biến động theo mùa vụ và thị trường. Xem trang Giá Thủy Sản để cập nhật giá hàng ngày.',
    
    // Câu hỏi chung
    'xin chào' => 'Xin chào! Tôi có thể giúp bạn về kỹ thuật nuôi trồng thủy sản. Bạn muốn hỏi về gì?',
    'cảm ơn' => 'Rất vui được giúp đỡ! Nếu có câu hỏi khác, cứ hỏi tôi nhé.',
    'help' => 'Tôi có thể tư vấn về: nuôi tôm, nuôi cá, bệnh thủy sản, chất lượng nước, thức ăn, ao nuôi, giống, thu hoạch.',
];

// Tìm câu trả lời phù hợp
$response = null;

foreach ($responses as $keyword => $answer) {
    if (strpos($message, $keyword) !== false) {
        $response = $answer;
        break;
    }
}

// Nếu không tìm thấy, trả về câu trả lời mặc định
if (!$response) {
    $response = 'Xin lỗi, tôi chưa có thông tin về câu hỏi này. Bạn có thể hỏi về: nuôi tôm, nuôi cá, bệnh thủy sản, chất lượng nước, thức ăn, ao nuôi. Hoặc tìm kiếm bài viết trên website.';
}

echo json_encode([
    'success' => true,
    'response' => $response
]);
?>
