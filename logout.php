<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Destroy customer session
if (isset($_SESSION['customer_id'])) {
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_email']);
    session_destroy();
}

// Redirect to home page
redirect('index');




































