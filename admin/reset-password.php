<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
$pageTitle = "reset-password";


$message = '';
$error = '';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = 'Invalid password reset link.';
}

$token = sanitize($_GET['token']);
$tokenHash = hash('sha256', $token);

// Verify token
$sql = "SELECT id, reset_token_expiry FROM admins WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    $error = 'Invalid password reset link.';
} elseif (strtotime($admin['reset_token_expiry']) <= time()) {
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
        $sql = "UPDATE admins SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $admin['id']);
        
        if ($stmt->execute()) {
            $message = 'Your password has been reset successfully. You can now <a href="spk-st-wl">login</a> with your new password.';
        } else {
            $error = 'Error resetting your password. Please try again.';
        }
    }
}

?>    
    <style>
:root {
  --primary: #6366f1;
  --primary-hover: #4f46e5;
  --success: #10b981;
  --error: #ef4444;
  --light: #f9fafb;
  --dark: #111827;
  --gray: #6b7280;
  --light-gray: #e5e7eb;
  --radius: 0.5rem;
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background-color: #f3f4f6;
  color: var(--dark);
  line-height: 1.5;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
}

.login-container {
  width: 100%;
  max-width: 28rem;
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow-md);
  padding: 2rem;
  margin: 0 auto;
}

h1 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark);
  text-align: center;
  margin-bottom: 1.5rem;
}

/* Alert Styles */
.alert {
  padding: 0.75rem 1rem;
  margin-bottom: 1.5rem;
  border-radius: var(--radius);
  font-size: 0.875rem;
  text-align: center;
}

.alert-success {
  background-color: rgba(16, 185, 129, 0.1);
  color: var(--success);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.alert-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: var(--error);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

/* Form Styles */
.form-group {
  margin-bottom: 1.25rem;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--dark);
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  font-size: 0.875rem;
  border: 1px solid var(--light-gray);
  border-radius: var(--radius);
  transition: var(--transition);
  background-color: var(--light);
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
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
  padding: 0.75rem;
  background-color: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  margin: 1rem 0;
}

.btn:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.btn:active {
  transform: translateY(0);
}

.btn:disabled {
  background-color: var(--gray);
  cursor: not-allowed;
}

/* Link Styles */
.back-to-login {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.875rem;
}

.back-to-login a {
  color: var(--gray);
  font-weight: 500;
  transition: var(--transition);
}

.back-to-login a:hover {
  color: var(--primary);
}

/* Responsive Design */
@media (max-width: 640px) {
  .login-container {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 1.25rem;
  }
}

@media (max-width: 400px) {
  .login-container {
    padding: 1.25rem;
  }
  
  .btn {
    padding: 0.625rem;
  }
  
  .form-group input {
    padding: 0.625rem;
  }
}

/* Password toggle visibility */
.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 2.25rem;
  background: none;
  border: none;
  color: var(--gray);
  cursor: pointer;
}

.password-toggle:hover {
  color: var(--primary);
}</style>

<?php
include '../includes/header.php';
?> 
    <div class="login-container">
        <h1>Reset Password</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($message) && empty($error)): ?>
            <form method="post" action="reset-password?token=<?php echo urlencode($token); ?>">
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
        
        <div class="back-to-login">
            <a href="spk-st-wl">Back to Login</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>