<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Register";


// Redirect if already logged in
if (isCustomerLoggedIn()) {
    redirect('account');
}

$error = '';
$success = '';
$showOTPModal = false;

// Handle initial registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Password must contain at least one special character';
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Generate and send OTP
            $otp = generateOTP();
            
            // Store OTP and registration data in session
            $_SESSION['otp_data'] = [
                'email' => $email,
                'otp' => $otp,
                'expiry' => time() + (10 * 60), // 10 minutes
                'registration_data' => [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password, // Store plain password temporarily
                    'phone' => $phone,
                    'address' => $address
                ]
            ];
            
            // Send OTP email
            $otpSent = sendOTPEmail($email, $otp, $name);
            
            if ($otpSent) {
                $showOTPModal = true;
                $success = 'OTP has been sent to your email. Please verify to complete registration.';
            } else {
                $error = 'Failed to send OTP. Please try again.';
                unset($_SESSION['otp_data']);
            }
        }
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = sanitize($_POST['email']);
    $otp = sanitize($_POST['otp']);
    
    if (verifyOTP($email, $otp)) {
        // OTP verified, complete registration
        if (isset($_SESSION['otp_data']['registration_data'])) {
            $regData = $_SESSION['otp_data']['registration_data'];
            
            // Hash password before storing in database
            $hashed_password = hashPassword($regData['password']);
            
            // Insert new customer
            $sql = "INSERT INTO customers (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $regData['name'], $regData['email'], $hashed_password, $regData['phone'], $regData['address']);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now <a href="login">login</a>.';
                unset($_SESSION['otp_data']);
                
                // Clear form data
                echo '<script>document.addEventListener("DOMContentLoaded", function() { document.querySelector("form").reset(); });</script>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        } else {
            $error = 'Registration data not found. Please try again.';
        }
    } else {
        $error = 'Invalid OTP or OTP has expired. Please try again.';
        $showOTPModal = true;
    }
}

// Resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
    $email = sanitize($_POST['email']);
    
    if (isset($_SESSION['otp_data']) && $_SESSION['otp_data']['email'] === $email) {
        $otp = generateOTP();
        $otpSent = sendOTPEmail($email, $otp, $_SESSION['otp_data']['registration_data']['name']);
        
        if ($otpSent) {
            $_SESSION['otp_data']['otp'] = $otp;
            $_SESSION['otp_data']['expiry'] = time() + (10 * 60);
            $success = 'New OTP has been sent to your email.';
            $showOTPModal = true;
        } else {
            $error = 'Failed to resend OTP. Please try again.';
        }
    } else {
        $error = 'Unable to resend OTP. Please restart registration.';
    }
}
?>

    <style>
:root {
    --primary:rgb(215, 67, 238);
    --primary-dark:rgb(181, 58, 212);
    --secondary: #7209b7;
    --success: #4cc9f0;
    --danger: #f72585;
    --warning: #f8961e;
    --info: #4895ef;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --light-gray: #e9ecef;
    --border-radius: 12px;
    --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Floating Animation */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.floating {
    animation: float 6s ease-in-out infinite;
}

/* Slide In Animation */
@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(30px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pulse Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Form Styling */
.auth-form {
    background: white;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    max-width: 500px;
    margin: 2rem auto;
    animation: slideIn 0.6s ease-out;
    position: relative;
    overflow: hidden;
}

.auth-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
}

.form-title {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--dark);
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.form-group {
    margin-bottom: 1.5rem;
    animation: slideIn 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper input,
.input-wrapper textarea {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid var(--light-gray);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
    background-color: #fff;
}

.input-wrapper input:focus,
.input-wrapper textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.input-icon {
    position: absolute;
    left: 1rem;
    color: var(--gray);
    transition: var(--transition);
}

.textarea-icon {
    position: absolute;
    left: 1rem;
    top: 1rem;
    color: var(--gray);
}

.input-wrapper input:focus + .input-icon,
.input-wrapper textarea:focus + .textarea-icon {
    color: var(--primary);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: var(--gray);
    cursor: pointer;
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--primary);
    transform: scale(1.1);
}

/* Password Strength Meter */
.password-strength {
    margin-top: 0.5rem;
    height: 5px;
    background-color: var(--light-gray);
    border-radius: 5px;
    overflow: hidden;
}

.strength-meter {
    height: 100%;
    width: 0%;
    border-radius: 5px;
    transition: var(--transition);
}

.strength-weak {
    background-color: var(--danger);
}

.strength-medium {
    background-color: var(--warning);
}

.strength-strong {
    background-color: var(--success);
}

.strength-text {
    font-size: 0.8rem;
    margin-top: 0.25rem;
    color : black;
    font-weight: 600;
    transition: var(--transition);
}

/* Password Requirements */
.password-requirements {
    margin-top: 0.75rem;
    padding: 1rem;
    background-color: rgba(248, 249, 250, 0.7);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary);
}

