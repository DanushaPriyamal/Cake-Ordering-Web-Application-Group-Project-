<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
$pageTitle = "Dashboard";


// Redirect if not logged in
if (!isAdminLoggedIn()) {
  redirect('spk-st-wl');
}

// Get stats
$totalCakes = $conn->query("SELECT COUNT(*) FROM cakes")->fetch_row()[0];
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalRevenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'")->fetch_row()[0];
$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$totalSubcategories = $conn->query("SELECT COUNT(*) FROM subcategories")->fetch_row()[0];
$totalBrands = $conn->query("SELECT COUNT(*) FROM brands")->fetch_row()[0];
$totalCustomers = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];


$recentOrders = $conn->query("
    SELECT o.id, o.total_amount, o.status, o.created_at, c.name AS customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<style>
  /*Global Styles */
  body {
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
    background: #fff8f7;
    color: #333;
  }

  .container {
    width: 90%;
    max-width: 1200px;
    margin: auto;
  }

  h1,
  h2,
  h3 {
    font-weight: 600;
    color: #3b2d2f;
  }

  /*  Dashboard Header */
  .dashboard h1 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color: #b23b52;
  }

  /*  Stats Cards  */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }

  .stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  .stat-card h3 {
    font-size: 1.1rem;
    color: #b23b52;
    margin-bottom: 10px;
  }

  .stat-card p {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 10px 0;
  }

  .stat-card .btn {
    background: #b23b52;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: 0.3s;
  }

  .stat-card .btn:hover {
    background: #8d2d43;
  }

  /* Recent Orders (Updated & Responsive) */
  .recent-orders {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 40px;
    overflow-x: auto;
  }

  .recent-orders h2 {
    margin-bottom: 10px;
    color: #b23b52;
    text-align: left;
  }

  .orders-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
    margin-top: 10px;
  }

  .orders-table th,
  .orders-table td {
    text-align: left;
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
    white-space: nowrap;
  }

  .orders-table th {
    background: #f9e2e5;
    color: #8d2d43;
    font-weight: 600;
  }

  .orders-table tr:hover {
    background: #fff3f4;
  }

  .status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #fff;
  }

  .status-badge.pending {
    background: #f6b93b;
  }

  .status-badge.confirmed {
    background: #38ada9;
  }

  .status-badge.completed {
    background: #78e08f;
  }

  .status-badge.cancelled {
    background: #e55039;
  }

  .btn-primary {
    background: #b23b52;
    color: #fff;
    border-radius: 6px;
    padding: 6px 10px;
    text-decoration: none;
    white-space: nowrap;
  }

  .btn-secondary {
    background: #f6b93b;
    color: #fff;
    border-radius: 6px;
    padding: 8px 14px;
    text-decoration: none;
    transition: 0.3s;
  }

  .btn-secondary:hover {
    background: #dca835;
  }

  .view-all {
    text-align: center;
    margin-top: 20px;
  }

  /* Quick Actions  */
  .quick-actions {
    margin-bottom: 40px;
  }

  .action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
  }

  .action-card {
    background: linear-gradient(145deg, #fff, #ffe5ea);
    padding: 20px;
    border-radius: 16px;
    text-align: center;
    color: #b23b52;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    text-decoration: none;
  }

  .action-card:hover {
    background: #b23b52;
    color: #fff;
    transform: translateY(-5px);
  }

  .action-card i {
    font-size: 2rem;
    margin-bottom: 10px;
  }

  /* Logout Button */
  .logout-form {
    text-align: center;
    margin-top: 30px;
  }

  .btn-danger {
    background: #e55039;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
  }

  .btn-danger:hover {
    background: #c23616;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .dashboard h1 {
      font-size: 1.6rem;
    }

    .stat-card h3 {
      font-size: 1rem;
    }

    .orders-table {
      display: block;
      overflow-x: auto;
      white-space: nowrap;
    }

    .orders-table th,
    .orders-table td {
      font-size: 0.9rem;
      padding: 8px;
    }

    .recent-orders::-webkit-scrollbar {
      height: 6px;
    }

    .recent-orders::-webkit-scrollbar-thumb {
      background: #b23b52;
      border-radius: 3px;
    }

    .action-grid {
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
  }

  /* Small Screens: Card Layout for Orders  */
  @media (max-width: 550px) {

    .orders-table,
    .orders-table thead,
    .orders-table tbody,
    .orders-table th,
    .orders-table td,
    .orders-table tr {
      display: block;
    }

    .orders-table thead {
      display: none;
    }

    .orders-table tr {
      background: #fff8f9;
      margin-bottom: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      padding: 10px;
    }

    .orders-table td {
      border: none;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 10px;
      font-size: 0.9rem;
    }

    .orders-table td::before {
      content: attr(data-label);
      font-weight: 600;
      color: #8d2d43;
    }
  }

  @media (max-width: 480px) {

    .stats-grid,
    .action-grid {
      grid-template-columns: 1fr;
    }

    .orders-table {
      font-size: 0.85rem;
    }
  }
</style>



<?php
include '../includes/header.php';
?>

<div class="dashboard" style="padding-top: 30px;">
  <div class="container">
    <h1>Admin Dashboard</h1>

    <!-- Stats Summary -->
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Products</h3>
        <p><?php echo $totalCakes; ?></p>
        <a href="cakes/list" class="btn btn-sm">Manage</a>
      </div>
      <div class="stat-card">
        <h3>Total Orders</h3>
        <p><?php echo $totalOrders; ?></p>
        <a href="orders" class="btn btn-sm">View All</a>
      </div>
      <div class="stat-card">
        <h3>Total Revenue</h3>
        <p>Rs.<?php echo number_format($totalRevenue ?? 0, 2); ?></p>
      </div>
      <div class="stat-card">
        <h3>Categories</h3>
        <p><?php echo $totalCategories; ?></p>
        <a href="categories/list" class="btn btn-sm">Manage</a>
      </div>
      <div class="stat-card">
        <h3>Subcategories</h3>
        <p><?php echo $totalSubcategories; ?></p>
        <a href="subcategories/list" class="btn btn-sm">Manage</a>
      </div>
      <div class="stat-card">
        <h3>Brands</h3>
        <p><?php echo $totalBrands; ?></p>
        <a href="brand/list" class="btn btn-sm">Manage</a>
      </div>
      <div class="stat-card">
        <h3>Total Customers</h3>
        <p><?php echo $totalCustomers; ?></p>
        <a href="customers/list" class="btn btn-sm">Manage</a>
      </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="recent-orders">
      <h2>Recent Orders</h2>
      <table class="orders-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentOrders)): ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td data-label="Order ID">#<?php echo $order['id']; ?></td>
                <td data-label="Customer"><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></td>
                <td data-label="Amount">Rs.<?php echo number_format($order['total_amount'], 2); ?></td>
                <td data-label="Status">
                  <span class="status-badge <?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </td>
                <td data-label="Date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                <td data-label="Action">
                  <a href="order-details?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">No recent orders.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="view-all">
        <a href="orders" class="btn btn-secondary">View All Orders</a>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-grid">
        <a href="cakes/add" class="action-card">
          <i class="fas fa-plus-circle"></i>
          <span>Add New Product</span>
        </a>
        <a href="categories/add" class="action-card">
          <i class="fas fa-folder-plus"></i>
          <span>Add Category</span>
        </a>
        <a href="subcategories/add" class="action-card">
          <i class="fas fa-folder-open"></i>
          <span>Add Subcategory</span>
        </a>
        <a href="brand/add" class="action-card">
          <i class="fas fa-tag"></i>
          <span>Add Brand</span>
        </a>
        <a href="customers/add" class="action-card">
          <i class="fas fa-user-plus"></i>
          <span>Add Customer</span>
        </a>
      </div>
    </div>

    <!-- Logout -->
    <form action="logout" method="post" class="logout-form">
      <button type="submit" class="btn btn-danger">Logout</button>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>