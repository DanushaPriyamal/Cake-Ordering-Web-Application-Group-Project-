<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Order Details";

// Check if customer is logged in
if (!isCustomerLoggedIn()) {
    redirect('login');
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    redirect('account');
}

// Get order details
$sql = "SELECT o.*, c.name AS customer_name, c.email, c.phone 
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ? AND o.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('account');
}

// Get order items
$sql = "SELECT oi.*, c.name AS cake_name, c.image 
        FROM order_items oi
        JOIN cakes c ON oi.cake_id = c.id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<style>
    :root {
        --primary: #6c5ce7;
        --primary-dark: #5649c0;
        --success: #00b894;
        --warning: #fdcb6e;
        --danger: #d63031;
        --info: #0984e3;
        --dark: #2d3436;
        --light: #f8f9fa;
        --gray: #dfe6e9;
        --border-radius: 10px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    .order-details {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .order-details h1 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray);
        font-weight: 600;
    }

    .order-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .order-meta,
    .delivery-info {
        flex: 1;
        min-width: 300px;
    }

    .order-meta p {
        margin-bottom: 0.8rem;
        font-size: 1rem;
        color: #555;
        display: flex;
    }

    .order-meta strong {
        color: var(--dark);
        font-weight: 500;
        min-width: 140px;
        display: inline-block;
    }

    .delivery-info {
        background: var(--light);
        padding: 1.5rem;
        border-radius: var(--border-radius);
    }

    .delivery-info h3 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: var(--dark);
        font-weight: 600;
    }

    /* Status Badges */
    [class*="status-"] {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: rgba(253, 203, 110, 0.2);
        color: #e17055;
    }

    .status-completed {
        background-color: rgba(0, 184, 148, 0.2);
        color: var(--success);
    }

    .status-processing {
        background-color: rgba(9, 132, 227, 0.2);
        color: var(--info);
    }

    /* Order Items Table */
    .order-items {
        margin: 2rem 0;
        overflow-x: auto;
    }

    .order-items h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--dark);
        font-weight: 600;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
        min-width: 600px;
    }

    thead {
        background: var(--light);
    }

    th,
    td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--gray);
    }

    th {
        font-weight: 600;
        color: var(--dark);
    }

    .item-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .item-info img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }

    /* Order Actions */
    .order-actions {
        text-align: right;
        margin-top: 2rem;
    }

    .btn {
        display: inline-block;
        padding: 0.8rem 1.8rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .order-details {
            padding: 1.5rem;
        }

        .order-meta,
        .delivery-info {
            min-width: 100%;
        }

        table {
            min-width: 100%;
        }

        th,
        td {
            padding: 0.75rem;
        }

        .item-info img {
            width: 50px;
            height: 50px;
        }

        .order-actions {
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .order-details {
            padding: 1rem;
            margin: 1rem;
        }

        .order-details h1 {
            font-size: 1.5rem;
        }

        .order-items h2 {
            font-size: 1.25rem;
        }

        table {
            display: block;
            min-width: 100%;
        }

        thead {
            display: none;
        }

        tbody {
            display: block;
        }

        tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        td {
            display: flex;
            flex-direction: column;
            padding: 0.5rem;
            border: none;
        }

        td:first-child {
            grid-column: 1 / -1;
        }

        td::before {
            content: attr(data-label);
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .item-info {
            flex-direction: row;
        }

        .btn {
            width: 100%;
            padding: 0.9rem;
        }
    }
</style>

    <?php include 'includes/header.php'; ?>

    <main class="container order-details" style="padding-top: 30px;  padding-bottom:20px;">
        <h1>Order Details #<?php echo $order['id']; ?></h1>

        <div class="order-summary">
            <div class="order-meta">
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Total:</strong> Rs.<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                <p><strong>Payment Status:</strong> <?php echo $order['payment_status'] ?? 'N/A'; ?></p>
            </div>

            <div class="delivery-info">
                <h3>Delivery Information</h3>
                <p style="color: black;"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                <p style="color: black;">Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
        </div>

        <div class="order-items">
            <h2>Order Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="color: black;">
                                <div class="item-info">
                                    <img src="<?php echo getCakeImage($item['image']); ?>" alt="<?php echo htmlspecialchars($item['cake_name']); ?>" width="50">
                                    <?php echo htmlspecialchars($item['cake_name']); ?>
                                </div>
                            </td>
                            <td style="color: black;">Rs.<?php echo number_format($item['price'], 2); ?></td>
                            <td style="color: black;"><?php echo $item['quantity']; ?></td>
                            <td style="color: black;">Rs.<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="order-actions">
            <a href="account" class="btn">Back to Account</a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>