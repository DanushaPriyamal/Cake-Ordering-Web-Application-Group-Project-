<!DOCTYPE html>
<html lang="en">

<head>
  <title><?php echo $pageTitle ?? 'Yummy Cake'; ?></title>
  <link rel="icon" type="image/png" href="assets/images/favicon.jpg">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo defined('SITE_NAME') ? SITE_NAME : 'Yummy Cake'; ?></title>
  <link rel="icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/print.css" media="print">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
  * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

/*  HEADER */
.site-header {
  position: sticky;
  top: 0;
  width: 100%;
  z-index: 1000;
  background: linear-gradient(90deg, #ffd6d6, #fff0f5);
  padding: 1rem 0;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  padding: 0 1rem;
}

/*LOGO */
.logo img {
  height: 55px;
  transition: transform 0.4s ease, filter 0.4s ease;
}

.logo img:hover {
  transform: scale(1.15);
  filter: drop-shadow(0 5px 10px rgba(236,72,153,0.5));
}

.logo h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #ec4899;
  background: linear-gradient(45deg, #ec4899, #fbbf24);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* NAVIGATION  */
.main-nav ul {
  display: flex;
  gap: 2rem;
  list-style: none;
  align-items: center;
}



.main-nav ul li a {
  position: relative;
  display: inline-block;  /* ✅ Add this line */
  font-weight: 500;
  font-size: 1rem;
  color: #4b5563;
  padding: 0.5rem 0;
  transition: all 0.3s ease;
  text-decoration: none; /* optional for clarity */
}

/* Animated underline */
.main-nav ul li a::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -5px;
  height: 3px;
  width: 0;
  background: linear-gradient(90deg, #ec4899, #fbbf24);
  border-radius: 3px;
  transition: width 0.4s ease;
}

.main-nav ul li a:hover::after,
.main-nav ul li a.active::after {
  width: 100%;
}

.main-nav ul li a:hover {
  color: #ec4899;
}

.main-nav ul li a.active {
  color: #ec4899;
}

.main-nav ul li a.active::after {
  width: 100%;
}


/*CART ICON */
.cart-icon {
  position: relative;
  transition: transform 0.3s ease;
}

.cart-icon i {
  font-size: 1.6rem;
  color: #ec4899;
  transition: transform 0.3s ease, filter 0.3s ease;
}

.cart-icon:hover i {
  transform: scale(1.25);
  filter: drop-shadow(0 4px 8px rgba(236,72,153,0.5));
}

.cart-count {
  position: absolute;
  top: -10px;
  right: -12px;
  background: #f59e0b;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0 7px;
  border-radius: 50%;
}

/*HAMBURGER MENU */
.menu-toggle {
  display: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #ec4899;
  transition: transform 0.3s ease;
  z-index: 1010;
}

.menu-toggle:hover {
  transform: scale(1.2);
}

/* MOBILE NAV */
@media (max-width: 992px) {
  /* Overlay background */
  body.menu-open::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    z-index: 999;
  }

  .main-nav ul {
    position: fixed;
    top: 0;
    right: -100%;
    height: 100%;
    width: 300px;
    background: linear-gradient(160deg, #ffd6d6, #fff0f5);
    flex-direction: column;
    gap: 1.5rem;
    padding: 5rem 2rem;
    box-shadow: -5px 0 20px rgba(0,0,0,0.15);
    border-radius: 0 0 0 15px;
    transition: right 0.5s ease;
    z-index: 1000;
  }

  .main-nav ul.show {
    right: 0;
  }

  .main-nav ul li a {
    font-size: 1.2rem;
    color: #4b5563;
  }

  .menu-toggle {
    display: block;
  }
}

/*RESPONSIVE SMALLER  */
@media (max-width: 768px) {
  .logo img {
    height: 45px;
  }

  .cart-icon i {
    font-size: 1.4rem;
  }

  .main-nav ul li a {
    font-size: 1.1rem;
  }
}
</style>
  
</head>

<body class="<?php echo $bodyClass; ?>">
  <header class="site-header">
    <div class="container">
      <div class="header-content">
        <div class="logo">
          <a href="<?php echo SITE_URL; ?>">
            <?php if (file_exists('<?php echo SITE_URL; ?>/assets/images/logo.jpg')): ?>
              <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Yummy Cake Logo">
            <?php else: ?>
              <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Yummy Cake Logo">
            <?php endif; ?>
          </a>
        </div>

        <div class="menu-toggle" onclick="toggleMenu()">
          <i class="fas fa-bars"></i>
        </div>

        <nav class="main-nav" id="mainNav">
          <ul>
            <li><a href="<?php echo SITE_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">Our Products</a></li>
            <li><a href="<?php echo SITE_URL; ?>/about" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">About</a></li>
            <li><a href="<?php echo SITE_URL; ?>/contact" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contact</a></li>

            <?php if (isAdminLoggedIn()): ?> 
              <li><a href="<?php echo SITE_URL; ?>/admin/dashboard" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
              <li><a href="<?php echo SITE_URL; ?>/admin/logout">Logout</a></li>

            <?php elseif (isCustomerLoggedIn()): ?> 
              <li><a href="<?php echo SITE_URL; ?>/account" class="<?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'active' : '' ?>">My Account</a></li>
              <li><a href="<?php echo SITE_URL; ?>/logout">Logout</a></li>

            <?php else: ?> 
              <li><a href="<?php echo SITE_URL; ?>/login" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>">Login</a></li>
              <li><a href="<?php echo SITE_URL; ?>/register" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : '' ?>">Register</a></li>
            <?php endif; ?>
          </ul>
        </nav>

        <div class="cart-icon">
          <a href="<?php echo SITE_URL; ?>/cart">
            <i class="fas fa-shopping-cart"></i> 

            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
              <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
            <?php endif; ?>
          </a>
        </div>

      </div>
    </div>
  </header>


<script>
const navUL = document.querySelector('#mainNav ul'); 
const body = document.body;

function toggleMenu() {
  navUL.classList.toggle('show');
  body.classList.toggle('menu-open'); // overlay
}

// Close menu when clicking outside
document.addEventListener('click', function(e) {
  const toggle = document.querySelector('.menu-toggle');
  if (!navUL.contains(e.target) && !toggle.contains(e.target)) {
    navUL.classList.remove('show');
    body.classList.remove('menu-open');
  }
});
</script>




