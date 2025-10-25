<?php
header('Content-Type: application/json');
require_once '../db/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if (!$username) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu username.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT email FROM Customer WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['email'])) {
        echo json_encode([
            'success' => true,
            'email' => $result['email']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy email của người dùng.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
