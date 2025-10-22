<?php
header('Content-Type: application/json');
require_once '../db/db.php';

$student_id = trim($_GET['id'] ?? '');

if (!$student_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu mã số sinh viên.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT student_id, full_name, faculty, semester, amount, status 
        FROM Payment
        WHERE student_id = :student_id
        LIMIT 1
    ");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sinh viên với mã số đã nhập.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'student_id' => $student['student_id'],
            'full_name' => $student['full_name'],
            'faculty' => $student['faculty'],
            'semester' => $student['semester'],
            'amount' => floatval($student['amount']),
            'status' => $student['status']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
