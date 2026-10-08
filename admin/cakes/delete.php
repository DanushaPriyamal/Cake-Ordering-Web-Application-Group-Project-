<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

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
?>