<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

if (!isset($_GET['id'])) {
    redirect('list');
}

$customerId = (int)$_GET['id'];
$errors = [];

// Get customer details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    redirect('list');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
    $customer['name'] = sanitize($_POST['name']);
    $customer['email'] = sanitize($_POST['email']);
    $customer['phone'] = sanitize($_POST['phone']);
    $customer['address'] = sanitize($_POST['address']);
    $password = $_POST['password'];
    
    if (empty($customer['name'])) $errors[] = 'Name is required';
    if (empty($customer['email'])) $errors[] = 'Email is required';
    if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    
    // Check if email already exists (excluding current customer)
    $checkEmail = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $customer['email'], $customerId);
    $checkEmail->execute();
    $checkEmail->store_result();
    
    if ($checkEmail->num_rows > 0) {
        $errors[] = 'Email already exists';
    }
    $checkEmail->close();
    
    if (empty($errors)) {
        if (!empty($password)) {
            // Update with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE customers SET name = ?, email = ?, password = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", 
                $customer['name'], 
                $customer['email'], 
                $hashedPassword, 
                $customer['phone'], 
                $customer['address'],
                $customerId
            );
        } else {
            // Update without changing password
            $sql = "UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", 
                $customer['name'], 
                $customer['email'], 
                $customer['phone'], 
                $customer['address'],
                $customerId
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Customer updated successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error updating customer';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <style>
  
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <link rel="stylesheet" href="../../assets/css/admin-edit-styles.css">


    <div class="admin-container">
        <div class="container">
            <h1>Edit Customer</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="edit?id=<?= $customerId ?>">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($customer['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($customer['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password">
                    <div class="password-note">Leave blank to keep current password</div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>
                
                <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                <a href="list" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>