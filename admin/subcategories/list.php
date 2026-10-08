<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $subcategoryId = (int)$_GET['id'];

    // Check if subcategory is used in any products
    $checkSql = "SELECT COUNT(*) FROM cakes WHERE subcategory_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];

    if ($count > 0) {
        $_SESSION['error_message'] = 'Cannot delete subcategory as it is assigned to products.';
    } else {
        // Get subcategory image to delete
        $sql = "SELECT image FROM subcategories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $subcategory = $result->fetch_assoc();

        // Delete subcategory from database
        $sql = "DELETE FROM subcategories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategoryId);

        if ($stmt->execute()) {
            // Delete image file if exists
            if (!empty($subcategory['image'])) {
                $imagePath = '../../assets/uploads/subcategories/' . $subcategory['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $_SESSION['success_message'] = 'Subcategory deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error deleting subcategory.';
        }
    }

    redirect('list');
}


// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Get total number of subcategories
$totalResult = $conn->query("SELECT COUNT(*) as total FROM subcategories");
$totalRow = $totalResult->fetch_assoc();
$totalSubcategories = $totalRow['total'];
$totalPages = ceil($totalSubcategories / $limit);

// Get subcategories with pagination
$subcategories = $conn->query("SELECT s.*, c.name as category_name 
                             FROM subcategories s
                             JOIN categories c ON s.category_id = c.id
                             ORDER BY c.name, s.name
                             LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
?>

<style>

</style>


<?php
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin-list-styles.css">

<div class="admin-container">
    <div class="container">
        <div class="page-header">
            <h1>Manage Subcategories</h1>
            <a href="add" class="btn btn-primary">Add New Subcategory</a>
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
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subcategories as $subcategory): ?>
                        <tr>
                            <td><?= $subcategory['id'] ?></td>
                            <td><?= htmlspecialchars($subcategory['name']) ?></td>
                            <td><?= htmlspecialchars($subcategory['category_name']) ?></td>
                            <td><?= htmlspecialchars($subcategory['slug']) ?></td>
                            <td><span class="status-badge status-<?= $subcategory['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $subcategory['is_active'] ? 'Active' : 'Inactive' ?>
                                </span></td>
                            <td>
                                <div class="action-btns">
                                    <a href="edit?id=<?= $subcategory['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="list?action=delete&id=<?= $subcategory['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure? This cannot be undone.')">Delete</a>
                                </div>
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