<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../spk-st-wl');
}

$errors = [];
$customer = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    // Validate inputs
    $customer['name'] = sanitize($_POST['name']);
    $customer['email'] = sanitize($_POST['email']);
    $customer['phone'] = sanitize($_POST['phone']);
    $customer['address'] = sanitize($_POST['address']);
    $password = $_POST['password'];
    
    if (empty($customer['name'])) $errors[] = 'Name is required';
    if (empty($customer['email'])) $errors[] = 'Email is required';
    if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    
    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $checkEmail->bind_param("s", $customer['email']);
    $checkEmail->execute();
    $checkEmail->store_result();
    
    if ($checkEmail->num_rows > 0) {
        $errors[] = 'Email already exists';
    }
    $checkEmail->close();
    
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO customers (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $customer['name'], 
            $customer['email'], 
            $hashedPassword, 
            $customer['phone'], 
            $customer['address']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Customer added successfully!';
            redirect('list');
        } else {
            $errors[] = 'Error adding customer to database';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <style>
   
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <link rel="stylesheet" href="../../assets/css/admin-add-styles.css">

    <div class="admin-container">
        <div class="container">
            <h1>Add New Customer</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="add">
                <div class="form-group">
                    <label for="name" class="required">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($customer['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($customer['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="password" class="required">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>
                
                <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
                <a href="list" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>