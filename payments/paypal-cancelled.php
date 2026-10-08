<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : 'Unknown';

include '../includes/header.php';
?>

<section class="payment-status" style="padding: 4rem 0; background: #f8f9fa; min-height: 100vh;">
    <div class="container">
        <div class="status-card" style="max-width: 600px; margin: 0 auto; background: white; padding: 3rem; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="status-icon" style="font-size: 4rem; color: #ff6b6b; margin-bottom: 1.5rem;">
                ❌
            </div>
            <h1 style="color: #dc3545; margin-bottom: 1rem;">Payment Cancelled</h1>
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 2rem;">
                Your PayPal payment for Order #<?php echo htmlspecialchars($orderId); ?> was cancelled.
                No charges were made to your account.
            </p>
            
            <div class="action-buttons" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="../checkout.php" class="btn btn-primary" 
                   style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Try Payment Again
                </a>
                <a href="../cart.php" class="btn btn-secondary"
                   style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Return to Cart
                </a>
                <a href="../index.php" class="btn btn-outline"
                   style="border: 2px solid #007bff; color: #007bff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Continue Shopping
                </a>
            </div>
            
            <div class="support-info" style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <p style="color: #888; margin-bottom: 0.5rem;">Need help with your payment?</p>
                <a href="https://wa.me/94XXXXXXXXX" target="_blank" 
                   style="color: #25D366; text-decoration: none; font-weight: 600;">
                    <i class="fab fa-whatsapp"></i> Contact us on WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>