<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Yummy Cake | Delicious Cakes in Sri Lanka";

$featuredCakes = getFeaturedCakes();
?>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  body {
    background-color: #fff9f9;
    color: #333;
    overflow-x: hidden;
    scroll-behavior: smooth;
  }

  a {
    text-decoration: none;
    color: inherit;
  }

  .container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
  }

  /*  HERO SECTION */
  .hero {
    background: linear-gradient(135deg, #fbcfe8, #fde68a);
    text-align: center;
    padding: 6rem 1rem;
    color: #2d3748;
    position: relative;
    overflow: hidden;
  }

  .hero::before {
    content: "";
    position: absolute;
    top: -50px;
    left: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite alternate;
  }

  @keyframes float {
    from {
      transform: translateY(0px);
    }

    to {
      transform: translateY(25px);
    }
  }

  .hero h1 {
    font-size: 4rem;
    font-weight: 900;
    background: linear-gradient(to right, #ec4899, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 1rem;
    animation: fadeInDown 1s ease;
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .hero p {
    font-size: 1.2rem;
    color: #4b5563;
    margin-bottom: 2rem;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.8;
  }

  .btn-primary {
    background: linear-gradient(90deg, #ec4899, #f59e0b);
    color: #fff;
    padding: 0.9rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(236, 72, 153, 0.3);
  }

  .btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(236, 72, 153, 0.5);
  }

  /*  FEATURED PRODUCTS */
  .best-deals {
    padding: 4rem 1rem;
    text-align: center;
    background: #fff;
  }

  .best-deals h2 {
    font-size: 2.2rem;
    color: #ec4899;
    margin-bottom: 2.5rem;
    position: relative;
  }

  .deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    justify-items: center;
  }

  .deal-card {
    background: #fff;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    width: 100%;
    max-width: 300px;
  }

  .deal-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  }

  .deal-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-bottom: 3px solid #fbcfe8;
  }

  .discount-badge-fp {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ec4899;
    color: #fff;
    padding: 5px 10px;
    font-size: 0.9rem;
    border-radius: 6px;
    font-weight: 600;
  }

  .deal-card h3 {
    font-size: 1.2rem;
    margin: 1rem;
    color: #374151;
  }

  .price-section {
    display: flex;
    justify-content: center;
    gap: 1rem;
    align-items: baseline;
    margin-bottom: 0.8rem;
  }

  .original-price {
    text-decoration: line-through;
    color: #9ca3af;
  }

  .discounted-price {
    color: #ec4899;
    font-weight: 700;
    font-size: 1.2rem;
  }

  .savings {
    background: #fef3c7;
    color: #92400e;
    padding: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: 0 0 1rem 1rem;
  }

  /*ABOUT SECTION*/
  section[aria-label="About our bakery"] {
    background: linear-gradient(135deg, #fff0f5, #fffbea);
    text-align: center;
    padding: 5rem 1rem;
  }

  section[aria-label="About our bakery"] h2 {
    font-size: 2.3rem;
    background: linear-gradient(to right, #ec4899, #f59e0b);
    -webkit-background-clip: text;
    color: transparent;
    font-weight: 800;
    margin-bottom: 1.5rem;
  }

  section[aria-label="About our bakery"] p {
    color: #4b5563;
    line-height: 1.8;
    max-width: 850px;
    margin: 0 auto 1.5rem;
  }

  section[aria-label="About our bakery"] ul {
    margin-top: 1rem;
    list-style: none;
    color: #475569;
    font-size: 1rem;
  }

  section[aria-label="About our bakery"] li {
    padding: 0.3rem 0;
  }

  /* FLOATING BUTTONS */
  .whatsapp-button,
  .facebook-button {
    position: fixed;
    bottom: 25px;
    width: 55px;
    height: 55px;
    z-index: 1000;
  }

  .whatsapp-button {
    right: 25px;
  }

  .facebook-button {
    right: 90px;
  }

  .whatsapp-button img,
  .facebook-button img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    transition: transform 0.3s ease;
  }

  .whatsapp-button img:hover,
  .facebook-button img:hover {
    transform: scale(1.1);
  }

  /*  RESPONSIVE */
  @media (max-width: 768px) {
    .hero h1 {
      font-size: 2.8rem;
    }

    .hero p {
      font-size: 1rem;
    }

    .btn-primary {
      padding: 0.7rem 1.5rem;
    }

    .deals-grid {
      gap: 1.5rem;
    }
  }

  /* secret login h1*/
  #secret-heading {
    cursor: default;
    /* not showing clickable hand */
    user-select: none;
  }

  #secret-heading:active {
    opacity: 0.8;
    /* optional little feedback when clicked */
  }
