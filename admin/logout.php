<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== SECURITY ENHANCEMENTS START ===== //

// 1. Clear all session data
$_SESSION = array();

// 2. Destroy the session
session_destroy();

// 3. Clear session cookie (important for complete logout)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Regenerate session ID (security against session fixation)
session_regenerate_id(true);

// ===== SECURITY ENHANCEMENTS END ===== //

// Redirect to login page with success message
$_SESSION['logout_success'] = "You have been successfully logged out.";
header('Location: spk-st-wl');
exit();
?>