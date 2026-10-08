<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];

    // Check if category has subcategories
    $checkSql = "SELECT COUNT(*) FROM subcategories WHERE category_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];

    if ($count > 0) {
        $_SESSION['error_message'] = 'Cannot delete category with existing subcategories.';
    } else {
        // Delete category from database
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Category deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error deleting category.';
        }
    }

    redirect('list');
}



// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Get total number of categories
$totalResult = $conn->query("SELECT COUNT(*) as total FROM categories");
$totalRow = $totalResult->fetch_assoc();
$totalCategories = $totalRow['total'];
$totalPages = ceil($totalCategories / $limit);

// Get categories with pagination
$categories = $conn->query("SELECT * FROM categories ORDER BY name LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
?>


<style>



</style>




<?php
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin-list-styles.css">

<div class="admin-container" style="padding-top: 30px;">
    <div class="container">
        <div class="page-header">
            <h1>Manage Categories</h1>
            <a href="add" class="btn btn-primary">Add New Category</a>
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
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars($category['slug']) ?></td>
                            <td><?= $category['is_active'] ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <a href="edit?id=<?= $category['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="list?action=delete&id=<?= $category['id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure? This cannot be undone.')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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