<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
  redirect('../spk-st-wl');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
  $cakeId = (int)$_GET['id'];

  // Get cake image to delete
  $sql = "SELECT image FROM cakes WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $cakeId);
  $stmt->execute();
  $result = $stmt->get_result();
  $cake = $result->fetch_assoc();

  // Delete cake from database
  $sql = "DELETE FROM cakes WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $cakeId);

  if ($stmt->execute()) {
    // Delete image file if exists
    if (!empty($cake['image'])) {
      $imagePath = '../../assets/uploads/' . $cake['image'];
      if (file_exists($imagePath)) {
        unlink($imagePath);
      }
    }

    $_SESSION['success_message'] = 'Cake deleted successfully!';
  } else {
    $_SESSION['error_message'] = 'Error deleting cake.';
  }

  redirect('list');
}


// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;

// Get total number of cakes
$totalResult = $conn->query("SELECT COUNT(*) as total FROM cakes");
$totalRow = $totalResult->fetch_assoc();
$totalCakes = $totalRow['total'];
$totalPages = ceil($totalCakes / $limit);

// Get cakes with pagination
$cakes = $conn->query("SELECT c.*, cat.name as category_name, b.name as brand_name 
                      FROM cakes c
                      LEFT JOIN categories cat ON c.category_id = cat.id
                      LEFT JOIN brands b ON c.brand_id = b.id
                      ORDER BY c.created_at DESC
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
      <h1>Manage Products</h1>
      <a href="add" class="btn btn-primary">Add New Product</a>
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
            <th>Price</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Featured</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cakes as $cake): ?>
            <tr>
              <td><?= $cake['id'] ?></td>
              <td>
                <img src="../../<?= getCakeImage($cake['image']) ?>"
                  alt="<?= htmlspecialchars($cake['name']) ?>"
                  class="cake-thumbnail">
              </td>
              <td><?= htmlspecialchars($cake['name']) ?></td>
              <td>Rs.<?= number_format($cake['price'], 2) ?></td>
              <td><?= !empty($cake['category_name']) ? htmlspecialchars($cake['category_name']) : 'N/A' ?></td>
              <td><?= !empty($cake['brand_name']) ? htmlspecialchars($cake['brand_name']) : 'N/A' ?></td>
              <td><span class="status-badge <?= $cake['is_featured'] ? 'status-featured' : '' ?>"><?= $cake['is_featured'] ? 'Yes' : 'No' ?></span></td>
              <td><span class="status-badge <?= $cake['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $cake['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td>
                <a href="edit?id=<?= $cake['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="list?action=delete&id=<?= $cake['id'] ?>"
                  class="btn btn-sm btn-danger"
                  onclick="return confirm('Are you sure?')">Delete</a>
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