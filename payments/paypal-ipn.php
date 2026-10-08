<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// PayPal IPN (Instant Payment Notification) Handler

// Read the POST data from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = [];

foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Build the validation request
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// PayPal sandbox for testing (use live URL for production)
$paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
// $paypal_url = 'https://www.paypal.com/cgi-bin/webscr'; // Live version

// Post IPN data back to PayPal for verification
$ch = curl_init($paypal_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);

$res = curl_exec($ch);

if ($res === false) {
    file_put_contents('paypal_ipn.log', date('Y-m-d H:i:s') . " - CURL ERROR: " . curl_error($ch) . "\n", FILE_APPEND);
}
curl_close($ch);

// Log IPN response
file_put_contents('paypal_ipn.log', date('Y-m-d H:i:s') . " - IPN Response: " . $res . "\n", FILE_APPEND);
file_put_contents('paypal_ipn.log', date('Y-m-d H:i:s') . " - IPN Data: " . json_encode($myPost) . "\n", FILE_APPEND);

// Check PayPal response
if (strcmp(trim($res), "VERIFIED") === 0) {
    // Verified payment
    $order_id = isset($myPost['custom']) ? (int)$myPost['custom'] : 0;
    $payment_status = $myPost['payment_status'] ?? '';
    $txn_id = $myPost['txn_id'] ?? '';
    $payment_amount = $myPost['mc_gross'] ?? 0;
    $payment_currency = $myPost['mc_currency'] ?? '';

    if ($payment_status === 'Completed') {
        // Update order status
        $sql = "UPDATE orders SET status='completed', payment_status='paid', transaction_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $txn_id, $order_id);
        $stmt->execute();

        // Clear cart session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['cart']);
        unset($_SESSION['current_order_id']);

        file_put_contents('paypal_ipn.log', "Order $order_id marked as completed\n", FILE_APPEND);
    }

    http_response_code(200);
} else {
    // Invalid or failed verification
    file_put_contents('paypal_ipn.log', "INVALID IPN: " . json_encode($myPost) . "\n", FILE_APPEND);
    http_response_code(400);
}
?>
