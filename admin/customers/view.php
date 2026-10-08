<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

$customerId = (int)$_GET['id'];

// Get customer details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    redirect('list');
}

// Pagination setup
$limit = 10; // orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Count total orders for this customer
$countSql = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?";
$stmt = $conn->prepare($countSql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalOrders = $totalResult['total'];
$totalPages = ceil($totalOrders / $limit);

// Fetch paginated orders
$sql = "
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.customer_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $customerId, $limit, $offset);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<style>
/* customer-details.css - Modern Responsive CSS for Customer Details Page */

:root {
  /* Color Palette - Cake Themed */
  --primary: #e91e63;
  --primary-light: #f8bbd9;
  --primary-dark: #ad1457;
  --secondary: #ff9800;
  --secondary-light: #ffcc80;
  --accent: #ff4081;
  --success: #4caf50;
  --warning: #ff9800;
  --danger: #f44336;
  --info: #2196f3;
  
  /* Neutral Colors */
  --light: #f8f9fa;
  --dark: #343a40;
  --gray-100: #f8f9fa;
  --gray-200: #e9ecef;
  --gray-300: #dee2e6;
  --gray-400: #ced4da;
  --gray-500: #adb5bd;
  --gray-600: #6c757d;
  --gray-700: #495057;
  --gray-800: #343a40;
  --gray-900: #212529;
  
  /* Typography */
  --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  --font-size-xs: 0.75rem;
  --font-size-sm: 0.875rem;
  --font-size-base: 1rem;
  --font-size-lg: 1.125rem;
  --font-size-xl: 1.25rem;
  --font-size-2xl: 1.5rem;
  --font-size-3xl: 1.875rem;
  
  /* Spacing */
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  
  /* Border Radius */
  --border-radius-sm: 0.25rem;
  --border-radius: 0.5rem;
  --border-radius-lg: 0.75rem;
  --border-radius-xl: 1rem;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  
  /* Transitions */
  --transition: all 0.3s ease;
}

/* Reset and Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family);
  font-size: var(--font-size-base);
  line-height: 1.6;
  color: var(--gray-800);
  background-color: #f9f5f0;
  min-height: 100vh;
}

/* Admin Container */
.admin-container {
  padding: var(--space-6) var(--space-4);
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.container {
  max-width: 100%;
  margin: 0 auto;
}

/* Headings */
h1, h2, h3, h4, h5, h6 {
  margin-bottom: var(--space-6);
  color: var(--primary-dark);
  font-weight: 600;
  line-height: 1.2;
}

h1 {
  font-size: var(--font-size-3xl);
  position: relative;
  padding-bottom: var(--space-3);
  margin-bottom: var(--space-8);
}

h1::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 60px;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  border-radius: 2px;
}

h2 {
  font-size: var(--font-size-2xl);
  color: var(--primary);
  margin-bottom: var(--space-6);
  padding-bottom: var(--space-2);
  border-bottom: 2px solid var(--primary-light);
}

/* Cards */
.card {
  background: white;
  border-radius: var(--border-radius-xl);
  padding: var(--space-8);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--gray-200);
  margin-bottom: var(--space-8);
  transition: var(--transition);
}

.card:hover {
  box-shadow: var(--shadow-xl);
  transform: translateY(-2px);
}

/* Customer Header */
.customer-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: var(--space-6);
  margin-bottom: var(--space-6);
}

.customer-info {
  flex: 1;
}

.customer-name {
  font-size: var(--font-size-2xl);
  font-weight: 700;
  color: var(--primary-dark);
  margin-bottom: var(--space-2);
  line-height: 1.2;
}

.customer-email {
  font-size: var(--font-size-lg);
  color: var(--gray-600);
  margin-bottom: var(--space-6);
  font-weight: 500;
}

/* Customer Details Grid */
.customer-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--space-4);
  margin-bottom: var(--space-4);
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  padding: var(--space-3);
  background: var(--gray-50);
  border-radius: var(--border-radius);
  border-left: 4px solid var(--primary);
}

.detail-label {
  font-size: var(--font-size-sm);
  color: var(--gray-600);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: var(--font-size-base);
  color: var(--gray-800);
  font-weight: 600;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: var(--space-3);
  flex-shrink: 0;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-6);
  font-size: var(--font-size-base);
  font-weight: 600;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  text-decoration: none;
  gap: var(--space-2);
  white-space: nowrap;
}

.btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.3);
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--accent));
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-warning {
  background: linear-gradient(135deg, var(--warning), #ffb74d);
  color: white;
}

.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-secondary {
  background: white;
  color: var(--gray-700);
  border: 2px solid var(--gray-300);
}

.btn-secondary:hover {
  background: var(--gray-100);
  border-color: var(--gray-400);
  transform: translateY(-2px);
}

.btn-sm {
  padding: var(--space-2) var(--space-4);
  font-size: var(--font-size-sm);
  font-weight: 500;
}

/* Orders Table */
.orders-table-container {
  overflow-x: auto;
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow);
  margin-bottom: var(--space-6);
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 700px;
}

.orders-table thead {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
}

.orders-table th {
  padding: var(--space-4) var(--space-3);
  text-align: left;
  font-weight: 600;
  color: white;
  font-size: var(--font-size-sm);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.orders-table td {
  padding: var(--space-4) var(--space-3);
  border-bottom: 1px solid var(--gray-200);
  vertical-align: middle;
}

.orders-table tbody tr {
  transition: var(--transition);
}

.orders-table tbody tr:hover {
  background-color: var(--primary-light);
}

.orders-table tbody tr:last-child td {
  border-bottom: none;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: var(--space-1) var(--space-3);
  border-radius: 20px;
  font-size: var(--font-size-xs);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending {
  background-color: rgba(255, 152, 0, 0.1);
  color: var(--warning);
  border: 1px solid var(--warning);
}

.status-processing {
  background-color: rgba(33, 150, 243, 0.1);
  color: var(--info);
  border: 1px solid var(--info);
}

.status-completed {
  background-color: rgba(76, 175, 80, 0.1);
  color: var(--success);
  border: 1px solid var(--success);
}

.status-cancelled {
  background-color: rgba(244, 67, 54, 0.1);
  color: var(--danger);
  border: 1px solid var(--danger);
}

.status-paid {
  background-color: rgba(76, 175, 80, 0.1);
  color: var(--success);
  border: 1px solid var(--success);
}

.status-unpaid {
  background-color: rgba(244, 67, 54, 0.1);
  color: var(--danger);
  border: 1px solid var(--danger);
}

.status-pending-payment {
  background-color: rgba(255, 152, 0, 0.1);
  color: var(--warning);
  border: 1px solid var(--warning);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: var(--space-12) var(--space-6);
  color: var(--gray-500);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: var(--space-4);
  color: var(--gray-400);
}

.empty-state p {
  font-size: var(--font-size-lg);
  margin: 0;
  font-weight: 500;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-8);
  flex-wrap: wrap;
}

.pagination a,
.pagination span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
  min-width: 40px;
  height: 40px;
  font-size: var(--font-size-sm);
}

.pagination a {
  background-color: white;
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
}

.pagination a:hover {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary);
  transform: translateY(-1px);
}

.pagination .current {
  background-color: var(--primary);
  color: white;
  border: 1px solid var(--primary);
}

.pagination .disabled {
  background-color: var(--gray-200);
  color: var(--gray-500);
  border: 1px solid var(--gray-300);
  cursor: not-allowed;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  .admin-container {
    padding: var(--space-4) var(--space-3);
  }
  
  h1 {
    font-size: var(--font-size-2xl);
    margin-bottom: var(--space-6);
  }
  
  h2 {
    font-size: var(--font-size-xl);
  }
  
  .card {
    padding: var(--space-6) var(--space-4);
    border-radius: var(--border-radius-lg);
  }
  
  /* Customer Header Mobile */
  .customer-header {
    flex-direction: column;
    gap: var(--space-4);
  }
  
  .customer-name {
    font-size: var(--font-size-xl);
  }
  
  .customer-email {
    font-size: var(--font-size-base);
  }
  
  /* Customer Details Mobile */
  .customer-details {
    grid-template-columns: 1fr;
    gap: var(--space-3);
  }
  
  .detail-item {
    padding: var(--space-3);
  }
  
  /* Action Buttons Mobile */
  .action-buttons {
    width: 100%;
    justify-content: stretch;
  }
  
  .action-buttons .btn {
    flex: 1;
    text-align: center;
    justify-content: center;
  }
  
  /* Orders Table Mobile */
  .orders-table-container {
    margin: 0 -var(--space-3);
    width: calc(100% + var(--space-6));
    border-radius: 0;
  }
  
  .orders-table {
    min-width: 800px;
    font-size: var(--font-size-sm);
  }
  
  .orders-table th,
  .orders-table td {
    padding: var(--space-3) var(--space-2);
  }
  
  /* Pagination Mobile */
  .pagination {
    gap: var(--space-1);
  }
  
  .pagination a,
  .pagination span {
    min-width: 35px;
    height: 35px;
    padding: var(--space-1) var(--space-2);
    font-size: var(--font-size-xs);
  }
}

