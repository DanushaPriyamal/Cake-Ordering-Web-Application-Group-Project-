<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Cart";


// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cakeId = (int)$_POST['cake_id'];
    if (isset($_SESSION['cart'][$cakeId])) {
        unset($_SESSION['cart'][$cakeId]);
        $_SESSION['success_message'] = 'Item removed from cart successfully!';
    }
    redirect('cart');
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cakeId => $quantity) {
        $cakeId = (int)$cakeId;
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $_SESSION['cart'][$cakeId] = $quantity;
        } else {
            unset($_SESSION['cart'][$cakeId]);
        }
    }
    $_SESSION['success_message'] = 'Cart updated successfully!';
    redirect('cart');
}

// Handle checkout redirection (without login check)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_checkout'])) {
    redirect('checkout');
}


$cartItems = getCartItems();
$cartTotal = getCartTotal();
?>
 <style>

.cart-modern-wrapper {
  padding: 2rem 0;
  justify-content: center; /* Center items vertically */
  align-items: center;
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
  background-color: #f8f9fa;
  min-height: 70vh;
  box-sizing: border-box;
  width: 80%;
  margin: 0 auto; /* optional: center the wrapper itself horizontally */

}

.cart-modern-title {
  font-size: clamp(1.8rem, 5vw, 2.5rem);
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 2rem;
  text-align: center;
  position: relative;
  padding-bottom: 1rem;
  width: 100%;
}

.cart-modern-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 4px;
  background: linear-gradient(90deg, #ff6b6b, #feca57);
  border-radius: 2px;
}

.cart-modern-alert {
  padding: 1rem;
  margin: 0 auto 2rem;
  border-radius: 8px;
  font-weight: 500;
  text-align: center;
  animation: fadeIn 0.5s ease-out;
  max-width: 100%;
  box-sizing: border-box;
}

.cart-modern-alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.cart-modern-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  animation: slideUp 0.6s ease-out;
}

.cart-modern-table thead {
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  color: white;
}

.cart-modern-table th {
  padding: 1.2rem 1rem;
  text-align: left;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.cart-modern-table td {
  padding: 1.5rem 1rem;
  border-bottom: 1px solid #e9ecef;
  transition: all 0.3s ease;
}

.cart-modern-table tr:last-child td {
  border-bottom: none;
}

.cart-modern-table tr:hover td {
  background-color: #f8f9fa;
}

.cart-modern-product-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.cart-modern-product-info img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.cart-modern-product-info img:hover {
  transform: scale(1.05);
}

.cart-modern-product-info h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #2c3e50;
  font-weight: 600;
}

.cart-modern-price {
  font-weight: 600;
  color: #2c3e50;
}

.cart-modern-quantity input {
  width: 70px;
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 6px;
  text-align: center;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.cart-modern-quantity input:focus {
  border-color: #2575fc;
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
}

.cart-modern-total {
  font-weight: 700;
  color: #2c3e50;
}

.cart-modern-action .btn-danger {
  background-color: #ff6b6b;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.cart-modern-action .btn-danger:hover {
  background-color: #ff5252;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
}



.cart-modern-actions {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 2rem;
  animation: fadeIn 0.8s ease-out;
  width: 100%;
}

.cart-modern-actions .btn {
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  flex: 1 1 auto;
  min-width: 200px;
  text-align: center;
  box-sizing: border-box;
}

.cart-modern-actions .btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
}

