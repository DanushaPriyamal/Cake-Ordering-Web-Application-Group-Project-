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
$errors = [];

// Get cake details
$sql = "SELECT * FROM cakes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cakeId);
$stmt->execute();
$result = $stmt->get_result();
$cake = $result->fetch_assoc();

if (!$cake) {
    redirect('list');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cake'])) {
    $cake['name'] = sanitize($_POST['name']);
    $cake['description'] = str_replace(['\\r\\n', '\\n', '\\r'], "\n", sanitize($_POST['description']));
    $cake['price'] = (float)$_POST['price'];
    $cake['category_id'] = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $cake['subcategory_id'] = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
    $cake['brand_id'] = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $cake['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $cake['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if (empty($cake['name'])) $errors[] = 'Name is required';
    if (empty($cake['description'])) $errors[] = 'Description is required';
    if ($cake['price'] <= 0) $errors[] = 'Price must be greater than 0';

    $imageName = $cake['image'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($imageName)) {
                $oldImagePath = '../../assets/uploads/' . $imageName;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            $uploadPath = '../../assets/uploads/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }

    if (empty($errors)) {
   // In the POST handling section, after validating inputs:
$cake['slug'] = generateSlug($cake['name']);

// In the SQL query:
$sql = "UPDATE cakes SET name = ?, slug = ?, description = ?, price = ?, image = ?, category_id = ?, subcategory_id = ?, brand_id = ?, is_featured = ?, is_active = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdsiiiiii", 
    $cake['name'], 
    $cake['slug'], 
    $cake['description'], 
    $cake['price'], 
    $imageName, 
    $cake['category_id'], 
    $cake['subcategory_id'], 
    $cake['brand_id'], 
    $cake['is_featured'], 
    $cake['is_active'], 
    $cakeId
);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Cake updated successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error updating cake';
        }
    }
}

// Get categories, brands for dropdowns
$categories = getCategories(false);
$brands = getBrands(false);
$subcategories = $cake['category_id'] ? getSubcategories($cake['category_id'], false) : [];

?>
<style>


</style>
    <?php

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/admin-edit-styles.css">


<div class="admin-container">
    <div class="container">
        <h1 >Edit Product</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit?id=<?= $cakeId ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($cake['name']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required><?= htmlspecialchars($cake['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (Rs) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($cake['price']) ?>">
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" onchange="loadSubcategories(this.value)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $cake['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory_id">Subcategory</label>
                <select id="subcategory_id" name="subcategory_id">
                    <option value="">Select Subcategory</option>
                    <?php foreach ($subcategories as $subcategory): ?>
                        <option value="<?= $subcategory['id'] ?>" <?= $cake['subcategory_id'] == $subcategory['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subcategory['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand_id">Brand</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand['id'] ?>" <?= $cake['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
    <label>Current Image</label>
    <div class="current-image-container">
        <img src="../../<?= getCakeImage($cake['image']) ?>" alt="<?= htmlspecialchars($cake['name']) ?>" class="current-image">
    </div>
    <div class="file-input-wrapper">
        <label class="file-input-label">
            <i class="fas fa-cloud-upload-alt"></i>
            <span class="file-input-text">Choose new image (Leave blank to keep current)</span>
        </label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>
</div>

<div class="checkbox-group">
    <div class="checkbox">
        <input type="checkbox" id="is_featured" name="is_featured" <?= $cake['is_featured'] ? 'checked' : '' ?>>
        <label for="is_featured">Featured Product</label>
    </div>
    <div class="checkbox">
        <input type="checkbox" id="is_active" name="is_active" <?= $cake['is_active'] ? 'checked' : '' ?>>
        <label for="is_active">Active</label>
    </div>
</div>

<div class="form-actions">
    <button type="submit" name="update_cake" class="btn btn-primary">
        <i class="fas fa-save"></i> Update Product
    </button>
    <a href="list" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
        </form>
    </div>
</div>

<script>
function loadSubcategories(categoryId) {
    if (categoryId) {
        fetch(`../../api/get_subcategories.php?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                const subcategorySelect = document.getElementById('subcategory_id');
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    subcategorySelect.appendChild(option);
                });
                
                // Set previously selected subcategory if available
                const prevSubcategoryId = <?= $cake['subcategory_id'] ?: 'null' ?>;
                if (prevSubcategoryId) {
                    subcategorySelect.value = prevSubcategoryId;
                }
            });
    } else {
        document.getElementById('subcategory_id').innerHTML = '<option value="">Select Subcategory</option>';
    }
}
</script>

<?php include '../../includes/footer.php'; ?>

