<?php
require_once 'config.php';
require_once 'db.php';

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to check if customer is logged in
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}





// // Function to send email
// function sendEmail($to, $subject, $message) {
//     require_once __DIR__ . '/../vendor/autoload.php';

//     $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    
//     try {
//         // Server settings
//         $mail->isSMTP();
//         $mail->Host       = SMTP_HOST;
//         $mail->SMTPAuth   = true;
//         $mail->Username   = SMTP_USERNAME;
//         $mail->Password   = SMTP_PASSWORD;
//         // $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
//         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 

//         $mail->Port       = 587;

//         // Recipients
//         $mail->setFrom(FROM_EMAIL, FROM_NAME);
//         $mail->addAddress($to);

//         // Content
//         $mail->isHTML(true);
//         $mail->Subject = $subject;
//         $mail->Body    = $message;

//         $mail->send();
//         return true;
//     } catch (Exception $e) {
//         error_log("Email sending failed: " . $mail->ErrorInfo);
//         return false;
//     }
// }





use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function sendEmail($to, $subject, $message) {
    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}


// Function to get cake image path
function getCakeImage($image) {
    if (empty($image)) {
        return 'assets/images/default-cake.jpg';
    }
    return 'assets/uploads/' . $image;
}

// Get all active categories
function getCategories($activeOnly = true) {
    global $conn;
    $sql = "SELECT * FROM categories" . ($activeOnly ? " WHERE is_active = 1" : "") . " ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get subcategories by category ID
function getSubcategories($categoryId, $activeOnly = true) {
    global $conn;
    $sql = "SELECT * FROM subcategories WHERE category_id = ?" . ($activeOnly ? " AND is_active = 1" : "") . " ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all active brands
function getBrands($activeOnly = true) {
    global $conn;
    $sql = "SELECT * FROM brands" . ($activeOnly ? " WHERE is_active = 1" : "") . " ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get featured cakes
function getFeaturedCakes($limit = 8) {
    global $conn;
    $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE c.is_featured = 1 AND c.is_active = 1 
            ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllCakesPaginated($page = 1, $limit = 10, $activeOnly = true) {
    global $conn;
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id" . 
            ($activeOnly ? " WHERE c.is_active = 1" : "") . 
            " ORDER BY c.name LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Get cake by ID
function getCakeById($id) {
    global $conn;
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, 
                   sub.name as subcategory_name, sub.slug as subcategory_slug, 
                   b.name as brand_name, b.slug as brand_slug 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

//new two
function getCakeBySlug($slug) {
    global $conn;
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, 
                   sub.name as subcategory_name, sub.slug as subcategory_slug, 
                   b.name as brand_name, b.slug as brand_slug 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE c.slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// neww
function getRelatedCakes($categoryId, $excludeCakeId, $limit = 4) {
    global $conn;
    $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE c.category_id = ? AND c.id != ? AND c.is_active = 1
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $categoryId, $excludeCakeId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


function searchCakesPaginated($query, $page = 1, $limit = 10) {
    global $conn;
    $offset = ($page - 1) * $limit;
    $query = "%$query%";
    
    $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE (c.name LIKE ? OR c.description LIKE ? OR cat.name LIKE ? OR sub.name LIKE ? OR b.name LIKE ?)
            AND c.is_active = 1
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $query, $query, $query, $query, $query, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function filterCakesByCategoryPaginated($categorySlug, $filterType = 'category', $page = 1, $limit = 10) {
    global $conn;
    $offset = ($page - 1) * $limit;
    
    switch ($filterType) {
        case 'subcategory':
            $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
                    FROM cakes c
                    JOIN subcategories sub ON c.subcategory_id = sub.id
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN brands b ON c.brand_id = b.id
                    WHERE sub.slug = ? AND c.is_active = 1
                    LIMIT ? OFFSET ?";
            break;
        case 'brand':
            $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
                    FROM cakes c
                    JOIN brands b ON c.brand_id = b.id
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
                    WHERE b.slug = ? AND c.is_active = 1
                    LIMIT ? OFFSET ?";
            break;
        case 'category':
        default:
            $sql = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name, b.name as brand_name 
                    FROM cakes c
                    JOIN categories cat ON c.category_id = cat.id
                    LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
                    LEFT JOIN brands b ON c.brand_id = b.id
                    WHERE cat.slug = ? AND c.is_active = 1
                    LIMIT ? OFFSET ?";
            break;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $categorySlug, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Format phone number
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10 && preg_match('/^07[0-9]{8}$/', $phone)) {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $phone);
    }
    if (strlen($phone) === 10 && preg_match('/^0[1-9][0-9]{8}$/', $phone)) {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $phone);
    }
    return $phone;
}

// Get cart items
function getCartItems() {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cartItems = [];
        foreach ($_SESSION['cart'] as $cakeId => $quantity) {
            $cake = getCakeById($cakeId);
            if ($cake) {
                $cake['quantity'] = $quantity;
                $cartItems[] = $cake;
            }
        }
        return $cartItems;
    }
    return [];
}

// Calculate cart total
function getCartTotal() {
    $cartItems = getCartItems();
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Send order notifications
function sendOrderNotifications($orderId) {
    global $conn;
    
    // Get order details
    $orderSql = "SELECT o.*, c.name AS customer_name, c.email, c.phone 
                 FROM orders o
                 LEFT JOIN customers c ON o.customer_id = c.id
                 WHERE o.id = ?";
    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    // Get order items
    $itemsSql = "SELECT oi.*, c.name AS cake_name 
                 FROM order_items oi
                 JOIN cakes c ON oi.cake_id = c.id
                 WHERE oi.order_id = ?";
    $stmt = $conn->prepare($itemsSql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Prepare email content
// Prepare email content (HTML version)
$emailSubject = "Order Confirmation #" . $order['id'];

$emailMessage = '
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; }
    .container { background: #fff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { color: #4CAF50; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table th, table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    table th { background: #f2f2f2; }
    .total { font-weight: bold; color: #000; }
    .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Thank you for your order, ' . htmlspecialchars($order['customer_name'] ?: 'Customer') . '!</h2>
    <p>We have received your order. Here are your order details:</p>
    
    <p><strong>Order ID:</strong> #' . $order['id'] . '<br>
       <strong>Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '<br>
       <strong>Total Amount:</strong> Rs' . number_format($order['total_amount'], 2) . '</p>
       
    <table>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Price (Rs)</th>
      </tr>';

foreach ($items as $item) {
    $emailMessage .= '
      <tr>
        <td>' . htmlspecialchars($item['cake_name']) . '</td>
        <td>' . (int)$item['quantity'] . '</td>
        <td>' . number_format($item['price'], 2) . '</td>
      </tr>';
}

$emailMessage .= '
      <tr class="total">
        <td colspan="2">Total</td>
        <td>Rs' . number_format($order['total_amount'], 2) . '</td>
      </tr>
    </table>
    
    <p>We will contact you soon regarding delivery/pickup.</p>
    
    <p>Best Regards,<br><strong>' . SITE_NAME . '</strong></p>
    
    <div class="footer">
      This is an automated email. Please do not reply.
    </div>
  </div>
</body>
</html>';

    
    // Send email to customer
    $emailSent = sendEmail($order['email'], $emailSubject, $emailMessage);
    
    // Send WhatsApp notification to admin
    if (defined('WHATSAPP_ADMIN_NUMBER') && !empty($order['phone'])) {
        $whatsappMessage = "New Order #" . $order['id'] . "\n";
        $whatsappMessage .= "Customer: " . ($order['customer_name'] ?: 'Guest') . "\n";
        $whatsappMessage .= "Amount: Rs" . number_format($order['total_amount'], 2) . "\n";
        $whatsappMessage .= "Items: " . count($items) . "\n";
        $whatsappMessage .= "Phone: " . $order['phone'];
        
        $whatsappUrl = "https://wa.me/" . WHATSAPP_ADMIN_NUMBER . "?text=" . urlencode($whatsappMessage);
        $_SESSION['whatsapp_notification_url'] = $whatsappUrl;
    }
    
    return $emailSent;
}


// Generate slug from string
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}






// new register otp

// Function to generate OTP
function generateOTP($length = 6) {
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[random_int(0, 9)];
    }
    return $otp;
}

// Function to send OTP email
function sendOTPEmail($email, $otp, $name = '') {
    $subject = "Your OTP for Registration - " . SITE_NAME;
    
    $message = "
    <html>
    <head>
        <title>OTP Verification</title>
    </head>
    <body>
        <h2>Welcome to " . SITE_NAME . "!</h2>
        <p>Dear " . ($name ?: 'Customer') . ",</p>
        <p>Your OTP for registration is: <strong style='font-size: 24px; color: #4361ee;'>" . $otp . "</strong></p>
        <p>This OTP is valid for 10 minutes.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <br>
        <p>Best regards,<br>" . SITE_NAME . " Team</p>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// Function to verify OTP
function verifyOTP($email, $enteredOTP) {
    if (!isset($_SESSION['otp_data'])) {
        return false;
    }
    
    $otpData = $_SESSION['otp_data'];
    
    // Check if OTP exists and not expired
    if (!isset($otpData['email']) || $otpData['email'] !== $email || time() > $otpData['expiry']) {
        unset($_SESSION['otp_data']);
        return false;
    }
    
    // Verify OTP
    if ($otpData['otp'] === $enteredOTP) {
        return true;
    }
    
    return false;
}




// newwww
// Get total count functions
function getTotalCakesCount($activeOnly = true) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM cakes" . ($activeOnly ? " WHERE is_active = 1" : "");
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

function getSearchCakesCount($query) {
    global $conn;
    $query = "%$query%";
    
    $sql = "SELECT COUNT(*) as total 
            FROM cakes c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN subcategories sub ON c.subcategory_id = sub.id
            LEFT JOIN brands b ON c.brand_id = b.id
            WHERE (c.name LIKE ? OR c.description LIKE ? OR cat.name LIKE ? OR sub.name LIKE ? OR b.name LIKE ?)
            AND c.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $query, $query, $query, $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function getFilteredCakesCount($categorySlug, $filterType = 'category') {
    global $conn;
    
    switch ($filterType) {
        case 'subcategory':
            $sql = "SELECT COUNT(*) as total 
                    FROM cakes c
                    JOIN subcategories sub ON c.subcategory_id = sub.id
                    WHERE sub.slug = ? AND c.is_active = 1";
            break;
        case 'brand':
            $sql = "SELECT COUNT(*) as total 
                    FROM cakes c
                    JOIN brands b ON c.brand_id = b.id
                    WHERE b.slug = ? AND c.is_active = 1";
            break;
        case 'category':
        default:
            $sql = "SELECT COUNT(*) as total 
                    FROM cakes c
                    JOIN categories cat ON c.category_id = cat.id
                    WHERE cat.slug = ? AND c.is_active = 1";
            break;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categorySlug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

// Helper function to build pagination URLs
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return http_build_query($params);
}