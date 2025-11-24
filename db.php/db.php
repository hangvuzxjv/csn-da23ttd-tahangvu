<?php
// Chứa thông tin kết nối tới CSDL
define('DB_HOST', 'localhost');
define('DB_NAME', 'lab1'); // Điền tên CSDL vào
define('DB_USER', 'root');
define('DB_PASS', '');

function consolePrint($string) {
    echo '<script>console.log(' . json_encode((string)$string) . ');</script>';
}
?>