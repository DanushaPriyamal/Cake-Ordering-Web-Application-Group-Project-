<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

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
    redirect('list');
}

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

redirect('list');
?>