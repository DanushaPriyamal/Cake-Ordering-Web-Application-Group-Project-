<?php
require_once 'config.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to read (emoji,sinhala like) 
$conn->set_charset("utf8mb4");
?>