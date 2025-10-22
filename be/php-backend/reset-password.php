<?php
header('Content-Type: application/json');
require_once '../db/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username     = trim($data['username'] ?? '');
$newPassword  = $data['newPassword'] ?? '';

if (!$username || !$newPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ thông tin.'
    ]);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'Mật khẩu phải tối thiểu 8 ký tự.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE Customer SET password = :password WHERE username = :username");
    $stmt->execute([
        'password' => $newPassword,
        'username' => $username
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể cập nhật mật khẩu hoặc tài khoản không tồn tại.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
