<?php
/**
 * concurrent_payment_test.php
 *
 * Mô tả: Script CLI PHP để mô phỏng nhiều lần 2 request gần như đồng thời
 * gửi tới endpoint xử lý giao dịch (process-transaction.php). Dùng để kiểm tra
 * race condition khi hai tài khoản cùng thanh toán cho 1 MSSV/payment.
 *
 * Cách chạy (PowerShell):
 * php concurrent_payment_test.php --url=http://localhost/ibanking-app/be/php-backend/process-transaction.php --payment_id=1 --amount=3500000 --users=payer_a,payer_b --iterations=10 --delay_ms=50
 */

$defaults = [
    'url' => 'http://localhost/ibanking-app/be/php-backend/process-transaction.php',
    'payment_id' => 1,
    'student_id' => '',
    'amount' => 3500000,
    'users' => 'payer_a,payer_b',
    'iterations' => 5,
    'delay_ms' => 10,
];

$args = $argv;
array_shift($args);
foreach ($args as $arg) {
    if (strpos($arg, '--') !== 0) continue;
    $pair = explode('=', substr($arg, 2), 2);
    if (count($pair) == 2) {
        $k = $pair[0]; $v = $pair[1];
        if (array_key_exists($k, $defaults)) $defaults[$k] = $v;
    }
}

$url = $defaults['url'];
$payment_id = (int)$defaults['payment_id'];
$student_id = trim($defaults['student_id']);
$amount = (float)$defaults['amount'];
$users = array_map('trim', explode(',', $defaults['users']));
$iterations = max(1, (int)$defaults['iterations']);
$delay_ms = max(0, (int)$defaults['delay_ms']);

echo "Concurrent payment test\n";
echo "URL: $url\n";
echo "Payment ID: $payment_id, Student ID: $student_id, Amount: $amount\n";
echo "Users: ".implode(', ', $users)."\n";
echo "Iterations: $iterations, delay between iterations: ${delay_ms}ms\n\n";

$stats = ['total'=>0, 'success'=>0, 'failed'=>0, 'per_user'=>[]];
foreach ($users as $u) $stats['per_user'][$u] = ['success'=>0,'failed'=>0];

for ($i=1; $i<=$iterations; $i++) {
    echo "== Iteration $i ==\n";

    $payloads = [];
    foreach ($users as $u) {
        $p = ['username'=>$u, 'amount'=>$amount];
        if ($payment_id) $p['payment_id'] = $payment_id;
        if ($student_id) $p['student_id'] = $student_id;
        $payloads[] = json_encode($p);
    }

    // init multi curl
    $mh = curl_multi_init();
    $chs = [];

    foreach ($payloads as $idx => $payload) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_multi_add_handle($mh, $ch);
        $chs[$idx] = $ch;
    }

    // execute
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh, 0.5);
    } while ($running > 0);

    // collect
    foreach ($chs as $idx => $ch) {
        $resp = curl_multi_getcontent($ch);
        $info = curl_getinfo($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);

        $user = $users[$idx];
        $stats['total']++;

        $parsed = json_decode($resp, true);
        $ok = false;
        if (is_array($parsed) && array_key_exists('success', $parsed) && $parsed['success'] === true) {
            $ok = true;
        }

        if ($ok) {
            echo "[$user] => SUCCESS: ".(is_array($parsed) ? json_encode($parsed) : $resp)."\n";
            $stats['success']++;
            $stats['per_user'][$user]['success']++;
        } else {
            echo "[$user] => FAIL: HTTP_CODE={$info['http_code']} RESP=".trim($resp)."\n";
            $stats['failed']++;
            $stats['per_user'][$user]['failed']++;
        }
    }

    curl_multi_close($mh);

    // small delay between iterations
    if ($i < $iterations && $delay_ms > 0) {
        usleep($delay_ms * 1000);
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "Total requests: {$stats['total']}\n";
echo "Success: {$stats['success']}, Failed: {$stats['failed']}\n";
foreach ($stats['per_user'] as $user => $s) {
    echo " - $user: success={$s['success']}, failed={$s['failed']}\n";
}

echo "\nLưu ý: Trước khi chạy, hãy đảm bảo bảng Payment ở trạng thái 'unpaid' và các tài khoản payer có đủ số dư.\n";

?>
