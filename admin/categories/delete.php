<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

$categoryId = (int)$_GET['id'];

// Get category image to delete
$sql = "SELECT image FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

// Check if category has subcategories
$checkSql = "SELECT COUNT(*) FROM subcategories WHERE category_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_row()[0];

if ($count > 0) {
    $_SESSION['error_message'] = 'Cannot delete category with existing subcategories.';
    redirect('list');
}

// Delete category from database
$sql = "DELETE FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);

if ($stmt->execute()) {
    // Delete image file if exists
    if (!empty($category['image'])) {
        $imagePath = '../../assets/uploads/categories/' . $category['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $_SESSION['success_message'] = 'Category deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Error deleting category.';
}

redirect('list');
?>