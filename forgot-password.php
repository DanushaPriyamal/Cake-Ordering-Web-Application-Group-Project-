<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Forgot-Password";


$message = '';
$error = '';

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_request'])) {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        // Check if email exists
        $sql = "SELECT id, email FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();
            
            // Generate reset token
            $token = generateToken();
            $tokenHash = hash('sha256', $token);
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            
            // Store token in database
            $sql = "UPDATE customers SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $tokenHash, $expiry, $customer['id']);
            
            if ($stmt->execute()) {
                // Send reset email
                $resetLink = SITE_URL . "/reset-password?token=" . urlencode($token);
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


    <style>
:root {
  --primary-color: #3498db;
  --primary-hover: #2980b9;
  --success-color: #2ecc71;
  --error-color: #e74c3c;
  --light-gray: #f5f5f5;
  --dark-gray: #333;
  --medium-gray: #777;
  --border-radius: 6px;
  --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  --transition: all 0.3s ease;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.5;
  color: var(--dark-gray);
  background-color: #f9f9f9;
}

a {
  text-decoration: none;
  color: var(--primary-color);
  transition: var(--transition);
}

a:hover {
  color: var(--primary-hover);
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
   /* padding: 0 15px; */
  
}



/* Main */
main {
  padding: 0.25rem 0 1rem;
  min-height: auto;
}

/* Title */
h1 {
  font-size: 1.5rem;
  margin: 0.5rem auto 1rem;
  text-align: center;
  color: var(--dark-gray);
}

/* Alert Messages */
.alert {
  padding: 0.65rem 1rem;
  margin-bottom: 1rem;
  border-radius: var(--border-radius);
  font-size: 0.9rem;
}

.alert-success {
  background-color: rgba(46, 204, 113, 0.1);
  color: var(--success-color);
  border: 1px solid rgba(46, 204, 113, 0.3);
}

.alert-danger {
  background-color: rgba(231, 76, 60, 0.1);
  color: var(--error-color);
  border: 1px solid rgba(231, 76, 60, 0.3);
}

/* Auth Form */
.auth-form {
  max-width: 400px;
  margin: 0 auto;
  padding: 1.25rem;
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
}

/* Form Group */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 500;
  color: var(--dark-gray);
}

.form-group input {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #ddd;
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
}

.form-group input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

/* Button */
.btn {
  display: inline-block;
  width: 100%;
  padding: 0.65rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1rem;
  cursor: pointer;
  transition: var(--transition);
}

.btn:hover {
  background-color: var(--primary-hover);
}

.auth-links {
  text-align: center;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.auth-links a {
  color: var(--medium-gray);
}

.auth-links a:hover {
  color: var(--primary-color);
}


/* Responsive Adjustments */
@media (max-width: 768px) {
  h1 {
    font-size: 1.3rem;
  }

  .auth-form {
    padding: 1rem;
  }

  .btn {
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) {
  main {
    padding: 0.5rem 0;
  }

  .auth-form {
    padding: 0.9rem;
  }

  .btn {
    padding: 0.6rem;
    font-size: 0.9rem;
  }

  h1 {
    font-size: 1.2rem;
  }
}
    </style>

    <?php include 'includes/header.php'; ?>
    
    <main class="container" style="margin-bottom: 30px;
  margin-top: 30px; ">
        <h1>Forgot Password</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="forgot-password" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" name="reset_request" class="btn btn-primary">Reset Password</button>
            
            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </main>
    
    <?php include 'includes/footer.php'; ?>
