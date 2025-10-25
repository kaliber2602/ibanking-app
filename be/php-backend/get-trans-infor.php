<?php
header('Content-Type: application/json');
require_once '../db/db.php';

// Nhận dữ liệu từ body (POST)
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$student_id = trim($data['student_id'] ?? '');

if (!$username || !$student_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu username hoặc mã số sinh viên.'
    ]);
    exit;
}

try {
    // Truy vấn thông tin người dùng
    $stmtUser = $conn->prepare("
        SELECT full_name, phone, email, balance
        FROM Customer
        WHERE username = :username
        LIMIT 1
    ");
    $stmtUser->execute(['username' => $username]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy người dùng.'
        ]);
        exit;
    }

    // Truy vấn thông tin sinh viên
    $stmtStudent = $conn->prepare("
        SELECT student_id, full_name, faculty, semester, amount
        FROM Payment
        WHERE student_id = :student_id
        LIMIT 1
    ");
    $stmtStudent->execute(['student_id' => $student_id]);
    $student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sinh viên với mã số đã nhập.'
        ]);
        exit;
    }

    // Trả về dữ liệu tổng hợp
    echo json_encode([
        'success' => true,
        'data' => [
            'user' => [
                'full_name' => $user['full_name'],
                'phone' => $user['phone'],
                'email' => $user['email'],
                'balance' => floatval($user['balance'])
            ],
            'student' => [
                'student_id' => $student['student_id'],
                'full_name' => $student['full_name'],
                'faculty' => $student['faculty'],
                'semester' => $student['semester'],
                'amount' => floatval($student['amount'])
            ]
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}