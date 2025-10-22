<?php
header('Content-Type: application/json');
require_once '../db/db.php';

// Endpoint: process-transaction.php
// Mục đích: Xử lý thanh toán an toàn sau khi OTP đã được xác thực
// Bảo đảm: Không có giao dịch đồng thời thay đổi số dư của người nộp tiền (Customer)
// và ngăn chặn hai tài khoản cùng thanh toán cho cùng 1 MSSV bằng cách khoá hàng Payment.

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$payment_id = intval($data['payment_id'] ?? 0);
$student_id = trim($data['student_id'] ?? '');
$amount = isset($data['amount']) ? floatval($data['amount']) : null;
$note = trim($data['note'] ?? '');

// Kiểm tra đầu vào
if (!$username || (!$payment_id && !$student_id) || $amount === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin bắt buộc: username, payment_id hoặc student_id, amount'
    ]);
    exit;
}

try {
    $conn->beginTransaction();

    // Khóa hàng Customer
    $stmt = $conn->prepare('SELECT balance FROM Customer WHERE username = :username FOR UPDATE');
    $stmt->execute(['username' => $username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người nộp tiền (username).']);
        exit;
    }

    $currentBalance = floatval($customer['balance']);

    // Khóa hàng Payment
    if (!$payment_id && $student_id) {
        $stmt = $conn->prepare('SELECT payment_id, student_id, amount, status FROM Payment WHERE student_id = :student_id FOR UPDATE');
        $stmt->execute(['student_id' => $student_id]);
    } else {
        $stmt = $conn->prepare('SELECT payment_id, student_id, amount, status FROM Payment WHERE payment_id = :payment_id FOR UPDATE');
        $stmt->execute(['payment_id' => $payment_id]);
    }

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment && !$payment_id) {
        $payment_id = intval($payment['payment_id']);
    }

    if (!$payment) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin Payment.']);
        exit;
    }

    if ($payment['status'] !== 'unpaid') {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Học phí đã được thanh toán hoặc không ở trạng thái unpaid.']);
        exit;
    }

    $expectedAmount = floatval($payment['amount']);
    if (abs($expectedAmount - $amount) > 0.01) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Số tiền thanh toán không khớp với số tiền cần nộp.']);
        exit;
    }

    if ($currentBalance < $amount) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Số dư không đủ để thực hiện giao dịch.']);
        exit;
    }

    // Trừ tiền
    $stmt = $conn->prepare('UPDATE Customer SET balance = balance - :amount WHERE username = :username');
    $stmt->execute(['amount' => $amount, 'username' => $username]);

    // Đánh dấu Payment
    $stmt = $conn->prepare('UPDATE Payment SET status = "paid" WHERE payment_id = :payment_id');
    $stmt->execute(['payment_id' => $payment_id]);

    // Ghi lịch sử giao dịch
    $stmt = $conn->prepare('INSERT INTO Transaction (username, payment_id, amount, status) VALUES (:username, :payment_id, :amount, :status)');
    $stmt->execute([
        'username' => $username,
        'payment_id' => $payment_id,
        'amount' => $amount,
        'status' => 'success'
    ]);

    $transactionId = $conn->lastInsertId();
    $conn->commit();

    // Trả về kết quả
    $stmt = $conn->prepare('SELECT balance FROM Customer WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $newBalRow = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Thanh toán thành công.',
        'transaction_id' => $transactionId,
        'new_balance' => floatval($newBalRow['balance'])
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        try {
            $stmt = $conn->prepare('INSERT INTO Transaction (username, payment_id, amount, status) VALUES (:username, :payment_id, :amount, :status)');
            $stmt->execute([
                'username' => $username,
                'payment_id' => $payment_id,
                'amount' => $amount ?? 0,
                'status' => 'failed'
            ]);
        } catch (Exception $inner) {
            // ignore
        }
        $conn->rollBack();
    }

    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>