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
    $stmt = $conn->prepare("
        SELECT transaction_id, created_at, username, payment_id, amount, status
        FROM Transaction
        WHERE username = :username
        ORDER BY created_at DESC
    ");
    $stmt->execute(['username' => $username]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $transactions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
