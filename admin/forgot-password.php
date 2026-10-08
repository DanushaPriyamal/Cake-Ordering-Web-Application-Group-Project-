<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$message = '';
$error = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_request'])) {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        // Check if email exists
        $sql = "SELECT id, email FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Generate reset token
            $token = generateToken();
            $tokenHash = hash('sha256', $token);
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            
            // Store token in database
            $sql = "UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $tokenHash, $expiry, $admin['id']);
            
            if ($stmt->execute()) {
                // Send reset email
                $resetLink = SITE_URL . "/admin/reset-password?token=" . urlencode($token);
                $subject = "Password Reset Request";
                $message = "Hello,<br><br>"
                          . "We received a request to reset your password. Please click the link below to reset your password:<br><br>"
                          . "<a href='$resetLink'>$resetLink</a><br><br>"
                          . "This link will expire in 1 hour.<br><br>"
                          . "If you didn't request this, please ignore this email.<br><br>"
                          . "Thanks,<br>"
                          . SITE_NAME;
                
                if (sendEmail($email, $subject, $message)) {
                    $message = 'Password reset link has been sent to your email. Please check your inbox.';
                } else {
                    $error = 'Failed to send reset email. Please try again.';
                }
            } else {
                $error = 'Error processing your request. Please try again.';
            }
        } else {
            $error = 'No account found with that email address.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>/* assets/css/admin.css */
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
}</style>
</head>
<body>
    <div class="login-container">
        <h1>Forgot Password</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="forgot-password">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" name="reset_request" class="btn btn-primary">Reset Password</button>
            
            <div class="back-to-login">
                <a href="spk-st-wl">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>