</style>

<?php include 'includes/header.php'; ?>

<!-- HERO -->
<section class="hero" aria-label="Yummy Cake introduction">
  <div class="container">
    <h1 id="secret-heading">YUMMY CAKES</h1>

    <p>Freshly baked cakes for every celebration! Order your favorite cake online and get it delivered anywhere in Sri Lanka 🎂</p>
    <a href="products" class="btn-primary">Order Now</a>
  </div>
</section>

<!-- FEATURED CAKES -->
<section class="best-deals" aria-label="Best cake deals">
  <div class="container">
    <h2>🎂 Featured Cakes & Best Deals</h2>
    <div class="deals-grid">
      <?php foreach ($featuredCakes as $cake):
        $discount_percentage = rand(5, 10);
        $original_price = $cake['price'] * (1 + ($discount_percentage / 100));
        $discounted_price = $cake['price'];
      ?>
        <article class="deal-card" itemscope itemtype="https://schema.org/Product">
          <a href="product-detail?slug=<?php echo urlencode($cake['slug']); ?>" class="btn btn-secondary">
            <div class="discount-badge-fp">-<?php echo $discount_percentage; ?>%</div>
            <img src="<?php echo getCakeImage($cake['image']); ?>" alt="<?php echo htmlspecialchars($cake['name']); ?>">
            <h3><?php echo htmlspecialchars($cake['name']); ?></h3>

            <div class="price-section">
              <div class="original-price">Rs. <?php echo number_format($original_price, 2); ?></div>
              <div class="discounted-price">Rs. <?php echo number_format($discounted_price, 2); ?></div>
            </div>
            <div class="savings">You save Rs. <?php echo number_format($original_price - $discounted_price, 2); ?></div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section aria-label="About our bakery">
  <h2>About Yummy Cake</h2>
  <p>Since 2018, <strong>Yummy Cake</strong> has been Sri Lanka’s most beloved online cake store, bringing joy to thousands with our irresistible range of cakes. From timeless classics to creative custom designs, we specialize in making every celebration sweeter. Trusted for quality, loved for taste — Yummy Cake is where happiness is baked fresh daily.</p>
  <p>3. Crafted with the finest ingredients and a whole lot of love, every Yummy Cake is soft, indulgent, and unforgettable. Whether it’s a birthday, anniversary, or just because — we’re here to make your special moments even sweeter.</p>
</section>

<!-- WHATSAPP  -->
<div class="whatsapp-button">
  <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=<?php echo urlencode(WHATSAPP_MESSAGE); ?>" target="_blank">
    <img src="assets/images/wai.png" alt="Chat on WhatsApp">
  </a>
</div>



<script>
  // secret login h1
  document.addEventListener('DOMContentLoaded', function() {
    const heading = document.getElementById('secret-heading');
    if (!heading) return;

    const adminURL = "<?php echo SITE_URL; ?>admin/spk-st-wl";
    const indexURL = "<?php echo SITE_URL; ?>";

    let clicks = 0;
    const needed = 5; // 👈 number of clicks to go admin
    const timeout = 2000; // reset after 2s
    let timer = null;

    heading.addEventListener('click', function() {
      clicks++;

      if (timer) clearTimeout(timer);
      timer = setTimeout(() => {
        if (clicks < needed) {
          window.location.href = indexURL;
        }
        clicks = 0;
      }, timeout);

      if (clicks >= needed) {
        clearTimeout(timer);
        clicks = 0;
        window.location.href = adminURL;
      }
    });

    // For mobile users
    heading.addEventListener('touchend', function(e) {
      e.preventDefault();
      heading.click();
    });
  });
</script>
<?php include 'includes/footer.php'; ?>