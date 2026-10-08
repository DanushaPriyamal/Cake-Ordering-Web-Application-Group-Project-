<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = "Order-Details";

if (!isAdminLoggedIn()) {
  redirect('spk-st-wl');
}

if (!isset($_GET['id'])) {
  redirect('orders');
}

$orderId = (int)$_GET['id'];

// Get order details
$orderSql = "SELECT o.*, c.name AS customer_name, c.email, c.phone, c.address 
             FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.id = ?";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$itemsSql = "SELECT oi.*, c.name AS cake_name, c.price AS unit_price 
             FROM order_items oi
             JOIN cakes c ON oi.cake_id = c.id
             WHERE oi.order_id = ?";
$stmt = $conn->prepare($itemsSql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>


<style>
  
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  /*  MAIN CONTAINER  */
  .admin-container {
    background: #fff8f6;
    min-height: 100vh;
    padding: 40px 15px;
    display: flex;
    justify-content: center;
  }

  .container-oder-details {
    width: 100%;
    max-width: 1100px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    padding: 25px;
    overflow-x: auto;
  }

  /* HEADER  */
  .order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
  }

  .order-header h1 {
    color: #e45d87;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .btn-primary {
    background: #e45d87;
    color: #fff;
  }

  .btn-primary:hover {
    background: #c94b74;
    transform: scale(1.05);
  }

  .btn-secondary {
    background: #fcb7c2;
    color: #5b1e32;
  }

  .btn-secondary:hover {
    background: #f995a7;
    transform: scale(1.05);
  }

  .btn-whatsapp {
    background: #25d366;
    color: white;
  }

  .btn-whatsapp:hover {
    background: #1da851;
  }

  .btn-call {
    background: #ffc107;
    color: #333;
  }

  .btn-call:hover {
    background: #e0a800;
  }

  /*  GRID */
  .order-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 35px;
  }

  .order-info,
  .customer-info {
    background: #fff4f6;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }

  .order-info h2,
  .customer-info h2 {
    color: #e45d87;
    font-size: 1.3rem;
    margin-bottom: 15px;
  }

  /* DETAILS */
  .detail-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    border-bottom: 1px dashed #f0c3cc;
    padding-bottom: 6px;
  }

  .detail-label {
    font-weight: 600;
    color: #5b1e32;
  }

  .detail-row span {
    font-size: 0.95rem;
    color: #444;
  }

  .total-amount {
    font-weight: 700;
    color: #e45d87;
  }

  /*STATUS & PAYMENT BADGES*/
  .status-badge,
  .payment-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .status-badge.pending {
    background: #fff3cd;
    color: #856404;
  }

  .status-badge.confirmed {
    background: #cce5ff;
    color: #004085;
  }

  .status-badge.completed {
    background: #d4edda;
    color: #155724;
  }

  .status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
  }

  .payment-status.paid {
    background: #d4edda;
    color: #155724;
  }

  .payment-status.unpaid {
    background: #f8d7da;
    color: #721c24;
  }

  /*ORDER ITEMS TABLE  */
  .order-items {
    background: #fff4f6;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
  }

  .order-items h2 {
    color: #e45d87;
    font-size: 1.3rem;
    margin-bottom: 15px;
  }

  .items-table {
    width: 100%;
    border-collapse: collapse;
  }

  .items-table th,
  .items-table td {
    padding: 12px 10px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
  }

  .items-table th {
    background: #ffdae0;
    color: #5b1e32;
    text-transform: uppercase;
    font-weight: 600;
  }

  .items-table tbody tr:hover {
    background: #fff0f3;
  }

  /* ACTION BUTTONS  */
  .order-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
  }

  /* ===== PRINT MODE */
  @media print {
    body * {
      visibility: hidden;
    }

    .container,
    .container * {
      visibility: visible;
    }

    .btn,
    .order-actions {
      display: none !important;
    }

    .container {
      box-shadow: none;
    }
  }

  /*  RESPONSIVE DESIGN */
  @media (max-width: 768px) {
    .order-details-grid {
      grid-template-columns: 1fr;
    }

    .order-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .order-header h1 {
      font-size: 1.5rem;
      margin-bottom: 15px;
    }

    .detail-row {
      flex-direction: column;
      align-items: flex-start;
    }

    .detail-label {
      margin-bottom: 3px;
    }

    .items-table thead {
      display: none;
    }

    .items-table,
    .items-table tbody,
    .items-table tr,
    .items-table td {
      display: block;
      width: 100%;
    }

    .items-table tr {
      background: #ffffff;
      margin-bottom: 10px;
      border-radius: 10px;
      box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
      padding: 8px;
    }

    .items-table td {
      text-align: right;
      padding: 8px;
      position: relative;
    }

    .items-table td::before {
      content: attr(data-label);
      position: absolute;
      left: 12px;
      width: 60%;
      text-align: left;
      font-weight: 600;
      color: #555;
    }
  }
