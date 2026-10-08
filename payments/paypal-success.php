<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Verify payment was successful and update order
if ($orderId > 0) {
    // Update order status
    $sql = "UPDATE orders SET status='Pending', payment_status='completed' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    
    // Clear cart and session
    unset($_SESSION['cart']);
    unset($_SESSION['current_order_id']);
    
    // Set success session variables
    $_SESSION['order_success'] = true;
    $_SESSION['order_id'] = $orderId;
}

include '../includes/header.php';
?>

<section class="payment-status" style="padding: 4rem 0; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="status-card" style="max-width: 600px; margin: 0 auto; background: white; padding: 3rem; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="status-icon" style="font-size: 4rem; color: #28a745; margin-bottom: 1.5rem;">
                ✅
            </div>
            <h1 style="color: #28a745; margin-bottom: 1rem;">Payment Successful!</h1>
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 2rem;">
                Thank you! Your PayPal payment was completed successfully.
            </p>
            
            <div class="order-details" style="background: #e8f5e8; padding: 1.5rem; border-radius: 8px; margin: 2rem 0; text-align: left;">
                <h4 style="color: #28a745; margin-top: 0;">Order Confirmation</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Order Number:</span>
                    <span>#<?php echo $orderId; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 600;">Status:</span>
                    <span style="color: #28a745;">Payment Completed</span>
                </div>
            </div>
            
            <div class="action-buttons" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="../order-success.php" class="btn btn-primary" 
                   style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    View Order Details
                </a>
                <a href="../index.php" class="btn btn-outline"
                   style="border: 2px solid #007bff; color: #007bff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Continue Shopping
                </a>
            </div>
            
            <div class="support-info" style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <p style="color: #888; margin-bottom: 0.5rem;">Payment processed securely by PayPal</p>
                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" style="width: 80px; opacity: 0.7;">
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>