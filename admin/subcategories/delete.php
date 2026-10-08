<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

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
    redirect('list');
}

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

redirect('list');
?>