.password-requirements h4 {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark);
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
    font-size: 0.8rem;
    transition: var(--transition);
}

.requirement i {
    margin-right: 0.5rem;
    font-size: 0.6rem;
}

.requirement.met {
    color: var(--success);
}

.requirement.unmet {
    color: var(--gray);
}

.password-match {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

/* Buttons */
.btn {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.btn:active {
    transform: translateY(0);
}

.auth-links {
    color:black;
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.9rem;
}

.auth-links a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.auth-links a:hover {
    color: var(--secondary);
    text-decoration: underline;
}

/* OTP Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    position: relative;
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    max-width: 450px;
    width: 90%;
    animation: slideIn 0.4s ease-out;
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1.5rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray);
    transition: var(--transition);
}

.close-modal:hover {
    color: var(--dark);
    transform: scale(1.1);
}

.otp-inputs {
    display: flex;
    justify-content: space-between;
    margin: 1.5rem 0;
}

.otp-input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 1.2rem;
    font-weight: 600;
    border: 2px solid var(--light-gray);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.otp-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    transform: scale(1.05);
}

.timer {
    text-align: center;
    margin: 1rem 0;
    font-weight: 600;
    color: var(--primary);
    animation: pulse 2s infinite;
}

.resend-link {
    text-align: center;
    margin-top: 1rem;
}

.resend-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.resend-link a:hover:not(.disabled) {
    color: var(--secondary);
    text-decoration: underline;
}

.resend-link a.disabled {
    color: var(--gray);
    cursor: not-allowed;
    opacity: 0.6;
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideIn 0.5s ease-out;
}

.alert-danger {
    background-color: rgba(247, 37, 133, 0.1);
    border-left: 4px solid var(--danger);
    color: var(--dark);
}

.alert-success {
    background-color: rgba(76, 201, 240, 0.1);
    border-left: 4px solid var(--success);
    color: var(--dark);
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(67, 97, 238, 0.2);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }
    
    .auth-form {
        padding: 1.5rem;
        margin: 1rem auto;
    }
    
    .form-title {
        font-size: 1.5rem;
    }
    
    .otp-input {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .modal-content {
        margin: 10% auto;
        padding: 1.5rem;
    }
    
    .password-requirements {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .auth-form {
        padding: 1rem;
    }
    
    .otp-inputs {
        justify-content: space-around;
    }
    
    .otp-input {
        width: 35px;
        height: 35px;
    }
    
    .btn {
        padding: 0.6rem 1rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
}

/* Additional Animations for Form Elements */
.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }
.form-group:nth-child(5) { animation-delay: 0.5s; }
.form-group:nth-child(6) { animation-delay: 0.6s; }

/* Focus effects */
input:focus, textarea:focus {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* Hover effects for form elements */
.input-wrapper:hover .input-icon {
    color: var(--primary);
    transform: scale(1.1);
}

/* Custom scrollbar for textarea */
textarea {
    resize: vertical;
    min-height: 80px;
}

textarea::-webkit-scrollbar {
    width: 6px;
}

textarea::-webkit-scrollbar-track {
    background: var(--light-gray);
    border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 3px;
}

/* Selection color */
::selection {
    background-color: rgba(67, 97, 238, 0.2);
}

/* Placeholder styling */
::placeholder {
    color: var(--gray);
    opacity: 0.7;
}

/* Page title styling */
.page-title {
    text-align: center;
    margin: 2rem 0;
    color: var(--dark);
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Container styling */
.container {
    max-width: 1200px;
    margin: 0 auto;
    /* padding: 0 2rem; */
}

/* Additional utility classes */
.text-center {
    text-align: center;
}

.mt-2 {
    margin-top: 2rem;
}

.mb-2 {
    margin-bottom: 2rem;
}

.hidden {
    display: none;
}

/* Error state for inputs */
.input-wrapper.error input,
.input-wrapper.error textarea {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px rgba(247, 37, 133, 0.1);
}

.input-wrapper.error .input-icon {
    color: var(--danger);
}

/* Success state for inputs */
.input-wrapper.success input,
.input-wrapper.success textarea {
    border-color: var(--success);
    box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.1);
}

.input-wrapper.success .input-icon {
    color: var(--success);
}
</style>

<?php include 'includes/header.php'; ?>


    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    
    <main class="container">
        <h1 class="page-title floating">Create Your Account</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$showOTPModal && empty($success)): ?>
            <form method="post" action="register" class="auth-form" id="registrationForm">
                <h1 class="form-title">Join Us Today</h1>
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name*</label>
                    <div class="input-wrapper">
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required 
                               placeholder="Enter your full name" autocomplete="name">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address*</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required 
                               placeholder="your@email.com" autocomplete="email">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password*</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required minlength="8" 
                               placeholder="Create a strong password" autocomplete="new-password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password strength</div>
                    
                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <h4><i class="fas fa-list-check"></i> Password Requirements:</h4>
                        <div class="requirement unmet" id="reqLength">
                            <i class="fas fa-circle"></i> At least 8 characters
                        </div>
                        <div class="requirement unmet" id="reqUppercase">
                            <i class="fas fa-circle"></i> One uppercase letter
                        </div>
                        <div class="requirement unmet" id="reqLowercase">
                            <i class="fas fa-circle"></i> One lowercase letter
                        </div>
                        <div class="requirement unmet" id="reqNumber">
                            <i class="fas fa-circle"></i> One number
                        </div>
                        <div class="requirement unmet" id="reqSpecial">
                            <i class="fas fa-circle"></i> One special character
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password*</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" 
                               placeholder="Confirm your password" autocomplete="new-password">
                        <button type="button" class="password-toggle" id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-match" id="passwordMatch"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <div class="input-wrapper">
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               placeholder="+94 XX XXX XXXX" autocomplete="tel">
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <div class="input-wrapper">
                        <textarea id="address" name="address" rows="3" 
                                  placeholder="Enter your delivery address" autocomplete="street-address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        <i class="fas fa-map-marker-alt textarea-icon"></i>
                    </div>
                </div>
                
                <button type="submit" name="register" class="btn" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <div class="auth-links">
                    Already have an account? <a href="login"><i class="fas fa-sign-in-alt"></i> Sign in here</a>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <!-- OTP Verification Modal -->
    <div id="otpModal" class="modal" style="<?php echo $showOTPModal ? 'display: block;' : 'display: none;'; ?>">
        <div class="modal-content">
            <span class="close-modal" onclick="closeOTPModal()">&times;</span>
            <h2>Verify Your Email</h2>
            <p>Enter the 6-digit OTP sent to <strong><?php echo isset($_SESSION['otp_data']['email']) ? $_SESSION['otp_data']['email'] : ''; ?></strong></p>
            
            <form method="post" action="register" id="otpForm">
                <input type="hidden" name="email" id="otpEmail" value="<?php echo isset($_SESSION['otp_data']['email']) ? $_SESSION['otp_data']['email'] : ''; ?>">
                
                <div class="otp-inputs">
                    <input type="text" name="otp1" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'otp2')">
                    <input type="text" name="otp2" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'otp3')">
                    <input type="text" name="otp3" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'otp4')">
                    <input type="text" name="otp4" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'otp5')">
                    <input type="text" name="otp5" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'otp6')">
                    <input type="text" name="otp6" class="otp-input" maxlength="1" required autocomplete="off" oninput="moveToNext(this, 'verifyBtn')">
                </div>
                
                <div class="timer" id="timer">OTP expires in: 10:00</div>
                
                <button type="submit" name="verify_otp" class="btn" id="verifyBtn">
                    <i class="fas fa-check-circle"></i> Verify OTP
                </button>
                
                <div class="resend-link">
                    <a href="#" onclick="resendOTP()" id="resendLink">
                        <i class="fas fa-redo"></i> Resend OTP
                    </a>
                </div>
            </form>
            
            <form method="post" action="register" id="resendForm" style="display: none;">
                <input type="hidden" name="email" value="<?php echo isset($_SESSION['otp_data']['email']) ? $_SESSION['otp_data']['email'] : ''; ?>">
                <input type="hidden" name="resend_otp" value="1">
            </form>
        </div>
    </div>
    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById('passwordToggle');
            const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');

            passwordToggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            confirmPasswordToggle.addEventListener('click', function() {
                const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            // Password strength checker
            passwordField.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });

            confirmPasswordField.addEventListener('input', checkPasswordMatch);

            // Form submission handler
            const registrationForm = document.getElementById('registrationForm');
            if (registrationForm) {
                registrationForm.addEventListener('submit', function(e) {
                    if (!validateForm()) {
                        e.preventDefault();
                        showError('Please fix the errors before submitting.');
                    } else {
                        showLoading();
                    }
                });
            }

            // Auto-focus first OTP input when modal opens
            <?php if ($showOTPModal): ?>
            const firstOtpInput = document.getElementsByName('otp1')[0];
            if (firstOtpInput) {
                setTimeout(() => firstOtpInput.focus(), 500);
            }
            <?php endif; ?>
        });

        // Password strength function
        function checkPasswordStrength(password) {
            const strengthMeter = document.getElementById('strengthMeter');
            const strengthText = document.getElementById('strengthText');
            const requirements = {
                length: document.getElementById('reqLength'),
                uppercase: document.getElementById('reqUppercase'),
                lowercase: document.getElementById('reqLowercase'),
                number: document.getElementById('reqNumber'),
                special: document.getElementById('reqSpecial')
            };

            let strength = 0;
            let messages = [];

            // Check requirements
            if (password.length >= 8) {
                strength += 20;
                requirements.length.classList.replace('unmet', 'met');
                requirements.length.innerHTML = '<i class="fas fa-check-circle"></i> At least 8 characters';
            } else {
                requirements.length.classList.replace('met', 'unmet');
                requirements.length.innerHTML = '<i class="fas fa-circle"></i> At least 8 characters';
            }

            if (/[A-Z]/.test(password)) {
                strength += 20;
                requirements.uppercase.classList.replace('unmet', 'met');
                requirements.uppercase.innerHTML = '<i class="fas fa-check-circle"></i> One uppercase letter';
            } else {
                requirements.uppercase.classList.replace('met', 'unmet');
                requirements.uppercase.innerHTML = '<i class="fas fa-circle"></i> One uppercase letter';
            }

            if (/[a-z]/.test(password)) {
                strength += 20;
                requirements.lowercase.classList.replace('unmet', 'met');
                requirements.lowercase.innerHTML = '<i class="fas fa-check-circle"></i> One lowercase letter';
            } else {
                requirements.lowercase.classList.replace('met', 'unmet');
                requirements.lowercase.innerHTML = '<i class="fas fa-circle"></i> One lowercase letter';
            }

            if (/[0-9]/.test(password)) {
                strength += 20;
                requirements.number.classList.replace('unmet', 'met');
                requirements.number.innerHTML = '<i class="fas fa-check-circle"></i> One number';
            } else {
                requirements.number.classList.replace('met', 'unmet');
                requirements.number.innerHTML = '<i class="fas fa-circle"></i> One number';
            }

            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                strength += 20;
                requirements.special.classList.replace('unmet', 'met');
                requirements.special.innerHTML = '<i class="fas fa-check-circle"></i> One special character';
            } else {
                requirements.special.classList.replace('met', 'unmet');
                requirements.special.innerHTML = '<i class="fas fa-circle"></i> One special character';
            }

            // Update strength meter
            strengthMeter.style.width = strength + '%';
            
            if (strength < 40) {
                strengthMeter.style.background = 'var(--danger)';
                strengthText.textContent = 'Weak Password';
                strengthText.className = 'strength-text strength-weak';
            } else if (strength < 80) {
                strengthMeter.style.background = 'var(--warning)';
                strengthText.textContent = 'Medium Password';
                strengthText.className = 'strength-text strength-medium';
            } else {
                strengthMeter.style.background = 'var(--success)';
                strengthText.textContent = 'Strong Password';
                strengthText.className = 'strength-text strength-strong';
            }
        }

        // Password match checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');

            if (!matchDiv) return;

            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
                matchDiv.className = 'password-match';
            } else if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                matchDiv.className = 'password-match strength-strong';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                matchDiv.className = 'password-match strength-weak';
            }
        }

        // Form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showError('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                showError('Password must be at least 8 characters long');
                return false;
            }

            return true;
        }

        // Error display function
        function showError(message) {
            // Create error alert
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            // Add to page
            const container = document.querySelector('.container');
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            container.insertBefore(errorDiv, container.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                errorDiv.style.animation = 'slideOut 0.5s ease-out';
                setTimeout(() => errorDiv.remove(), 500);
            }, 5000);
        }

        // Loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // OTP Input handling
        function moveToNext(current, nextFieldId) {
            if (current.value.length === 1) {
                if (nextFieldId === 'verifyBtn') {
                    document.getElementById('verifyBtn').focus();
                } else {
                    document.getElementsByName(nextFieldId)[0].focus();
                }
            }
        }

        // Combine OTP inputs before form submission
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const otp1 = document.getElementsByName('otp1')[0].value;
            const otp2 = document.getElementsByName('otp2')[0].value;
            const otp3 = document.getElementsByName('otp3')[0].value;
            const otp4 = document.getElementsByName('otp4')[0].value;
            const otp5 = document.getElementsByName('otp5')[0].value;
            const otp6 = document.getElementsByName('otp6')[0].value;
            
            const fullOTP = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
            
            const hiddenOtpInput = document.createElement('input');
            hiddenOtpInput.type = 'hidden';
            hiddenOtpInput.name = 'otp';
            hiddenOtpInput.value = fullOTP;
            this.appendChild(hiddenOtpInput);
            
            showLoading();
        });

        // Timer functionality
        let timeLeft = 600; // 10 minutes in seconds
        const timerElement = document.getElementById('timer');
        const resendLink = document.getElementById('resendLink');

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `OTP expires in: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft > 0) {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            } else {
                resendLink.classList.remove('disabled');
                timerElement.textContent = 'OTP has expired';
                timerElement.style.color = 'var(--danger)';
                timerElement.style.animation = 'pulse 0.5s infinite';
            }
        }

        // Start timer when modal is shown
        <?php if ($showOTPModal): ?>
        updateTimer();
        <?php endif; ?>

        function closeOTPModal() {
            document.getElementById('otpModal').style.display = 'none';
            window.location.href = 'register';
        }

        function resendOTP() {
            if (!resendLink.classList.contains('disabled')) {
                showLoading();
                document.getElementById('resendForm').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('otpModal');
            if (event.target === modal) {
                closeOTPModal();
            }
        }

        // Add CSS for slideOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);

        // Handle page load completion
        window.addEventListener('load', function() {
            hideLoading();
            
            // Add floating animation to form elements with delay
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
        <?php include 'includes/footer.php'; ?>

