<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
  redirect('../spk-st-wl');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  $brandId = (int)$_GET['id'];

  // Check if brand is used in any products
  $checkSql = "SELECT COUNT(*) FROM cakes WHERE brand_id = ?";
  $stmt = $conn->prepare($checkSql);
  $stmt->bind_param("i", $brandId);
  $stmt->execute();
  $result = $stmt->get_result();
  $count = $result->fetch_row()[0];

  if ($count > 0) {
    $_SESSION['error_message'] = 'Cannot delete brand as it is assigned to products.';
  } else {
    // Get brand image to delete
    $sql = "SELECT image FROM brands WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $brandId);
    $stmt->execute();
    $result = $stmt->get_result();
    $brand = $result->fetch_assoc();

    // Delete brand from database
    $sql = "DELETE FROM brands WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $brandId);

    if ($stmt->execute()) {
      // Delete image file if exists
      if (!empty($brand['image'])) {
        $imagePath = '../../assets/uploads/brands/' . $brand['image'];
        if (file_exists($imagePath)) {
          unlink($imagePath);
        }
      }

      $_SESSION['success_message'] = 'Brand deleted successfully!';
    } else {
      $_SESSION['error_message'] = 'Error deleting brand.';
    }
  }

  redirect('list');
}


// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Get total number of brands
$totalResult = $conn->query("SELECT COUNT(*) as total FROM brands");
$totalRow = $totalResult->fetch_assoc();
$totalBrands = $totalRow['total'];
$totalPages = ceil($totalBrands / $limit);

// Get brands with pagination
$brands = $conn->query("SELECT * FROM brands ORDER BY name LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
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
      <h1>Manage Brands</h1>
      <a href="add" class="btn btn-primary">Add New Brand</a>
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
            <th>Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($brands as $brand): ?>
            <tr>
              <td data-label="ID"><?= $brand['id'] ?></td>
              <td data-label="Image">
                <?php if (!empty($brand['image'])): ?>
                  <img src="../../assets/uploads/brands/<?= $brand['image'] ?>" alt="<?= htmlspecialchars($brand['name']) ?>" class="brand-thumbnail">
                <?php else: ?>
                  <span>No Image</span>
                <?php endif; ?>
              </td>
              <td data-label="Name"><?= htmlspecialchars($brand['name']) ?></td>
              <td data-label="Slug"><?= htmlspecialchars($brand['slug']) ?></td>
              <td data-label="Status"><?= $brand['is_active'] ? 'Active' : 'Inactive' ?></td>
              <td data-label="Actions">
                <a href="edit?id=<?= $brand['id'] ?>" class="btn-sm btn-primary">Edit</a>
                <a href="list?action=delete&id=<?= $brand['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure? This cannot be undone.')">Delete</a>
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