.cart-modern-actions .btn-secondary:hover {
  background-color: #5a6268;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

.cart-modern-actions .btn-primary {
  background-color: #2575fc;
  color: white;
  border: none;
}

.cart-modern-actions .btn-primary:hover {
  background-color: #1a68e8;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(37, 117, 252, 0.3);
}

.cart-modern-actions .btn-success {
  background-color: #28a745;
  color: white;
  border: none;
}

.cart-modern-actions .btn-success:hover {
  background-color: #218838;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.cart-modern-empty {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  animation: fadeIn 0.6s ease-out;
  margin: 0 auto;
  max-width: 100%;
  box-sizing: border-box;
}

.cart-modern-empty p {
  font-size: 1.2rem;
  color: #6c757d;
  margin-bottom: 2rem;
}

.cart-modern-empty .btn-primary {
  background-color: #2575fc;
  color: white;
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  display: inline-block;
}

.cart-modern-empty .btn-primary:hover {
  background-color: #1a68e8;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(37, 117, 252, 0.3);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  .cart-modern-wrapper {
      padding: 1rem;
      width: 100%;
  }
  
  .cart-modern-table {
      display: block;
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
  }
  
  .cart-modern-table thead {
      display: none;
  }
  
  .cart-modern-table tbody {
      display: block;
      width: 100%;
  }
  
  .cart-modern-table tr {
      display: flex;
      flex-direction: column;
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      width: 100%;
      box-sizing: border-box;
  }
  
  .cart-modern-table td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.8rem 0;
      border-bottom: 1px solid #e9ecef;
      width: 100%;
  }
  
  .cart-modern-table td:last-child {
      border-bottom: none;
  }
  
  .cart-modern-table td::before {
      content: attr(data-label);
      font-weight: 600;
      color: #6c757d;
      margin-right: 1rem;
      flex: 0 0 120px;
  }
  
  /* Specific fixes for subtotal row */
  .cart-modern-table tfoot tr {
      display: flex;
      flex-direction: column;
      padding: 1rem;
      margin-top: -1rem; /* Reduce gap */
  }
  
  .cart-modern-table tfoot td {
      display: flex;
      justify-content: space-between;
      padding: 0.8rem 0;
      width: 100%;
  }
  
  
  .cart-modern-table tfoot td[colspan="3"] {
      display: none;
  }
  
  .cart-modern-table tfoot .cart-modern-subtotal::before {
    content: none !important;
}
  
  .cart-modern-product-info {
      flex-direction: column;
      text-align: center;
      gap: 0.8rem;
      width: 100%;
  }
  
  .cart-modern-product-info img {
      width: 100%;
      height: auto;
      max-width: 200px;
      max-height: 200px;
      margin: 0 auto;
  }
  
  .cart-modern-actions {
      flex-direction: column;
      gap: 1rem;
  }
  
  .cart-modern-actions .btn {
      width: 100%;
      min-width: unset;
  }
  
  .cart-modern-quantity input {
      width: 60px;
  }
  
  .cart-modern-action .btn-danger {
      width: 100%;
  }
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
      opacity: 0;
      transform: translateY(20px);
  }
  to { 
      opacity: 1;
      transform: translateY(0);
  }
}

/* Button Pulse Animation */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.cart-modern-actions .btn-success {
  animation: pulse 2s infinite;
}

.cart-modern-actions .btn-success:hover {
  animation: none;
}

/* Ensure full width on mobile */
@media (max-width: 480px) {
  .container {
      padding-left: 15px;
      padding-right: 15px;
      width: 100%;
      max-width: 100%;
  }
  
  body {
      overflow-x: hidden;
  }
  
  html, body {
      width: 100%;
      position: relative;
  }
  
  /* Additional subtotal mobile fixes */
  .cart-modern-table tfoot tr {
      padding: 0.5rem 1rem;
  }
  
  .cart-modern-table tfoot td {
      padding: 0.5rem 0;
  }


}

@media (max-width:768px){
  .cart-modern-table tfoot tr.no-card {
      display: table-row !important;
      background: #f9f9f9 !important;
      box-shadow: none !important;
      padding: 0 !important;
      border-radius: 0 !important;
  }
  .cart-modern-table tfoot tr.no-card td {
      display: table-cell !important;
      text-align: right !important;
      border: none !important;
  }
}
.btn-secondary {
    text-decoration: none;
}
.btn-primary{
  text-decoration: none;
}


</style>

<?php
include 'includes/header.php';
?>

<section class="cart-modern-wrapper">
    <div class="container">
        <h1 class="cart-modern-title">Your Shopping Cart</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="cart-modern-alert cart-modern-alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($cartItems)): ?>
            <form method="post" action="cart">
                <table class="cart-modern-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td class="cart-modern-product-info" data-label="Product">
                                    <img src="<?php echo getCakeImage($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div>
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    </div>
                                </td>
                                <td class="cart-modern-price" data-label="Price">Rs.<?php echo number_format($item['price'], 2); ?></td>
                                <td class="cart-modern-quantity" data-label="Quantity">
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1">
                                </td>
                                <td class="cart-modern-total" data-label="Total">Rs.<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td class="cart-modern-action" data-label="Action">
                                    <button type="submit" name="remove_item" class="btn btn-danger">
                                        Remove
                                        <input type="hidden" name="cake_id" value="<?php echo $item['id']; ?>">
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                  
                        
                       <tr class="no-card" style="background:#f9f9f9; border-top:2px solid #ddd; font-weight:bold;">
                           <td colspan="3"></td>
                           <td style="text-align:right; padding:10px; font-size: clamp(14px, 1.8vw, 18px); color:#2c3e50; width:50%;">Subtotal:</td>
                           <td style="color:#27ae60; font-size: clamp(17px, 2vw, 20px); padding:10px; text-align:right; width:25%;">Rs.<?php echo number_format($cartTotal, 2); ?></td>
                       </tr>


                    </tfoot>
                </table>
                
                <div class="cart-modern-actions">
                    <a href="products" class="btn btn-secondary">Continue Shopping</a>
                    <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                    <button type="submit" name="proceed_checkout" class="btn btn-success">Proceed to Checkout</button>
                </div>
            </form>
        <?php else: ?>
            <div class="cart-modern-empty">
                <p>Your cart is empty.</p>
                <a href="products" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

