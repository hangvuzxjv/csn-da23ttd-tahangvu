<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

// register.php
require 'db.php';

// Các biến toàn cục
// Biến lưu dữ liệu từ người dùng
// $<tên biến> = $_<phương thức POST/ GET>["<thuộc tính name trong html>"];
// Phải sửa lại theo file HTML của VŨ
$input_useremail = $_POST["reg-email"];
$input_username = $_POST["reg-username"];
$input_userpasswd = $_POST["reg-password"];
$input_userpasswd_comfirm = $_POST["reg-confirm-password"];

// Tạo kết nối đến CSDL
$db_connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 

// Kiểm tra kết nối
$db_connect->connect_error ? consolePrint("CSDL: Lỗi kết nối!") : consolePrint("CSDL: Kết nối thành công!");

// SQL query
// Sửa biến lại
// Mật khẩu phải mã hóa md5() hoặc password_hash()
$db_sql = "INSERT INTO TAI_KHOAN(TEN_TAI_KHOAN, MAIL_TAI_KHOAN, MAT_KHAU_TAI_KHOAN) VALUES ('" . $input_username . "', '" . $input_useremail . "', '" . md5($input_userpasswd) . "')";

// Tạo truy vấn. Nếu lỗi, dừng chương trình.
$stmt = $db_connect->prepare($db_sql);
if (!$stmt) {
    consolePrint("Lỗi chuẩn bị truy vấn");
    return false;
}

// Thực hiện truy vấn. Nếu có lỗi, báo lỗi.
$stmt->execute();
if (!$stmt) {
    die('Prepare error: ' . $db_connect->error);
}

// Đóng truy vấn, dừng chương trình.
$stmt->close();
return false;
?>