</style>

<?php
include '../includes/header.php';
?>

<div class="admin-container">
  <div class="container-oder-details">
    <div class="order-header">
      <h1>Order Details #<?php echo $order['id']; ?></h1>
      <a href="orders.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Orders
      </a>
    </div>

    <div class="order-details-grid">
      <div class="order-info">
        <h2>Order Information</h2>
        <div class="detail-row">
          <span class="detail-label">Order Date:</span>
          <span><?php echo date('F j, Y \a\t g:i a', strtotime($order['created_at'])); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Status:</span>
          <span class="status-badge <?php echo strtolower($order['status']); ?>">
            <?php echo ucfirst($order['status']); ?>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Payment Status:</span>
          <span class="payment-status <?php echo strtolower($order['payment_status']); ?>">
            <?php echo ucfirst($order['payment_status']); ?>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Payment Method:</span>
          <span><?php echo ucfirst($order['payment_method']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Transaction ID:</span>
          <span><?php echo $order['transaction_id'] ?: 'N/A'; ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Amount:</span>
          <span class="total-amount">Rs.<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
      </div>

      <div class="customer-info">
        <h2>Customer Information</h2>
        <div class="detail-row">
          <span class="detail-label">Name:</span>
          <span><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email:</span>
          <span><?php echo htmlspecialchars($order['email']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Phone:</span>
          <span><?php echo htmlspecialchars($order['phone']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Delivery Address:</span>
          <span><?php echo nl2br(htmlspecialchars($order['address'])); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Customer Notes:</span>
          <span><?php echo nl2br(htmlspecialchars($order['notes'] ?: 'None')); ?></span>
        </div>
      </div>
    </div>

    <div class="order-items">
      <h2>Order Items</h2>
      <table class="items-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['cake_name']); ?></td>
              <td>Rs.<?php echo number_format($item['unit_price'], 2); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td>Rs.<?php echo number_format($item['price'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="order-actions">
      <!-- Print Invoice Button -->
      <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Print Invoice
      </button>

      <!-- Email Contact -->
      <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>?subject=Order%20%23<?php echo $order['id']; ?>%20-%20Delicious%20Cakes&body=Dear%20<?php echo urlencode($order['customer_name'] ?: 'Customer'); ?>,"
        class="btn btn-secondary">
        <i class="fas fa-envelope"></i> Email
      </a>

      <!-- WhatsApp Contact -->
      <?php if (!empty($order['phone'])): ?>
        <?php
        $rawPhone = preg_replace('/[^0-9]/', '', $order['phone']);
        $whatsappNumber = (strpos($rawPhone, '0') === 0)
          ? '94' . substr($rawPhone, 1)
          : $rawPhone;

        $customerName = isset($order['customer_name']) ? $order['customer_name'] : 'Customer';
        $message = "Hello $customerName, regarding your order #{$order['id']} from Yummy Cakes.";
        $whatsappMessage = urlencode($message);
        ?>
        <a href="https://wa.me/<?php echo htmlspecialchars($whatsappNumber); ?>?text=<?php echo $whatsappMessage; ?>"
          target="_blank"
          class="btn btn-whatsapp">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
      <?php endif; ?>



      <!-- SMS/Call -->
      <?php if (!empty($order['phone'])): ?>
        <a href="tel:<?php echo htmlspecialchars($order['phone']); ?>" class="btn btn-call">
          <i class="fas fa-phone"></i> Call
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>