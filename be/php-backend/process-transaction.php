<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db.php';
require_once __DIR__ . '/../utils/sendEmail.php';

$data = json_decode(file_get_contents('php://input'), true);
$username    = trim($data['username'] ?? '');
$payment_id  = intval($data['payment_id'] ?? 0);
$student_id  = trim($data['student_id'] ?? '');
$amount      = isset($data['amount']) ? floatval($data['amount']) : null;
$note        = trim($data['note'] ?? '');

if (!$username || (!$payment_id && !$student_id) || $amount === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin bắt buộc: username, payment_id hoặc student_id, amount'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn->beginTransaction();

    // Lấy thông tin người nộp tiền
    $stmt = $conn->prepare('SELECT balance, email FROM Customer WHERE username = :username FOR UPDATE');
    $stmt->execute(['username' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người nộp tiền.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $currentBalance = floatval($customer['balance']);
    $payerEmail     = $customer['email'] ?? '';

    // Lấy thông tin học phí cần thanh toán
    if (!$payment_id && $student_id) {
        $stmt = $conn->prepare('SELECT payment_id, amount, status FROM Payment WHERE student_id = :student_id AND status = "unpaid" FOR UPDATE');
        $stmt->execute(['student_id' => $student_id]);
    } else {
        $stmt = $conn->prepare('SELECT payment_id, amount, status FROM Payment WHERE payment_id = :payment_id FOR UPDATE');
        $stmt->execute(['payment_id' => $payment_id]);
    }

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin học phí cần thanh toán.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!$payment_id) {
        $payment_id = intval($payment['payment_id']);
    }

    if ($payment['status'] !== 'unpaid') {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Khoản học phí đã được thanh toán hoặc không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $expectedAmount = floatval($payment['amount']);
    if (abs($expectedAmount - $amount) > 0.01) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Số tiền thanh toán không khớp.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($currentBalance < $amount) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Số dư không đủ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Trừ tiền
    $stmt = $conn->prepare('UPDATE Customer SET balance = balance - :amount WHERE username = :username');
    $stmt->execute(['amount' => $amount, 'username' => $username]);

    // Đánh dấu đã thanh toán
    $stmt = $conn->prepare('UPDATE Payment SET status = "paid" WHERE payment_id = :payment_id');
    $stmt->execute(['payment_id' => $payment_id]);

    // Ghi lịch sử giao dịch
    $stmt = $conn->prepare('INSERT INTO Transaction (username, payment_id, amount, status) VALUES (:username, :payment_id, :amount, :status)');
    $stmt->execute([
        'username'    => $username,
        'payment_id'  => $payment_id,
        'amount'      => $amount,
        'status'      => 'success'
    ]);

    $transactionId = $conn->lastInsertId();
    $conn->commit();

    // Gửi email xác nhận
    $sent = false;
    if ($payerEmail) {
        $subject = "Xác nhận thanh toán học phí";
        $message = "Giao dịch thanh toán học phí cho sinh viên ID $student_id đã được xử lý thành công.\n"
                 . "Số tiền: $amount VND\n"
                 . "Ghi chú: $note\n"
                 . "Mã giao dịch: $transactionId";

        $sent = sendEmail($payerEmail, $subject, $message);
        if (!$sent) {
            error_log("Gửi email thất bại tới $payerEmail");
        }
    }

    // Trả về kết quả
    $stmt = $conn->prepare('SELECT balance FROM Customer WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $newBalRow = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'        => true,
        'message'        => 'Thanh toán thành công.',
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Lỗi hệ thống: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}