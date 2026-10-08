<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Login";


// Redirect if already logged in
if (isCustomerLoggedIn()) {
    redirect('account');
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Check customer credentials
        $sql = "SELECT * FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();

            // Verify password
            if (verifyPassword($password, $customer['password'])) {
                // Set session variables
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                $_SESSION['customer_email'] = $customer['email'];

                // Redirect to account page or previous page
                redirect('account');
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>


<style>
    :root {
        --primary-color: rgb(238, 67, 235);
        --primary-dark: rgb(138, 58, 212);
        --primary-light: rgb(204, 171, 207);
        --secondary-color: rgb(89, 6, 145);
        --accent-color: #f72585;
        --danger-color: #ef233c;
        --success-color: #4cc9f0;
        --warning-color: #f8961e;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --gray-color: #6c757d;
        --light-gray: #e9ecef;
        --border-radius: 12px;
        --border-radius-sm: 8px;
        --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        --box-shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-primary: linear-gradient(135deg, rgb(240, 106, 245) 0%, rgb(109, 16, 152) 100%);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--dark-color);
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        line-height: 1.6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding: 0;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        flex: 1;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }
    }

    .login-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 200px);
        padding: 2rem 0;
    }

    .login-wrapper {
        display: flex;
        width: 100%;
        max-width: 900px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow-lg);
        overflow: hidden;
        min-height: 550px;
    }

    .login-left {
        flex: 1;
        background: var(--gradient-primary);
        color: white;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .login-left::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(30deg);
    }

    .login-right {
        flex: 1;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-logo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-logo img {
        max-height: 50px;
    }

    .login-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .login-subtitle {
        color: var(--gray-color);
        margin-bottom: 2rem;
        text-align: center;
    }

    /* ===== Form Styles ===== */
    .auth-form {
        width: 100%;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark-color);
        font-size: 0.9rem;
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-color);
        z-index: 2;
    }

    .form-group input {
        width: 100%;
        padding: 0.85rem 1rem 0.85rem 3rem;
        font-size: 1rem;
        border: 1px solid var(--light-gray);
        border-radius: var(--border-radius-sm);
        transition: var(--transition);
        background-color: var(--light-color);
        font-family: inherit;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        background-color: white;
    }

    /* ===== Button Styles ===== */
    .btn {
        display: inline-block;
        width: 100%;
        padding: 0.85rem;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        margin-top: 0.5rem;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    /* ===== Alert Styles ===== */
    .alert {
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        border-radius: var(--border-radius-sm);
        font-size: 0.9rem;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background-color: rgba(239, 35, 60, 0.08);
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }

    .alert i {
        font-size: 1.2rem;
    }

    /* ===== Auth Links ===== */
    .auth-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        flex-wrap: wrap;
        gap: 10px;
    }

    .auth-links a {
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
    }

    .auth-links a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* ===== Password Toggle ===== */
    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--gray-color);
        font-size: 1rem;
        z-index: 2;
    }

    .password-toggle:hover {
        color: var(--dark-color);
    }

    /* ===== Features List ===== */
    .features-list {
        list-style: none;
        margin-top: 2rem;
    }

    .features-list li {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .features-list i {
        margin-right: 10px;
        background: rgba(255, 255, 255, 0.2);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ===== Responsive Design ===== */
    @media (max-width: 768px) {
        .login-wrapper {
            flex-direction: column;
            max-width: 450px;
        }

        .login-left {
            padding: 2rem;
            text-align: center;
        }

        .login-right {
            padding: 2rem;
        }

        .login-title {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 1rem;
        }

        .login-left,
        .login-right {
            padding: 1.5rem;
        }

        .auth-links {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .auth-links div {
            width: 100%;
            text-align: center;
        }
    }

    /* ===== Animations ===== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .login-right {
        animation: fadeIn 0.8s ease-out;
    }

    .login-left {
        animation: slideIn 0.8s ease-out;
    }

    /* ===== Loading State ===== */
    .btn.loading {
        pointer-events: none;
        opacity: 0.8;
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
        from {
            transform: rotate(0turn);
        }

        to {
            transform: rotate(1turn);
        }
    }

    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 1.5rem;
        font-size: 0.8rem;
        color: var(--gray-color);
    }
</style>

<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-left">
                <h2>Sweet Delights Await!</h2>
                <p>Sign in to your account to continue</p>

                <ul class="features-list">
                    <li><i class="fas fa-shield-alt"></i> Secure & Encrypted Login</li>
                    <li><i class="fas fa-birthday-cake"></i> Track Your Cake Orders</li>
                    <li><i class="fas fa-calendar-check"></i> Manage Your Bookings</li>
                    <li><i class="fas fa-user-cog"></i> Update Your Preferences</li>
                </ul>

                <div class="security-badge">
                        <i class="fas fa-lock"></i>
                    <span style="color: white;">Sweet & Secure Ordering</span>
                    </div>
            </div>

            <div class="login-right">
                <div class="login-logo">
                    <h1 class="login-title">YUMMY CAKE</h1>
                </div>

                <h2 class="login-title">Sweet Login</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="login" class="auth-form" itemprop="mainContentOfPage">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required autocomplete="email" placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon password-wrapper">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                            <button type="button" class="password-toggle" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary" id="loginBtn">
                        <span></span>Sweet Sign In
                    </button>

                    <div class="auth-links">
                        <div>
                            <a href="forgot-password">Forgot Password?</a>
                        </div>
                        <div>
                            <a href="register">Create an Account</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const passwordToggle = document.querySelector('.password-toggle');
        const passwordInput = document.querySelector('#password');

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Form submission loading state
        const loginForm = document.querySelector('.auth-form');
        const loginBtn = document.querySelector('#loginBtn');

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', function(e) {
                // Basic client-side validation
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                if (!email || !password) {
                    e.preventDefault();
                    return;
                }

                loginBtn.classList.add('loading');
                loginBtn.querySelector('span').textContent = 'Signing In...';
            });
        }
    });
</script>
<?php include 'includes/footer.php'; ?>