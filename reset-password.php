<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$message = '';
$error = '';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = 'Invalid password reset link.';
}

$token = sanitize($_GET['token']);
$tokenHash = hash('sha256', $token);

// Verify token
$sql = "SELECT id, reset_token_expiry FROM customers WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    $error = 'Invalid password reset link.';
} elseif (strtotime($customer['reset_token_expiry']) <= time()) {
    $error = 'Password reset link has expired. Please request a new one.';
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $password = sanitize($_POST['password']);
    $confirmPassword = sanitize($_POST['confirm_password']);
    
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Update password
        $hashedPassword = hashPassword($password);
        $sql = "UPDATE customers SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $customer['id']);
        
        if ($stmt->execute()) {
            $message = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
        } else {
            $error = 'Error resetting your password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* assets/css/style.css */
:root {
  --primary: #4361ee;
  --primary-dark: #3a56d4;
  --success: #4cc9f0;
  --error: #f72585;
  --light: #f8f9fa;
  --dark: #212529;
  --gray: #6c757d;
  --light-gray: #e9ecef;
  --border-radius: 10px;
  --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: #f5f7fa;
  color: var(--dark);
  line-height: 1.6;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  flex: 1;
}

/* Header Styles */
header {
  background-color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 1rem 0;
}

/* Main Content */
main {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin-top : 50px;
  padding: 2rem 0;
}

h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: var(--dark);
  text-align: center;
}

/* Alert Messages */
.alert {
  padding: 1rem;
  margin-bottom: 1.5rem;
  border-radius: var(--border-radius);
  font-size: 0.9rem;
  text-align: center;
  width: 100%;
  max-width: 500px;
}

.alert-success {
  background-color: rgba(76, 201, 240, 0.1);
  color: var(--success);
  border: 1px solid rgba(76, 201, 240, 0.3);
}

.alert-danger {
  background-color: rgba(247, 37, 133, 0.1);
  color: var(--error);
  border: 1px solid rgba(247, 37, 133, 0.3);
}

/* Form Styles */
.auth-form {
  width: 100%;
  max-width: 500px;
  background: white;
  padding: 2.5rem;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  margin: 1.5rem 0;
}

.form-group {
  margin-bottom: 1.5rem;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--dark);
}

.form-group input {
  width: 100%;
  padding: 0.9rem 1rem;
  font-size: 1rem;
  border: 1px solid var(--light-gray);
  border-radius: var(--border-radius);
  transition: var(--transition);
  background-color: white;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
}

/* Password Strength Indicator */
.password-strength {
  height: 4px;
  background: var(--light-gray);
  border-radius: 2px;
  margin-top: 0.5rem;
  overflow: hidden;
}

.strength-meter {
  height: 100%;
  width: 0;
  background: var(--error);
  transition: width 0.3s ease;
}

/* Button Styles */
.btn {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 1rem;
  background-color: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  margin: 1rem 0;
}

.btn:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
}

.btn:active {
  transform: translateY(0);
}

/* Link Styles */
.auth-links {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
}

.auth-links a {
  color: var(--gray);
  font-weight: 500;
  transition: var(--transition);
}

.auth-links a:hover {
  color: var(--primary);
}

/* Footer Styles */
footer {
  background-color: var(--dark);
  color: white;
  padding: 1.5rem 0;
  text-align: center;
  margin-top: auto;
}

/* Password Toggle Visibility */
.password-toggle {
  position: absolute;
  right: 1rem;
  top: 2.7rem;
  background: none;
  border: none;
  color: var(--gray);
  cursor: pointer;
  font-size: 1.1rem;
}

.password-toggle:hover {
  color: var(--primary);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .container {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 1.75rem;
  }
  
  .auth-form {
    padding: 2rem;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 1rem;
  }
  
  h1 {
    font-size: 1.5rem;
  }
  
  .auth-form {
    padding: 1.5rem;
  }
  
  .form-group input,
  .btn {
    padding: 0.8rem;
  }
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Reset Password</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($message) && empty($error)): ?>
            <form method="post" action="reset-password?token=<?php echo urlencode($token); ?>" class="auth-form">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <div class="auth-links">
            <a href="login">Back to Login</a>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>