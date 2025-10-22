<?php
// Đảm bảo phản hồi là JSON
header('Content-Type: application/json');

// Load hàm gửi email
require_once __DIR__ . '/../utils/sendEmail.php';

// Đọc dữ liệu JSON từ yêu cầu POST
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Kiểm tra email đầu vào
$email = $data["email"] ?? null;
if (!$email) {
    echo json_encode(["success" => false, "message" => "Thiếu email"]);
    exit;
}

// Tạo OTP và token
$otp = rand(100000, 999999);
$expires = time() + 300;
$secret = "your-secret-key";
$token = hash_hmac("sha256", $email . $otp . $expires, $secret);

// Soạn nội dung email
$subject = "Mã OTP xác thực";
$message = "Mã OTP của bạn là: $otp\nHết hạn sau 5 phút.";

// Gửi email
$sent = sendEmail($email, $subject, $message);

// Phản hồi kết quả
if (!$sent) {
    echo json_encode(["success" => false, "message" => "Gửi email thất bại"]);
    exit;
}

echo json_encode([
    "success" => true,
    "token" => $token,
    "expires" => $expires
]);
