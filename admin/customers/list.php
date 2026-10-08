<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $customerId = (int)$_GET['id'];

    // Check if customer has orders
    $orderCount = $conn->query("SELECT COUNT(*) FROM orders WHERE customer_id = $customerId")->fetch_row()[0];

    if ($orderCount > 0) {
        $_SESSION['error_message'] = 'Cannot delete customer because they have orders.';
    } else {
        // Delete customer from database
        $sql = "DELETE FROM customers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $customerId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Customer deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error deleting customer.';
        }
    }

    redirect('list');
}


// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Get total number of customers
$totalResult = $conn->query("SELECT COUNT(*) as total FROM customers");
$totalRow = $totalResult->fetch_assoc();
$totalCustomers = $totalRow['total'];
$totalPages = ceil($totalCustomers / $limit);

// Get customers with pagination
$customers = $conn->query("
    SELECT c.*, COUNT(o.id) as order_count 
    FROM customers c 
    LEFT JOIN orders o ON c.id = o.customer_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);
?>




<style>

</style>

<?php include '../../includes/header.php'; ?>
<link rel="stylesheet" href="../../assets/css/admin-list-styles.css">

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Manage Customers</h1>
            <a href="add" class="btn btn-primary">Add New Customer</a>
        </div>

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
        <div class="table-responsive">
            <div class="scroll-hint">
                ← Scroll horizontally to view all columns →
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Orders</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?= htmlspecialchars($customer['name']) ?></span>
                                        <span class="customer-email"><?= htmlspecialchars($customer['email']) ?></span>
                                    </div>
                                </td>
                                <td><?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A' ?></td>
                                <td><?= !empty($customer['address']) ? htmlspecialchars(substr($customer['address'], 0, 50)) . (strlen($customer['address']) > 50 ? '...' : '') : 'N/A' ?></td>
                                <td>
                                    <span class="status-badge <?= $customer['order_count'] > 0 ? 'status-active' : '' ?>">
                                        <?= $customer['order_count'] ?> orders
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($customer['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view?id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                        <a href="edit?id=<?= $customer['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="list?action=delete&id=<?= $customer['id'] ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No customers found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

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
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

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

<?php include '../../includes/footer.php'; ?>