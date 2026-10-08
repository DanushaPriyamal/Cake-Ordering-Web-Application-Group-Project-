<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = "orders";


if (!isAdminLoggedIn()) {
  redirect('spk-st-wl');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $orderId = (int)$_POST['order_id'];
  $newStatus = sanitize($_POST['status']);

  $sql = "UPDATE orders SET status = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("si", $newStatus, $orderId);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Order status updated successfully!';
  } else {
    $_SESSION['error_message'] = 'Error updating order status.';
  }

  redirect('orders');
}

// Pagination setup
$limit = 10; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

// Get total number of orders
$totalResult = $conn->query("SELECT COUNT(*) as total FROM orders");
$totalRow = $totalResult->fetch_assoc();
$totalOrders = $totalRow['total'];
$totalPages = ceil($totalOrders / $limit);

// Get orders with pagination
$sql = "SELECT o.*, c.name AS customer_name, c.email AS customer_email 
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset";
$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<style>
  /*  GENERAL RESET  */
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

  .container-order {
    width: 100%;
    max-width: 1100px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    padding: 25px;
    overflow-x: auto;
  }

  /*  HEADING  */
  h1 {
    text-align: center;
    color: #e45d87;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 30px;
  }

  /* ALERT MESSAGES */
  .alert {
    padding: 12px 18px;
    border-radius: 10px;
    margin-bottom: 15px;
    font-weight: 500;
    animation: fadeIn 0.6s ease;
  }

  .alert-success {
    background: #e6ffee;
    color: #1f7a3f;
    border-left: 5px solid #28a745;
  }

  .alert-danger {
    background: #ffe6e6;
    color: #a33a3a;
    border-left: 5px solid #dc3545;
  }

  /*  TABLE */
  .orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    overflow: hidden;
    border-radius: 10px;
  }

  .orders-table th,
  .orders-table td {
    padding: 14px 12px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
  }

  .orders-table th {
    background: #ffdae0;
    color: #5b1e32;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .orders-table tbody tr {
    background: #ffffff;
    transition: background 0.3s ease;
  }

  .orders-table tbody tr:hover {
    background: #fff4f6;
  }

  /* STATUS DROPDOWN  */
  .status-form {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .status-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  /* Colored status themes */
  .status-select.pending {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeeba;
  }

  .status-select.confirmed {
    background: #cce5ff;
    color: #004085;
    border-color: #b8daff;
  }

  .status-select.completed {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
  }

  .status-select.cancelled {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
  }

  /* BUTTONS  */
  .btn-status-update {
    background: #e45d87;
    border: none;
    color: white;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
  }

  .btn-status-update:hover {
    background: #c64b70;
    transform: scale(1.05);
  }

  .btn {
    display: inline-block;
    background: #fcb7c2;
    color: #5b1e32;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.3s ease, transform 0.2s ease;
  }

  .btn:hover {
    background: #f995a7;
    transform: scale(1.05);
  }

  .btn-primary {
    background: #e45d87;
    color: white;
  }

  .btn-primary:hover {
    background: #d44a73;
  }

  /* PAYMENT STATUS  */
  .payment-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .payment-status.paid {
    background: #d4edda;
    color: #155724;
  }

  .payment-status.unpaid {
    background: #f8d7da;
    color: #721c24;
  }

  /* RESPONSIVE DESIGN */
  @media (max-width: 768px) {
    .orders-table thead {
      display: none;
    }

    .orders-table,
    .orders-table tbody,
    .orders-table tr,
    .orders-table td {
      display: block;
      width: 100%;
    }

    .orders-table tr {
      background: #ffffff;
      margin-bottom: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 10px;
    }

    .orders-table td {
      text-align: right;
      padding: 10px 8px;
      position: relative;
    }

    .orders-table td::before {
      content: attr(data-label);
      position: absolute;
      left: 15px;
      width: 50%;
      text-align: left;
      font-weight: 600;
      color: #555;
    }

    h1 {
      font-size: 1.5rem;
    }
  }

  /*  ANIMATIONS */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }


  /* PAGINATION STYLES */
  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 25px 0 10px 0;
    gap: 8px;
    flex-wrap: wrap;
  }

  .pagination a,
  .pagination span {
    padding: 8px 14px;
    border: 1px solid #ffdae0;
    text-decoration: none;
    color: #e45d87;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.9rem;
  }

  .pagination a:hover {
    background: #e45d87;
    color: white;
    border-color: #e45d87;
    transform: translateY(-2px);
  }

  .pagination .current {
    background: #e45d87;
    color: white;
    border-color: #e45d87;
  }

  .pagination .disabled {
    color: #ccc;
    pointer-events: none;
    background-color: #f8f9fa;
    border-color: #eee;
  }

  .pagination-info {
    text-align: center;
    margin: 15px 0;
    color: #666;
    font-size: 0.9rem;
    background: #fff8f6;
    padding: 8px 15px;
    border-radius: 10px;
    border: 1px solid #ffdae0;
  }


  @media (max-width: 768px) {
    .pagination {
      gap: 5px;
    }

    .pagination a,
    .pagination span {
      padding: 6px 10px;
      font-size: 0.8rem;
    }

  }
</style>


<?php
include '../includes/header.php';
?>

<div class="admin-container">
  <div class="container-order">
    <h1>Order Management</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success">
        <?php echo $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger">
        <?php echo $_SESSION['error_message'];
        unset($_SESSION['error_message']); ?>
      </div>
    <?php endif; ?>

    <table class="orders-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td>
              <?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?>
              <br><small><?php echo htmlspecialchars($order['customer_email']); ?></small>
            </td>
            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
            <td>Rs.<?php echo number_format($order['total_amount'], 2); ?></td>
            <td>
              <form method="post" class="status-form">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <select name="status" class="status-select <?php echo strtolower($order['status']); ?>">
                  <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                  <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                  <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status" class="btn-status-update">
                  <i class="fas fa-save"></i>
                </button>
              </form>
            </td>
            <td>
              <span class="payment-status <?php echo strtolower($order['payment_status']); ?>">
                <?php echo ucfirst($order['payment_status']); ?>
              </span>
            </td>
            <td>
              <a href="order-details?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>



    <!-- Pagination Links -->
<?php if ($totalPages > 1): ?>
  <div class="pagination">
    <!-- Previous Button -->
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
    <?php else: ?>
      <span class="disabled">&laquo; Previous</span>
    <?php endif; ?>

    <!-- Page Numbers -->
    <?php
    // Show page numbers with ellipsis for many pages
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    if ($startPage > 1) {
      echo '<a href="?page=1">1</a>';
      if ($startPage > 2) echo '<span>...</span>';
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
      if ($i == $page) {
        echo '<span class="current">' . $i . '</span>';
      } else {
        echo '<a href="?page=' . $i . '">' . $i . '</a>';
      }
    }

    if ($endPage < $totalPages) {
      if ($endPage < $totalPages - 1) echo '<span>...</span>';
      echo '<a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
    }
    ?>

    <!-- Next Button -->
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
    <?php else: ?>
      <span class="disabled">Next &raquo;</span>
    <?php endif; ?>
  </div>
<?php endif; ?>



  </div>
</div>

<?php include '../includes/footer.php'; ?>