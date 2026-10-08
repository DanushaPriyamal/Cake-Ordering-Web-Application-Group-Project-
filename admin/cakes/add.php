<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

$errors = [];
$cake = [
    'name' => '',
    'description' => '',
    'price' => '',
    'category_id' => '',
    'subcategory_id' => '',
    'brand_id' => '',
    'is_featured' => 0,
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cake'])) {
    // Validate inputs
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

    // Handle image upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            $uploadPath = '../../assets/uploads/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    } else {
        $errors[] = 'Image is required';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        // In the POST handling section, after validating inputs:
        $cake['slug'] = generateSlug($cake['name']);

        // In the SQL query:
        $sql = "INSERT INTO cakes (name, slug, description, price, image, category_id, subcategory_id, brand_id, is_featured, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssdsiiiii",
            $cake['name'],
            $cake['slug'],
            $cake['description'],
            $cake['price'],
            $imageName,
            $cake['category_id'],
            $cake['subcategory_id'],
            $cake['brand_id'],
            $cake['is_featured'],
            $cake['is_active']
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Product added successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error adding product to database';

            // Delete uploaded image if database insert failed
            if (!empty($imageName)) {
                $imagePath = '../../assets/uploads/' . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }
}

// Get categories, brands for dropdowns
$categories = getCategories(false);
$brands = getBrands(false);

// Get subcategories if category is already selected
$subcategories = [];
if (!empty($cake['category_id'])) {
    $subcategories = getSubcategories($cake['category_id'], false);
}
?>

<style>
 

</style>
    <?php include '../../includes/header.php'; ?>
    <link rel="stylesheet" href="../../assets/css/admin-add-styles.css">

    <div class="admin-container">
        <div class="container">
            <h1>Add New Product</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="add" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name" class="required">Product Name</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($cake['name']) ?>">
                </div>

                <div class="form-group">
                    <label for="description" class="required">Description</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($cake['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price" class="required">Price (Rs)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($cake['price']) ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required onchange="loadSubcategories(this.value)">
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
                    <select id="subcategory_id" name="subcategory_id" required>
                        <option value="">Select Subcategory</option>
                        <?php
                        // Pre-populate if editing existing product
                        if (!empty($cake['category_id']) && !empty($subcategories)):
                            foreach ($subcategories as $subcategory):
                        ?>
                                <option value="<?= $subcategory['id'] ?>"
                                    <?= $cake['subcategory_id'] == $subcategory['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory['name']) ?>
                                </option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                    <span id="subcategoryLoading" class="loading" style="display: none;"></span>
                    <div id="subcategoryError" class="error-message" style="color: #ff4757; margin-top: 5px;"></div>
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
                    <label for="image" class="required">Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <small style="color: var(--text-muted);">Allowed types: JPG, PNG, GIF</small>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="is_featured" name="is_featured" <?= $cake['is_featured'] ? 'checked' : '' ?>>
                    <label for="is_featured">Featured Product</label>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="is_active" name="is_active" <?= $cake['is_active'] ? 'checked' : '' ?>>
                    <label for="is_active">Active</label>
                </div>

                <button type="submit" name="add_cake" class="btn btn-primary">Add Product</button>
                <a href="list" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        // Make sure SITE_URL is available in JavaScript
        const SITE_URL = '<?php echo SITE_URL; ?>';

        function loadSubcategories(categoryId) {
            const subcategorySelect = document.getElementById('subcategory_id');
            const loadingIndicator = document.getElementById('subcategoryLoading');
            const errorDisplay = document.getElementById('subcategoryError');

            // Reset previous state
            errorDisplay.innerHTML = '';
            errorDisplay.style.display = 'none';
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';
            subcategorySelect.disabled = true;
            loadingIndicator.style.display = 'inline-block';

            if (!categoryId) {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                subcategorySelect.disabled = false;
                loadingIndicator.style.display = 'none';
                return;
            }

            fetch(`${SITE_URL}/api/get_subcategories.php?category_id=${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status} status`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';

                        data.data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;

                            // Preselect if this was the previously selected subcategory
                            if (subcategory.id == <?= json_encode($cake['subcategory_id'] ?? '') ?>) {
                                option.selected = true;
                            }

                            subcategorySelect.appendChild(option);
                        });
                    } else {
                        throw new Error(data.error || 'No subcategories found');
                    }
                })
                .catch(error => {
                    console.error('Error loading subcategories:', error);
                    subcategorySelect.innerHTML = '<option value="">Error loading</option>';
                    errorDisplay.textContent = error.message;
                    errorDisplay.style.display = 'block';

                    // Add retry button
                    const retryBtn = document.createElement('button');
                    retryBtn.textContent = 'Retry';
                    retryBtn.className = 'btn btn-sm btn-primary';
                    retryBtn.style.marginLeft = '10px';
                    retryBtn.onclick = () => loadSubcategories(categoryId);
                    errorDisplay.appendChild(retryBtn);
                })
                .finally(() => {
                    subcategorySelect.disabled = false;
                    loadingIndicator.style.display = 'none';
                });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');

            // Load subcategories if category is already selected
            if (categorySelect.value) {
                loadSubcategories(categorySelect.value);
            }

            // Handle category changes
            categorySelect.addEventListener('change', function() {
                loadSubcategories(this.value);
            });

            // Debug output
            console.log('Initial category value:', categorySelect.value);
        });
    </script>

    <?php include '../../includes/footer.php'; ?>
