<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

$errors = [];
$brand = [
    'name' => '',
    'slug' => '',
    'description' => '',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    // Validate and sanitize input
    $brand['name'] = sanitize($_POST['name']);
    $brand['slug'] = generateSlug($_POST['slug'] ?: $_POST['name']);
    $brand['description'] = sanitize($_POST['description']);
    $brand['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($brand['name'])) {
        $errors[] = 'Name is required';
    }
    
    // Check if slug already exists
    $checkSlug = $conn->prepare("SELECT id FROM brands WHERE slug = ?");
    $checkSlug->bind_param("s", $brand['slug']);
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
            $uploadPath = '../../assets/uploads/brands/' . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO brands (name, slug, description, image, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $brand['name'], 
            $brand['slug'], 
            $brand['description'], 
            $imageName, 
            $brand['is_active']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Brand added successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error adding brand to database';
            
            // Delete uploaded image if database insert failed
            if (!empty($imageName)) {
                $imagePath = '../../assets/uploads/brands/' . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }
}
?>

    <style>

    </style>

<?php
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/admin-add-styles.css">
<div class="admin-container">
    <div class="container">
        <h1>Add New Brand</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="add" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Brand Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($brand['name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($brand['slug']) ?>">
                <small>Leave blank to auto-generate from name</small>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($brand['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Allowed types: JPG, PNG, GIF</small>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="is_active" name="is_active" <?= $brand['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Active</label>
            </div>
            
            <button type="submit" name="add_brand" class="btn btn-primary">Add Brand</button>
            <a href="list" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>

