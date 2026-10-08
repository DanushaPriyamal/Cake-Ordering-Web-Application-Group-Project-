<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
if (!isset($_GET['slug'])) {
    redirect('products');
}
$slug = sanitize($_GET['slug']);
$cake = getCakeBySlug($slug);
if (!$cake) {
    redirect('products');
}
$cakeId = $cake['id']; // Use ID for cart and related products
$pageTitle = htmlspecialchars($cake['name']);
// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
   
    if ($quantity < 1) {
        $quantity = 1;
    }
   
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
   
    if (isset($_SESSION['cart'][$cakeId])) {
        $_SESSION['cart'][$cakeId] += $quantity;
    } else {
        $_SESSION['cart'][$cakeId] = $quantity;
    }
   
    $_SESSION['success_message'] = 'Product added to cart successfully!';
    redirect('cart');
}
// Get related products (same category)
$relatedCakes = getRelatedCakes($cake['category_id'], $cakeId, 4);
?>
    <style>
               :root {
            --primary: #FF6B6B;
            --primary-light: #FF8E8E;
            --secondary: #2D5D7B;
            --accent: #FFD166;
            --dark: #2E2E2E;
            --light: #F8F9FA;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
            --white: #FFFFFF;
            --success: #06D6A0;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 10px 20px rgba(0, 0, 0, 0.1);
            --border-radius: 16px;
            --border-radius-sm: 10px;
            --border-radius-lg: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: var(--dark);
            background-color: var(--white);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            color: var(--gray);
            margin: 20px 0;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .back-btn:hover {
            color: var(--primary);
        }

        .back-btn i {
            margin-right: 8px;
        }

        /* Product Detail Section */
        .product-detail {
            padding: 2rem 0;
        }

        .product-detail-container {
            display: flex;
            flex-wrap: wrap;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .product-image-container {
            flex: 1 1 45%;
            min-width: 300px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            background: var(--light);
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image-container:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            max-height: 500px;
            transition: transform 0.5s ease;
            padding: 20px;
        }

        .product-image-container:hover .product-image {
            transform: scale(1.03);
        }

        .product-info {
            flex: 1 1 45%;
            min-width: 300px;
        }

        .product-title {
            font-size: clamp(1.5rem, 2.5vw, 2.25rem);
            margin-bottom: 1rem;
            color: var(--dark);
            font-weight: 700;
            line-height: 1.2;
        }

        .price {
            font-size: clamp(1.25rem, 2vw, 1.75rem);
            color: var(--primary);
            font-weight: 700;
            margin: 1rem 0;
            display: flex;
            align-items: center;
        }

        .price::before {
            content: 'Rs';
            font-size: 0.8em;
            margin-right: 4px;
            opacity: 100;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .meta-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(45, 93, 123, 0.1);
            color: var(--secondary);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .meta-tag:hover {
            background: rgba(45, 93, 123, 0.2);
        }

        .meta-tag i {
            margin-right: 8px;
            font-size: 0.8rem;
        }

        .description {
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--light);
            border-radius: var(--border-radius-sm);
            border-left: 4px solid var(--primary);
        }

        .description h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--secondary);
            font-size: 1.25rem;
        }

        .description p {
            color: var(--dark);
            line-height: 1.7;
        }

        /* Add to Cart Form */
        .add-to-cart-form {
            margin-top: 2rem;
        }

        .quantity-selector {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-selector label {
            font-weight: 600;
            color: var(--dark);
        }

        .quantity-selector input {
            width: 80px;
            padding: 12px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            text-align: center;
            font-size: 1rem;
            transition: var(--transition);
        }

        .quantity-selector input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            gap: 8px;
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
            box-shadow: none;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        /* Related Products */
        .related-products {
            margin-top: 4rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            font-size: clamp(1.5rem, 2.5vw, 1.75rem);
            color: var(--dark);
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            margin: 1rem auto 0;
            border-radius: 2px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .product-card-image {
            position: relative;
            overflow: hidden;
            background: var(--light);
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            transition: var(--transition);
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .product-card-content {
            padding: 1.5rem;
        }

        .product-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-card .price {
            font-size: 1.25rem;
            margin: 0.5rem 0 1rem;
        }

        /* Image placeholder for missing images */
        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            color: var(--gray);
            font-size: 1rem;
            text-align: center;
            padding: 20px;
        }

        /* Animations */
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

        .product-detail {
            animation: fadeIn 0.6s ease-out;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .product-detail-container {
                flex-direction: column;
                gap: 2rem;
            }
            
            .product-meta {
                gap: 0.75rem;
            }
            
            .quantity-selector {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .product-grid {
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .product-detail {
                padding: 1.5rem 0;
            }
            
            .description {
                padding: 1rem;
                margin: 1.5rem 0;
            }

            .meta-tag {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-hover);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
    <?php include 'includes/header.php'; ?>
    <section class="product-detail">
        <div class="container">
            <a href="products" class="back-btn" id="backButton">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
           
            <div class="product-detail-container">
                <div class="product-image-container">
                    <?php if (!empty($cake['image'])): ?>
                        <img src="<?php echo getCakeImage($cake['image']); ?>" alt="<?php echo htmlspecialchars($cake['name']); ?>" class="product-image">
                    <?php else: ?>
                        <div class="image-placeholder">
                            <span>No image available</span>
                        </div>
                    <?php endif; ?>
                </div>
               
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($cake['name']); ?></h1>
                   
                    <div class="price"><?php echo number_format($cake['price'], 2); ?></div>
                   
                    <div class="product-meta">
                        <?php if (!empty($cake['brand_name'])): ?>
                            <span class="meta-tag">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($cake['brand_name']); ?>
                            </span>
                        <?php endif; ?>
                       
                        <?php if (!empty($cake['category_name'])): ?>
                           <a href="products?category=<?php echo urlencode($cake['category_slug']); ?>&filter_type=category" class="meta-tag">
                                <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($cake['category_name']); ?>
                           </a>
                        <?php endif; ?>
                       
                        <?php if (!empty($cake['subcategory_name'])): ?>
                            <a href="products?category=<?php echo urlencode($cake['subcategory_slug']); ?>&filter_type=subcategory" class="meta-tag">
                                <i class="fas fa-tags"></i> <?php echo htmlspecialchars($cake['subcategory_name']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                   
                    <div class="description">
                        <h3>Cake Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($cake['description'])); ?></p>
                    </div>
                   
                    <form method="post" class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" min="1" value="1" class="quantity-input">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
           
            <!-- Related Products -->
            <?php if (!empty($relatedCakes)): ?>
                <div class="related-products">
                    <h2 class="section-title">You Might Also Like</h2>
                    <div class="product-grid">
                        <?php foreach ($relatedCakes as $relatedCake): ?>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <?php if (!empty($relatedCake['image'])): ?>
                                        <img src="<?php echo getCakeImage($relatedCake['image']); ?>" alt="<?php echo htmlspecialchars($relatedCake['name']); ?>">
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <span>No image available</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-card-content">
                                    <h3><?php echo htmlspecialchars($relatedCake['name']); ?></h3>
                                    <div class="price"><?php echo number_format($relatedCake['price'], 2); ?></div>
                                    <a href="product-detail?slug=<?php echo urlencode($relatedCake['slug']); ?>" class="btn btn-secondary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent default back button behavior and use history API
            const backButton = document.getElementById('backButton');
            if (backButton) {
                backButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Check if we have a history entry to go back to
                    if (window.history.length > 1) {
                        window.history.back();
                    } else {
                        window.location.href = this.href;
                    }
                });
            }
           
            // Quantity input validation
            const quantityInput = document.querySelector('.quantity-input');
            if (quantityInput) {
                quantityInput.addEventListener('change', function() {
                    if (this.value < 1) {
                        this.value = 1;
                    }
                });
            }
           
            // Show toast notification if added to cart
            <?php if (isset($_SESSION['success_message'])): ?>
                showToast('<?php echo $_SESSION['success_message']; ?>');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
           
            // Toast notification function
            function showToast(message) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.textContent = message;
                document.body.appendChild(toast);
               
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
               
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        });
    </script>