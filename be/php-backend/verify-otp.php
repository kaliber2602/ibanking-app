<?php
header('Content-Type: application/json');

// Nhận dữ liệu từ client
$data = json_decode(file_get_contents("php://input"), true);

$email   = $data["email"]   ?? null;
$otp     = $data["otp"]     ?? null;
$token   = $data["token"]   ?? null;
$expires = $data["expires"] ?? 0;

if (!$email || !$otp || !$token || !$expires) {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin xác thực"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Email không hợp lệ"]);
    exit;
}
// Kiểm tra thời gian hết hạn
if (time() > $expires) {
    echo json_encode(["success" => false, "message" => "OTP đã hết hạn"]);
    exit;
}

// Tính lại token để xác minh
$secret = "your-secret-key"; // phải giống với bên otp.php
$expectedToken = hash_hmac("sha256", $email . $otp . $expires, $secret);

// So sánh token
if (hash_equals($expectedToken, $token)) {
    echo json_encode(["success" => true, "message" => "Xác thực OTP thành công"]);
} else {
    echo json_encode(["success" => false, "message" => "OTP không đúng"]);
}
