<?php
header('Content-Type: application/json');
require_once '../db/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';


if (!$username || !$password) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ thông tin.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT password FROM Customer WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy tài khoản.'
        ]);
    } elseif (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Sai mật khẩu.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Đăng nhập thành công!'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>