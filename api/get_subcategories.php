<?php
// api/get_subcategories.php

// Define the root path
define('ROOT_PATH', dirname(__DIR__));

// Include configuration files
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

try {
    // Verify category_id is provided and valid
    if (!isset($_GET['category_id']) || !is_numeric($_GET['category_id'])) {
        throw new Exception('Missing or invalid category ID');
    }

    $categoryId = (int)$_GET['category_id'];
    
    // Check database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE category_id = ? AND is_active = 1");
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $categoryId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $subcategories = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $subcategories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'path_debug' => [
            'root_path' => ROOT_PATH,
            'config_path' => ROOT_PATH . '/includes/config.php',
            'file_exists' => file_exists(ROOT_PATH . '/includes/config.php')
        ]
    ]);
}