<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if order exists
if (!isset($_SESSION['current_order_id'])) {
    $_SESSION['error_message'] = "No order found";
    redirect('../cart');
}

$orderId = $_SESSION['current_order_id'];

// Fetch order details
$sql = "SELECT o.*, c.name, c.email, c.phone 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    $_SESSION['error_message'] = "Order not found";
    redirect('../cart');
}

$order = $orderResult->fetch_assoc();

// PayPal configuration
$paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // Sandbox URL
// $paypal_url = 'https://www.paypal.com/cgi-bin/webscr'; // Live URL

$paypal_email = 'sb-nlnys43837697@personal.example.com'; // Your PayPal business email
$currency_code = 'USD'; // PayPal supports USD, EUR, etc. You might want to convert LKR to USD

// Convert LKR to USD (approximate conversion rate)
$conversion_rate = 0.0031; // 1 LKR = 0.0031 USD (adjust as needed)
$usd_amount = $order['total_amount'] * $conversion_rate;

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayPal - Cake Shop</title>
    <style>
        .payment-redirect {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #003087 0%, #009cde 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .redirect-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 2rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-details {
            background: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            text-align: left;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .paypal-logo {
            width: 150px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <section class="payment-redirect">
        <div class="redirect-container">
            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="paypal-logo">
            <div class="loading-spinner"></div>
            <h2>Redirecting to PayPal</h2>
            <p>Please wait while we connect you to secure PayPal payment gateway...</p>
            
            <div class="payment-details">
                <h4>Order Summary</h4>
                <div class="detail-item">
                    <span>Order ID:</span>
                    <strong>#<?php echo $orderId; ?></strong>
                </div>
                <div class="detail-item">
                    <span>Amount (LKR):</span>
                    <strong>Rs.<?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Amount (USD):</span>
                    <strong>$<?php echo number_format($usd_amount, 2); ?></strong>
                </div>
                <div class="detail-item">
                    <span>Customer:</span>
                    <strong><?php echo htmlspecialchars($order['name']); ?></strong>
                </div>
            </div>
            
            <form action="<?php echo $paypal_url; ?>" method="post" id="paypalForm">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
                <input type="hidden" name="item_name" value="Cake Order #<?php echo $orderId; ?>">
                <input type="hidden" name="item_number" value="<?php echo $orderId; ?>">
                <input type="hidden" name="amount" value="<?php echo number_format($usd_amount, 2); ?>">
                <input type="hidden" name="currency_code" value="<?php echo $currency_code; ?>">
                <input type="hidden" name="return" value="<?php echo SITE_URL; ?>payments/paypal-success.php?order_id=<?php echo $orderId; ?>">
                <input type="hidden" name="cancel_return" value="<?php echo SITE_URL; ?>payments/paypal-cancelled.php?order_id=<?php echo $orderId; ?>">
                <input type="hidden" name="notify_url" value="<?php echo SITE_URL; ?>payments/paypal-ipn.php">
                <input type="hidden" name="custom" value="<?php echo $orderId; ?>">
                <input type="hidden" name="no_shipping" value="1">
                <input type="hidden" name="rm" value="2">
                
                <button type="submit" class="btn btn-light" style="padding: 12px 24px; border: none; border-radius: 5px; background: white; color: #003087; font-weight: bold; font-size: 16px;">
                    Proceed to PayPal
                </button>
            </form>
            
            <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                You will be redirected to PayPal to complete your payment securely.
            </p>
        </div>
    </section>

    <script>
        // Auto-submit form after short delay
        setTimeout(function() {
            document.getElementById('paypalForm').submit();
        }, 3000);

        // Manual submit button
        document.querySelector('button[type="submit"]').addEventListener('click', function() {
            document.getElementById('paypalForm').submit();
        });
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>