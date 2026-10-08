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
$errors = [];

// Get category details
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    redirect('list');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    // Validate and sanitize input
    $category['name'] = sanitize($_POST['name']);
    $category['slug'] = generateSlug($_POST['slug'] ?: $_POST['name']);
    $category['description'] = sanitize($_POST['description']);
    $category['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($category['name'])) {
        $errors[] = 'Name is required';
    }
    
    // Check if slug already exists (excluding current category)
    $checkSlug = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $checkSlug->bind_param("si", $category['slug'], $categoryId);
    $checkSlug->execute();
    $checkSlug->store_result();
    
    if ($checkSlug->num_rows > 0) {
        $errors[] = 'Slug already exists';
    }
    
    $imageName = $category['image'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($imageName)) {
                $oldImagePath = '../../assets/uploads/categories/' . $imageName;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            $uploadPath = '../../assets/uploads/categories/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", 
            $category['name'], 
            $category['slug'], 
            $category['description'], 
            $imageName, 
            $category['is_active'], 
            $categoryId
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Category updated successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error updating category';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>

</style>


</head>
<body>
    

<?php
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin-edit-styles.css">

<div class="admin-container">
    <div class="container">
        <h1>Edit Category</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit?id=<?= $categoryId ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($category['name']) ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required value="<?= htmlspecialchars($category['slug']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($category['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <?php if (!empty($category['image'])): ?>
                    <img src="../../assets/uploads/categories/<?= $category['image'] ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="current-image">
                <?php else: ?>
                    <p>No image uploaded</p>
                <?php endif; ?>
                <label for="image">New Image (Leave blank to keep current)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="is_active" name="is_active" <?= $category['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Active</label>
            </div>

            <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
            <a href="list" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

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
            
            const imageGroup = document.querySelector('.form-group:nth-child(4)');
            imageGroup.insertBefore(preview, imageGroup.lastElementChild);
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    }
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.value === '<?= $category['slug'] ?>') {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
</script>
</body>
</html>