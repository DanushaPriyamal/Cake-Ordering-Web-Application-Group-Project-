<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Allow both ID and slug for product lookup
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $cake = getCakeById($productId);
} elseif (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $productSlug = $_GET['slug'];
    $cake = getCakeBySlug($productSlug);
} else {
    echo json_encode(['error' => 'Invalid product identifier']);
    exit;
}

if (!$cake) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Prepare response data
$response = [
    'id' => $cake['id'],
    'name' => $cake['name'],
    'slug' => $cake['slug'],
    'price' => $cake['price'],
    'description' => $cake['description'] ?? 'No description available',
    'image' => getCakeImage($cake['image']),
    'brand_name' => $cake['brand_name'] ?? '',
    'brand_slug' => $cake['brand_slug'] ?? '',
    'category_name' => $cake['category_name'] ?? '',
    'category_slug' => $cake['category_slug'] ?? '',
    'subcategory_name' => $cake['subcategory_name'] ?? '',
    'subcategory_slug' => $cake['subcategory_slug'] ?? ''
];

echo json_encode($response);