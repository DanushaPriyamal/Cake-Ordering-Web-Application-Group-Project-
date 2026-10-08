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
$errors = [];

// Get brand details
$sql = "SELECT * FROM brands WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $brandId);
$stmt->execute();
$result = $stmt->get_result();
$brand = $result->fetch_assoc();

if (!$brand) {
    redirect('list');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brand'])) {
    // Validate and sanitize input
    $brand['name'] = sanitize($_POST['name']);
    $brand['slug'] = generateSlug($_POST['slug'] ?: $_POST['name']);
    $brand['description'] = sanitize($_POST['description']);
    $brand['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($brand['name'])) {
        $errors[] = 'Name is required';
    }
    
    // Check if slug already exists (excluding current brand)
    $checkSlug = $conn->prepare("SELECT id FROM brands WHERE slug = ? AND id != ?");
    $checkSlug->bind_param("si", $brand['slug'], $brandId);
    $checkSlug->execute();
    $checkSlug->store_result();
    
    if ($checkSlug->num_rows > 0) {
        $errors[] = 'Slug already exists';
    }
    
    $imageName = $brand['image'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($imageName)) {
                $oldImagePath = '../../assets/uploads/brands/' . $imageName;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            $uploadPath = '../../assets/uploads/brands/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE brands SET name = ?, slug = ?, description = ?, image = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", 
            $brand['name'], 
            $brand['slug'], 
            $brand['description'], 
            $imageName, 
            $brand['is_active'], 
            $brandId
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Brand updated successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error updating brand';
        }
    }
}
?>
<style>

</style>


<?php
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin-edit-styles.css">
<div class="admin-container">
    <div class="container">
        <h1>Edit Brand</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit?id=<?= $brandId ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Brand Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($brand['name']) ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required value="<?= htmlspecialchars($brand['slug']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($brand['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <?php if (!empty($brand['image'])): ?>
                    <img src="../../assets/uploads/brands/<?= $brand['image'] ?>" alt="<?= htmlspecialchars($brand['name']) ?>" class="current-image">
                <?php else: ?>
                    <p>No image uploaded</p>
                <?php endif; ?>
                <label for="image">New Image (Leave blank to keep current)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="is_active" name="is_active" <?= $brand['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Active</label>
            </div>

            <button type="submit" name="update_brand" class="btn btn-primary">Update Brand</button>
            <a href="list" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>





<script>
// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.createElement('div');
    preview.className = 'image-upload-preview';
    
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        const reader = new FileReader();
        
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.maxWidth = '100%';
            preview.appendChild(img);
            
            const currentImageGroup = document.querySelector('.form-group:nth-child(4)');
            currentImageGroup.insertBefore(preview, currentImageGroup.lastElementChild);
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    }
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.value === '<?= $brand['slug'] ?>') {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>