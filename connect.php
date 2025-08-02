<?php
$host = "localhost";
$user = "root";
$pass = ""; // Đổi nếu bạn có mật khẩu MySQL
$db = "quanly_tintuc"; // Đúng tên database đã tạo trong setup.sql

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");
?>