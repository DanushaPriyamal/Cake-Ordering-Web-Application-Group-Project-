<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check admin credentials
        $sql = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Redirect to dashboard
                redirect('dashboard.php');
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>


    <style>
:root {
    --primary-color: #4361ee;
    --primary-dark: #3a56d4;
    --danger-color: #ef233c;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --gray-color: #6c757d;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    color: var(--dark-color);
    line-height: 1.6;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

/* Login Container */
.login-container {
    background-color: white;
    width: 100%;
    max-width: 450px;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.login-container h1 {
    font-size: 1.8rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 600;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-color);
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
}

/* Button Styles */
.btn {
    display: inline-block;
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
}

/* Alert Styles */
.alert {
    padding: 0.75rem 1rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius);
    font-size: 0.9rem;
}

.alert-danger {
    background-color: rgba(239, 35, 60, 0.1);
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
}

/* Forgot Password Link */
.forgot-password {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.9rem;
}

.forgot-password a {
    color: var(--gray-color);
    text-decoration: none;
    transition: var(--transition);
}

.forgot-password a:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .login-container {
        padding: 2rem 1.5rem;
    }
    
    .login-container h1 {
        font-size: 1.5rem;
    }
    
    .btn {
        padding: 0.65rem;
    }
}

/* Animation for better UX */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-container {
    animation: fadeIn 0.5s ease-out;
}

/* Loading state for button */
.btn.loading {
    position: relative;
    pointer-events: none;
}

.btn.loading::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    margin: auto;
    border: 3px solid transparent;
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0turn); }
    to { transform: rotate(1turn); }
}

</style>

    <div class="login-container">
        <h1>Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="spk-st-wl">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary">Login</button>
            
            <div class="forgot-password">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
        </form>
    </div>