@media (max-width: 480px) {
  .admin-container {
    padding: var(--space-3) var(--space-2);
  }
  
  .card {
    padding: var(--space-5) var(--space-3);
  }
  
  h1 {
    font-size: var(--font-size-xl);
  }
  
  .customer-name {
    font-size: var(--font-size-lg);
  }
  
  .btn {
    padding: var(--space-2) var(--space-4);
    font-size: var(--font-size-sm);
  }
  
  .orders-table {
    min-width: 850px;
  }
  
  .orders-table th,
  .orders-table td {
    padding: var(--space-2);
    font-size: var(--font-size-xs);
  }
  
  .status-badge {
    font-size: 0.65rem;
    padding: 2px 8px;
  }
  
  .empty-state {
    padding: var(--space-8) var(--space-4);
  }
  
  .empty-state i {
    font-size: 2.5rem;
  }
  
  .pagination a,
  .pagination span {
    min-width: 30px;
    height: 30px;
    font-size: 0.7rem;
  }
}

/* Desktop Enhancements */
@media (min-width: 769px) {
  .customer-header {
    align-items: center;
  }
  
  .action-buttons {
    flex-direction: column;
    min-width: 200px;
  }
}

@media (min-width: 1024px) {
  .customer-details {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Animation for better UX */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card {
  animation: fadeInUp 0.5s ease-out;
}

/* Custom scrollbar for tables */
.orders-table-container::-webkit-scrollbar {
  height: 6px;
}

.orders-table-container::-webkit-scrollbar-track {
  background: var(--gray-200);
  border-radius: 3px;
}

.orders-table-container::-webkit-scrollbar-thumb {
  background: var(--primary-light);
  border-radius: 3px;
}

.orders-table-container::-webkit-scrollbar-thumb:hover {
  background: var(--primary);
}

/* Focus styles for accessibility */
.btn:focus,
.pagination a:focus {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* Loading state */
.loading {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid #f3f3f3;
  border-radius: 50%;
  border-top: 2px solid var(--primary);
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Print Styles */
@media print {
  .btn,
  .pagination {
    display: none;
  }
  
  .card {
    box-shadow: none;
    border: 1px solid var(--gray-400);
  }
  
  .orders-table-container {
    box-shadow: none;
    border: 1px solid var(--gray-400);
  }
  
  .orders-table thead {
    background: var(--gray-200) !important;
    color: var(--gray-800) !important;
  }
}
</style>

<?php include '../../includes/header.php'; ?>

<div class="admin-container">
    <div class="container">
        <h1>Customer Details</h1>

        <div class="card">
            <div class="customer-header">
                <div class="customer-info">
                    <div class="customer-name"><?= htmlspecialchars($customer['name']) ?></div>
                    <div class="customer-email"><?= htmlspecialchars($customer['email']) ?></div>

                    <div class="customer-details">
                        <div class="detail-item">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value"><?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registered</span>
                            <span class="detail-value"><?= date('F j, Y g:i A', strtotime($customer['created_at'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Orders</span>
                            <span class="detail-value"><?= $totalOrders ?></span>
                        </div>

                    </div>

                    <?php if (!empty($customer['address'])): ?>
                        <div class="detail-item" style="grid-column: 1 / -1; margin-top: 1rem;">
                            <span class="detail-label">Address</span>
                            <span class="detail-value"><?= htmlspecialchars($customer['address']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="action-buttons">
                    <a href="edit?id=<?= $customer['id'] ?>" class="btn btn-warning">Edit Customer</a>
                    <a href="list" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Order History</h2>

            <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td>Rs.<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td><?= $order['item_count'] ?> items</td>
                                <td>
                                    <a href="../order-details?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>No orders found for this customer</p>
                </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <!-- Previous Button -->
                    <?php if ($page > 1): ?>
                        <a href="?id=<?= $customerId ?>&page=<?= $page - 1 ?>">&laquo; Previous</a>
                    <?php else: ?>
                        <span class="disabled">&laquo; Previous</span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?id=<?= $customerId ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <?php if ($page < $totalPages): ?>
                        <a href="?id=<?= $customerId ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php else: ?>
                        <span class="disabled">Next &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>