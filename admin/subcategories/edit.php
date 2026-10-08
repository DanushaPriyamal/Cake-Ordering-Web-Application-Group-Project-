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
$errors = [];

// Get subcategory details
$sql = "SELECT * FROM subcategories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategoryId);
$stmt->execute();
$result = $stmt->get_result();
$subcategory = $result->fetch_assoc();

if (!$subcategory) {
    redirect('list');
}

// Get all active categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subcategory'])) {
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
    
    // Check if slug already exists (excluding current subcategory)
    $checkSlug = $conn->prepare("SELECT id FROM subcategories WHERE slug = ? AND id != ?");
    $checkSlug->bind_param("si", $subcategory['slug'], $subcategoryId);
    $checkSlug->execute();
    $checkSlug->store_result();
    
    if ($checkSlug->num_rows > 0) {
        $errors[] = 'Slug already exists';
    }
    
    $imageName = $subcategory['image'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($imageName)) {
                $oldImagePath = '../../assets/uploads/subcategories/' . $imageName;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

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

    if (empty($errors)) {
        $sql = "UPDATE subcategories SET name = ?, slug = ?, category_id = ?, description = ?, image = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissii", 
            $subcategory['name'], 
            $subcategory['slug'], 
            $subcategory['category_id'], 
            $subcategory['description'], 
            $imageName, 
            $subcategory['is_active'], 
            $subcategoryId
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Subcategory updated successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error updating subcategory';
        }
    }
}?>
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
        <h1>Edit Subcategory</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit?id=<?= $subcategoryId ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Subcategory Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($subcategory['name']) ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required value="<?= htmlspecialchars($subcategory['slug']) ?>">
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
                <label>Current Image</label>
                <?php if (!empty($subcategory['image'])): ?>
                    <img src="../../assets/uploads/subcategories/<?= $subcategory['image'] ?>" alt="<?= htmlspecialchars($subcategory['name']) ?>" class="current-image">
                <?php else: ?>
                    <p>No image uploaded</p>
                <?php endif; ?>
                <label for="image">New Image (Leave blank to keep current)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="is_active" name="is_active" <?= $subcategory['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Active</label>
            </div>

            <button type="submit" name="update_subcategory" class="btn btn-primary">Update Subcategory</button>
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
            
            const currentImageGroup = document.querySelector('.form-group:nth-child(5)');
            currentImageGroup.insertBefore(preview, currentImageGroup.lastElementChild);
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    }
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value || slugInput.value === '<?= $subcategory['slug'] ?>') {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
</script>
</body>
</html>