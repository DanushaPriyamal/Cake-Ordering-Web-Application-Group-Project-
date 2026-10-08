<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for order success BEFORE unsetting
if (!isset($_SESSION['order_success']) || !isset($_SESSION['order_id'])) {
    $_SESSION['error_message'] = "Invalid order access";
    redirect('index');
    exit();
}

$orderId = $_SESSION['order_id'];

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
    $order = null;
    $_SESSION['error_message'] = "Order not found";
    redirect('index');
    exit();
} else {
    $order = $orderResult->fetch_assoc();
}

// Fetch ordered items
$sql = "SELECT oi.quantity, oi.price, ck.name AS cake_name, ck.image
        FROM order_items oi 
        JOIN cakes ck ON oi.cake_id = ck.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$itemsResult = $stmt->get_result();

$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}

// Clear session flags AFTER we've used them
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>
<style>
    .order-success-section {
        padding: 3rem 0;
        background-color: #f9f9f9;
        min-height: 100vh;
    }

    .order-success-card {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .success-icon {
        margin-bottom: 1.5rem;
    }

    .success-title {
        font-size: 2rem;
        color: #2e7d32;
        margin-bottom: 0.5rem;
    }

    .success-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }

    .order-details-box {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: #333;
    }

    .detail-value {
        color: #555;
    }

    .status-pending {
        color: #ff9800;
    }

    .status-completed {
        color: #4caf50;
    }

    .status-processing {
        color: #2196f3;
    }

    .status-cancelled {
        color: #f44336;
    }

    .ordered-items {
        margin: 2rem 0;
        text-align: left;
    }

    .ordered-items h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }

    .items-list {
        margin-top: 1rem;
    }



    .item-image {
        width: 60px;
        height: 60px;
        margin-right: 1rem;
        border-radius: 8px;
        overflow: hidden;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-info {
        flex: 1;
    }

    .item-info h4 {
        margin: 0 0 0.3rem 0;
        font-size: 1rem;
    }

    .item-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .item-total {
        font-weight: 600;
        min-width: 80px;
        text-align: right;
    }

    .customer-notes {
        background: #e8f5e9;
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
        text-align: left;
    }

    .customer-notes h3 {
        margin-top: 0;
        color: #2e7d32;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    .btn-whatsapp {
        background-color: #25D366;
        color: white;
    }

    .btn-whatsapp:hover {
        background-color: #128C7E;
        color: white;
    }

    @media (max-width: 768px) {
        .order-success-card {
            padding: 1.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }

    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* item-total එක right-align වෙයි */
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .item-image {
        width: 60px;
        height: 60px;
        margin-right: 1rem;
    }

    .item-info {
        flex: 1;
        /* left side full width දෙනවා */
    }

    .item-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .item-total {
        font-weight: 600;
        min-width: 100px;
        text-align: right;
        color: black;
    }
</style>

<?php
include 'includes/header.php';
?>

<section class="order-success-section">
    <div class="container">
        <div class="order-success-card">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="#4CAF50">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            
            <h1 class="success-title">Order Confirmed!</h1>
            <p class="success-subtitle">Thank you for your purchase</p>
            
            <div class="order-details-box">
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">Rs.<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value status-<?php echo strtolower($order['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                    </span>
                </div>
            </div>
            
            <div class="ordered-items">
                <h3>Your Order Items</h3>
                <div class="items-list">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="item-row">
                        <div class="item-image">
                            <img src="<?php echo getCakeImage($item['image']); ?>" alt="<?php echo htmlspecialchars($item['cake_name']); ?>">
                        </div>
                        <div class="item-info">
                        <h4 style="color: black;"><?php echo htmlspecialchars($item['cake_name']); ?></h4>
                        <div class="item-meta">
                                <span>Qty: <?php echo $item['quantity']; ?></span>
                                <span>Price: Rs.<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                        </div>
                        <div class="item-total">
                            Rs.<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="customer-notes">
                <h3>Next Steps</h3>
                <p style="color: black;">We've sent an order confirmation to <?php echo htmlspecialchars($order['email']); ?></p>
                <p style="color: black;">Our team will contact you at <?php echo htmlspecialchars($order['phone']); ?> for delivery details.</p>
            </div>
            
           <div class="action-buttons" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">

                <!-- Continue Shopping -->
                <a href="products"
                    style="color: black; background-color: #cce5ff; border: 1px solid #b8daff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">
                    Continue Shopping
                </a>

                <!-- View Order History -->
                <a href="account"
                    style="color: black; background-color: #f9f9f9; border: 1px solid black; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">
                    View Order History
                </a>

                <!-- WhatsApp Contact -->
                <?php if (isset($_SESSION['whatsapp_notification_url'])): ?>
                    <a href="<?php echo $_SESSION['whatsapp_notification_url']; ?>"
                        target="_blank"
                        style="background-color: #25D366; color: white; border: none; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">
                        <i class="fab fa-whatsapp"></i> Contact Us on WhatsApp
                    </a>
                    <?php unset($_SESSION['whatsapp_notification_url']); ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>