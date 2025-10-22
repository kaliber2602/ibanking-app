<?php
// Cấu hình thông tin kết nối
$host = 'localhost';
$dbname = 'ibanking';
$username = 'root';
$password = ''; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>
