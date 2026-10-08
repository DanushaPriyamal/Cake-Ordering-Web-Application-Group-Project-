<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Account";


// Redirect if not logged in
if (!isCustomerLoggedIn()) {
    redirect('login');
}

// Get customer details
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        // Update profile
        $sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $phone, $address, $customer_id);
        
        if ($stmt->execute()) {
            $_SESSION['customer_name'] = $name;
            $success = 'Profile updated successfully!';
            // Refresh customer data
            $sql = "SELECT * FROM customers WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
        } else {
            $error = 'Error updating profile. Please try again.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = sanitize($_POST['current_password']);
    $new_password = sanitize($_POST['new_password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_pw = 'Please fill in all fields';
    } elseif ($new_password !== $confirm_password) {
        $error_pw = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $error_pw = 'Password must be at least 8 characters long';
    } elseif (!verifyPassword($current_password, $customer['password'])) {
        $error_pw = 'Current password is incorrect';
    } else {
        // Update password
        $hashed_password = hashPassword($new_password);
        $sql = "UPDATE customers SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $customer_id);
        
        if ($stmt->execute()) {
            $success_pw = 'Password changed successfully!';
        } else {
            $error_pw = 'Error changing password. Please try again.';
        }
    }
}

// Get customer orders
$sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>


    <style>
body {
    font-family: 'Segoe UI', Roboto, -apple-system, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Account Container */
.account-container {
    padding: 2rem 0;
}

.account-container h1 {
    font-size: 2.2rem;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    font-weight: 600;
}

/* Account Sections Grid */
.account-sections {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 992px) {
    .account-sections {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .account-sections > section:last-child {
        grid-column: span 2;
    }
}

/* Account Section Cards */
.account-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.account-section:hover {
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.account-section h2 {
    font-size: 1.5rem;
    margin-bottom: 1.2rem;
    color: #3498db;
    font-weight: 600;
    border-bottom: 2px solid #f1f1f1;
    padding-bottom: 0.5rem;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.2rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-group small {
    display: block;
    margin-top: 0.3rem;
    color: #777;
    font-size: 0.85rem;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn:hover {
    background-color: #2980b9;
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}

.btn-primary {
    background-color: #3498db;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

/* Alert Messages */
.alert {
    padding: 0.8rem 1rem;
    margin-bottom: 1rem;
    border-radius: 6px;
    font-size: 0.95rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Order History Styles */
.order-list {
    display: grid;
    gap: 1rem;
}

.order-item {
    background: white;
    border-radius: 8px;
    padding: 1.2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.order-item:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.order-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
    gap: 0.5rem;
}

.order-id {
    font-weight: 600;
    color: #2c3e50;
}

.order-date {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.order-status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.order-status.pending {
    background-color: #fff3cd;
    color: #856404;
}

.order-status.completed {
    background-color: #d4edda;
    color: #155724;
}

.order-status.processing {
    background-color: #cce5ff;
    color: #004085;
}

.order-status.cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-amount {
    font-weight: 600;
    color: #2c3e50;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .account-container {
        padding: 1rem 0;
    }
    
    .account-container h1 {
        font-size: 1.8rem;
    }
    
    .account-section {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.7rem 1.2rem;
    }
}

/* Animation for better UX */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.account-section {
    animation: fadeIn 0.5s ease forwards;
}

.account-section:nth-child(1) { animation-delay: 0.1s; }
.account-section:nth-child(2) { animation-delay: 0.2s; }
.account-section:nth-child(3) { animation-delay: 0.3s; }

</style>

    <?php include 'includes/header.php'; ?>
    
    <main class="container account-container" style="padding-top: 30px;">
        <h1>My Account</h1>
        
        <div class="account-sections">
            <section class="account-section">
                <h2>Profile Information</h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="post" action="account">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
                        <small>Email cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </section>
            
            <section class="account-section">
                <h2>Change Password</h2>
                
                <?php if (isset($success_pw)): ?>
                    <div class="alert alert-success"><?php echo $success_pw; ?></div>
                <?php elseif (isset($error_pw)): ?>
                    <div class="alert alert-danger"><?php echo $error_pw; ?></div>
                <?php endif; ?>
                
                <form method="post" action="account">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </section>
            
            <section class="account-section">
                <h2>Order History</h2>
                
                <?php if (empty($orders)): ?>
                    <p style="color: black;">You haven't placed any orders yet.</p>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-header">
                                    <span class="order-id">Order #<?php echo $order['id']; ?></span>
                                  <span class="order-date" style="color: black;">
                                    <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                  </span>
                                  <span class="order-status <?php echo strtolower($order['status']); ?>" style="color: black;">
                                    <?php echo ucfirst($order['status']); ?>
                                  </span>

                                </div>
                                <div class="order-details">
                                    <div class="order-amount">Total: Rs.<?php echo number_format($order['total_amount'], 2); ?></div>
                                    <a href="order-details?id=<?php echo $order['id']; ?>" class="btn btn-small">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
