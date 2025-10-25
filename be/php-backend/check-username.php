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
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Customer WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode([
            'success' => true,
            'exists' => true,
            'message' => 'Username tồn tại.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'exists' => false,
            'message' => 'Username không tồn tại.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
