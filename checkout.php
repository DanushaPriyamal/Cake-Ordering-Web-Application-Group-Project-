<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Checkout";


if (!isCustomerLoggedIn()) {
    redirect('login'); // not logged in → redirect to login.php
    exit;
}

// Redirect if cart is empty
$cartItems = getCartItems();
if (empty($cartItems)) {
    redirect('cart');
}

$cartTotal = getCartTotal();

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate inputs
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $notes = sanitize($_POST['notes']);
    $paymentMethod = sanitize($_POST['payment_method']);

    $errors = [];

    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';

    if (empty($errors)) {
        // Check if customer exists or create new
        $customerId = null;

        // Check if email exists
        $sql = "SELECT id FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            $customerId = $customer['id'];
        } else {
            // Create new customer
            $sql = "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $phone, $address);
            $stmt->execute();
            $customerId = $stmt->insert_id;
        }

        // Calculate final total (add 400 if COD)
        $finalTotal = $cartTotal;
        if ($paymentMethod === 'cod') {
            $finalTotal += 400;
        }

        // Create order
        $sql = "INSERT INTO orders (customer_id, total_amount, payment_method, delivery_address, phone, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idssss", $customerId, $finalTotal, $paymentMethod, $address, $phone, $notes);
        $stmt->execute();
        $orderId = $stmt->insert_id;

        // Add order items
        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (order_id, cake_id, quantity, price) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Handle payment
        if ($paymentMethod === 'paypal') {
            $_SESSION['current_order_id'] = $orderId;
            
            // Set order as "pending" initially
            $sql = "UPDATE orders SET status='pending' WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            
            redirect('payments/paypal-initiate'); // Redirect to PayPal payment
        }  else {
            // Cash on delivery
            if (sendOrderNotifications($orderId)) {
                unset($_SESSION['cart']);
                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $orderId;
                redirect('order-success');
            } else {
                $_SESSION['error_message'] = "Order placed but notifications failed. Order ID: #" . $orderId;
                redirect('order-success');
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
    /* Checkout Page Modern CSS */
    :root {
        --mint-cream: #f4f9f4;
        --tea-green: #d4e2d4;
        --cambridge-blue: #8db596;
        --hookers-green: #5c7a6d;
        --dark-slate: #3a4a42;
        --peach-puff: #ffd5b3;
        --salmon-pink: #ff9a9a;
        --placeholder-gray: #a8a8a8;
        --error-red: #ff6b6b;
        --success-green: #6bff6b;
    }

    /* Base Styles */
    .checkout {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        line-height: 1.6;
        color: var(--dark-slate);
        background-color: var(--mint-cream);
        padding: 2rem 0;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    h1,
    h2 {
        color: var(--hookers-green);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    h1 {
        font-size: 2.2rem;
        text-align: center;
        position: relative;
        padding-bottom: 1rem;
    }

    h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background-color: var(--cambridge-blue);
    }

    h2 {
        font-size: 1.5rem;
        margin-top: 2rem;
        border-bottom: 2px solid var(--tea-green);
        padding-bottom: 0.5rem;
    }

    /* Checkout Container */
    .checkout-container {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 2.5rem;
        margin-top: 2rem;
    }

    /* Form Styles */
    .checkout-form {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--hookers-green);
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    textarea {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 2px solid var(--tea-green);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: var(--mint-cream);
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="tel"]:focus,
    textarea:focus {
        outline: none;
        border-color: var(--cambridge-blue);
        box-shadow: 0 0 0 3px rgba(141, 181, 150, 0.2);
    }

    textarea {
        min-height: 100px;
        resize: vertical;
    }

    ::placeholder {
        color: var(--placeholder-gray);
        opacity: 1;
    }

    /* Payment Methods */
    .payment-methods {
        margin: 1.5rem 0;
    }

    .payment-method {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 8px;
        background-color: var(--mint-cream);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .payment-method:hover {
        border-color: var(--cambridge-blue);
    }

    .payment-method input[type="radio"] {
        margin-right: 1rem;
        accent-color: var(--cambridge-blue);
        transform: scale(1.2);
    }

    .payment-method label {
        margin-bottom: 0;
        font-weight: 500;
        color: var(--dark-slate);
        cursor: pointer;
    }

    /* Order Summary */
    .order-summary {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        align-self: flex-start;
        position: sticky;
        top: 1rem;
    }

    .summary-items {
        margin-bottom: 1.5rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0;
        border-bottom: 1px dashed var(--tea-green);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .item-name {
        color: var(--dark-slate);
    }

    .item-price {
        font-weight: 600;
        color: var(--hookers-green);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        margin-top: 1rem;
        border-top: 2px solid var(--cambridge-blue);
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--hookers-green);
    }

    .delivery-charge-line {
        font-weight: 600;
        color: var(--hookers-green);
        border-top: 1px dashed var(--cambridge-blue);
        border-bottom: 1px dashed var(--cambridge-blue);
        margin-top: 0.5rem;
        padding-top: 0.8rem;
        padding-bottom: 0.8rem;
    }

    /* Button Styles */
    .btn {
        display: inline-block;
        padding: 0.8rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary {
        background-color: var(--cambridge-blue);
        color: white;
        width: 100%;
        margin-top: 1rem;
    }

    .btn-primary:hover {
        background-color: var(--hookers-green);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Alert Styles */
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
    }

    .alert-danger {
        background-color: var(--salmon-pink);
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .checkout-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .order-summary {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }

        h1 {
            font-size: 1.8rem;
        }

        h2 {
            font-size: 1.3rem;
        }

        .checkout-form,
        .order-summary {
            padding: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        .checkout {
            padding: 1.5rem 0;
        }

        h1 {
            font-size: 1.6rem;
        }

        .btn-primary {
            padding: 0.8rem 1rem;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .checkout-container {
        animation: fadeIn 0.5s ease-out;
    }

    /* Form Validation */
    input:invalid,
    textarea:invalid {
        border-color: var(--salmon-pink);
    }

    input:valid,
    textarea:valid {
        border-color: var(--tea-green);
    }

    /* Accessibility Focus Styles */
    input:focus-visible,
    textarea:focus-visible,
    button:focus-visible {
        outline: 2px solid var(--hookers-green);
        outline-offset: 2px;
    }
</style>

<section class="checkout">
    <div class="container">
        <h1>Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="checkout-form">
                <form method="post" action="checkout">
                    <h2>Customer Information</h2>

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Order Notes</label>
                        <textarea id="notes" placeholder="Add your postal code and city here..." name="notes"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                    </div>

                    <h2>Payment Method</h2>

                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="paypal" name="payment_method" value="paypal" checked>
                            <label for="paypal">PayPal Payment (Credit/Debit Cards & PayPal Balance)</label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="cod" name="payment_method" value="cod">
                            <label for="cod">Cash on Delivery (+Rs.400 Delivery Charge) | Islandwide Delivery in 3 Days! 🛵 </label>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                </form>
            </div>

            <div class="order-summary">
                <h2>Your Order</h2>
                <div class="summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                            <span class="item-price">Rs.<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>Rs.<?php echo number_format($cartTotal, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const totalElement = document.querySelector('.summary-total span:last-child');
        const originalTotal = <?php echo $cartTotal; ?>;

        function updateTotal() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            let deliveryCharge = 0;
            let newTotal = originalTotal;

            if (selectedMethod === 'cod') {
                deliveryCharge = 400;
                newTotal = originalTotal + deliveryCharge;
            }

            // Update the display
            totalElement.textContent = 'Rs.' + newTotal.toFixed(2);

            // Add/update delivery charge line
            let deliveryLine = document.querySelector('.delivery-charge-line');
            if (!deliveryLine) {
                deliveryLine = document.createElement('div');
                deliveryLine.className = 'summary-item delivery-charge-line';
                const itemsContainer = document.querySelector('.summary-items');
                itemsContainer.appendChild(deliveryLine);
            }

            if (deliveryCharge > 0) {
                deliveryLine.innerHTML = `
                <span class="item-name">Delivery Charge (COD)</span>
                <span class="item-price">+Rs.${deliveryCharge.toFixed(2)}</span>
            `;
            } else {
                deliveryLine.innerHTML = '';
            }
        }

        // Add event listeners to all payment methods
        paymentMethods.forEach(method => {
            method.addEventListener('change', updateTotal);
        });

        // Initialize on page load
        updateTotal();
    });
</script>

<?php include 'includes/footer.php'; ?>