<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

$errors = [];
$category = [
    'name' => '',
    'slug' => '',
    'description' => '',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    // Validate and sanitize input
    $category['name'] = sanitize($_POST['name']);
    $category['slug'] = generateSlug($_POST['slug'] ?: $_POST['name']);
    $category['description'] = sanitize($_POST['description']);
    $category['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($category['name'])) {
        $errors[] = 'Name is required';
    }
    
    // Check if slug already exists
    $checkSlug = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $checkSlug->bind_param("s", $category['slug']);
    $checkSlug->execute();
    $checkSlug->store_result();
    
    if ($checkSlug->num_rows > 0) {
        $errors[] = 'Slug already exists';
    }
    
    // Handle image upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
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
    
    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO categories (name, slug, description, image, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $category['name'], 
            $category['slug'], 
            $category['description'], 
            $imageName, 
            $category['is_active']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Category added successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error adding category to database';
            
            // Delete uploaded image if database insert failed
            if (!empty($imageName)) {
                $imagePath = '../../assets/uploads/categories/' . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
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
<link rel="stylesheet" href="../../assets/css/admin-add-styles.css">

<div class="admin-container">
    <div class="container">
        <h1>Add New Category</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="form-card">
        <form method="post" action="add" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($category['name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($category['slug']) ?>">
                <small>Leave blank to auto-generate from name</small>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($category['description']) ?></textarea>
            </div>
            
            <div class="form-group">
    <label for="image">Image</label>
    <div class="file-upload-wrapper">
        <div class="file-upload">
            <div class="file-upload-icon">↑</div>
            <div class="file-upload-text">Drag & drop your image here</div>
            <div class="file-upload-hint">or click to browse (JPG, PNG, GIF)</div>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="file-upload-preview" id="image-preview"></div>
    </div>
</div>
            
<div class="checkbox-group">
    <input type="checkbox" id="is_active" name="is_active" <?= $category['is_active'] ? 'checked' : '' ?>>
    <label for="is_active">Active Category</label>
</div>
<div class="btn-group">
    <button type="submit" name="add_category" class="btn btn-primary">
        <span>Add Category</span>
    </button>
    <a href="list" class="btn btn-secondary">
        <span>Cancel</span>
    </a>
</div>
        </form>
    </div>
</div>
</div>
<script>
// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width:100%">`;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// Enhanced slug generator with animation
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    const slugWrapper = slugInput.parentElement;
    
    if (!slugInput.value) {
        slugWrapper.classList.add('slug-generator', 'active');
        setTimeout(() => {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugWrapper.classList.remove('active');
        }, 800);
    }
});

// Add floating label effect
document.querySelectorAll('.form-control').forEach(input => {
    if (input.value) {
        input.previousElementSibling.classList.add('floating');
    }
    input.addEventListener('input', function() {
        if (this.value) {
            this.previousElementSibling.classList.add('floating');
        } else {
            this.previousElementSibling.classList.remove('floating');
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>

</body>
</html>