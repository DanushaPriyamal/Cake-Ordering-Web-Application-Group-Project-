<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

$errors = [];
$subcategory = [
    'name' => '',
    'slug' => '',
    'category_id' => '',
    'description' => '',
    'is_active' => 1
];

// Get all active categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subcategory'])) {
    // Validate and sanitize input
    $subcategory['name'] = sanitize($_POST['name']);
    $subcategory['slug'] = generateSlug($_POST['slug'] ?: $_POST['name']);
    $subcategory['category_id'] = (int)$_POST['category_id'];
    $subcategory['description'] = sanitize($_POST['description']);
    $subcategory['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($subcategory['name'])) {
        $errors[] = 'Name is required';
    }
    
    if (empty($subcategory['category_id'])) {
        $errors[] = 'Category is required';
    }
    
    // Check if slug already exists
    $checkSlug = $conn->prepare("SELECT id FROM subcategories WHERE slug = ?");
    $checkSlug->bind_param("s", $subcategory['slug']);
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
            $uploadPath = '../../assets/uploads/subcategories/' . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO subcategories (name, slug, category_id, description, image, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissi", 
            $subcategory['name'], 
            $subcategory['slug'], 
            $subcategory['category_id'], 
            $subcategory['description'], 
            $imageName, 
            $subcategory['is_active']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Subcategory added successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error adding subcategory to database';
            
            // Delete uploaded image if database insert failed
            if (!empty($imageName)) {
                $imagePath = '../../assets/uploads/subcategories/' . $imageName;
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
        <h1>Add New Subcategory</h1>
        
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
                <label for="name">Subcategory Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($subcategory['name']) ?>">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($subcategory['slug']) ?>">
                <small>Leave blank to auto-generate from name</small>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $subcategory['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($subcategory['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Allowed types: JPG, PNG, GIF</small>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="is_active" name="is_active" <?= $subcategory['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Active</label>
            </div>
            
            <button type="submit" name="add_subcategory" class="btn btn-primary">Add Subcategory</button>
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

</body>
</html>