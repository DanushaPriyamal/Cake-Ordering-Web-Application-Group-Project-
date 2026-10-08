<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "About Yummy Cake - Sri Lanka's Best Online Cake Shop | yummycake.lk";
?>

<style>
:root {
  --primary-color: #ff6b6b;
  --secondary-color: #f59e0b;
  --accent-color: #6b5b95;
  --dark-color: #1e293b;
  --light-color: #fff7f2;
  --gray-color: #64748b;
  --success-color: #10b981;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--dark-color);
  line-height: 1.6;
  overflow-x: hidden;
}

/* About Section */
.about-section {
  padding: 2.5rem 50;
  background-color: var(--light-color);
  position: relative;
}

.about-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(245, 158, 11, 0.05) 100%);
  z-index: 0;
}

.container {
  position: relative;
  z-index: 1;
}

/* About Image */
.about-image {
  position: relative;
  overflow: hidden;
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transform: perspective(1000px) rotateY(-5deg);
  transition: all 0.5s ease;
  height: 100%;
  min-height: 400px;
}

.about-image:hover {
  transform: perspective(1000px) rotateY(0deg);
}

.about-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.about-image:hover img {
  transform: scale(1.03);
}

/* Experience Badge */
.experience-badge {
  position: absolute;
  bottom: -1.5rem;
  right: -1.5rem;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  text-align: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  animation: pulse 2s infinite;
  z-index: 2;
}

.years {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.experience-badge .text {
  font-size: 0.9rem;
  opacity: 0.9;
}

/* About Content */
.about-content {
  padding: 2rem;
}

.about-content h1 {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  position: relative;
  display: inline-block;
}

.about-content h1::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 0;
  width: 50px;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
  border-radius: 2px;
}

.about-content .lead {
  font-size: 1.25rem;
  color: var(--gray-color);
  margin-bottom: 2rem;
}

.about-text p {
  margin-bottom: 1.5rem;
  color: var(--dark-color);
  font-size: 1.1rem;
}

/* Specialties */
.specialties {
  background-color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-top: 2rem;
  border-left: 4px solid var(--primary-color);
  transition: transform 0.3s ease;
}

.specialties:hover {
  transform: translateY(-5px);
}

.specialties h2 {
  color: var(--primary-color);
  margin-bottom: 1rem;
  font-weight: 700;
  font-size: 1.5rem;
}

.specialties ul {
  list-style: none;
  padding: 0;
}

.specialties li {
  margin-bottom: 0.75rem;
  position: relative;
  padding-left: 2rem;
  color: var(--dark-color);
}

.specialties li i {
  color: var(--success-color);
  position: absolute;
  left: 0;
  top: 0.25rem;
}

/* Shop Images */
.shop-images {
  margin-top: 5rem;
  padding: 0 1rem;
}

.shop-images h2 {
  font-size: 2rem;
  font-weight: 800;
  color: var(--dark-color);
  margin-bottom: 3rem;
  text-align: center;
  position: relative;
}

.shop-images h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
  border-radius: 2px;
}

.shop-images .row {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 2rem;
}

.shop-images .col-md-4 {
  flex: 0 0 calc(33.333% - 1.5rem);
  max-width: calc(33.333% - 1.5rem);
  padding: 0;
}

.shop-images img {
  border-radius: 1rem;
  transition: all 0.5s ease;
  width: 100%;
  height: 220px;
  object-fit: cover;
  object-position: center;
  filter: grayscale(20%);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.shop-images img:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  filter: grayscale(0%);
  z-index: 2;
}

/* Achievements */
.achievements {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  border-radius: 1rem;
  margin-top: 5rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.achievement-item {
  padding: 1.5rem;
  color: white;
  position: relative;
  transition: transform 0.3s ease;
}

.achievement-item:hover {
  transform: translateY(-5px);
}

.achievement-item h3 {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.achievement-item p {
  opacity: 0.9;
  font-size: 1.1rem;
}

/* SEO Content */
.seo-content {
  background-color: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin-top: 3rem;
}

.seo-content h2 {
  color: var(--primary-color);
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  position: relative;
}

.seo-content h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 0;
  width: 50px;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
  border-radius: 2px;
}

.seo-content h3 {
  color: var(--secondary-color);
  font-size: 1.4rem;
  margin: 1.5rem 0 1rem;
}

.seo-content p {
  margin-bottom: 1rem;
  line-height: 1.7;
}

/* Animations */
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

.about-content,
.about-image,
.shop-images h2,
.achievement-item {
  animation: fadeIn 1s ease forwards;
}

.achievement-item:nth-child(1) { animation-delay: 0.2s; }
.achievement-item:nth-child(2) { animation-delay: 0.4s; }
.achievement-item:nth-child(3) { animation-delay: 0.6s; }
.achievement-item:nth-child(4) { animation-delay: 0.8s; }

/* Responsive Design */
@media (max-width: 992px) {
  .about-image { min-height: 350px; margin-bottom: 3rem; transform: none; }
  .about-image:hover { transform: none; }
  .experience-badge { bottom: -1rem; right: -1rem; padding: 1rem; }
  .years { font-size: 2rem; }
  .shop-images .col-md-4 { flex: 0 0 calc(50% - 1rem); max-width: calc(50% - 1rem); }
  .shop-images img { height: 200px; }
}

@media (max-width: 768px) {
  .about-section { padding: 3rem 0; }
  .about-content h1 { font-size: 2rem; }
  .shop-images h2 { font-size: 1.75rem; margin-bottom: 2rem; }
  .shop-images img { height: 180px; }
  .achievement-item h3 { font-size: 2rem; }
  .achievement-item { margin-bottom: 1.5rem; }
  .specialties { padding: 1rem; }
  .seo-content h2 { font-size: 1.5rem; }
  .seo-content h3 { font-size: 1.2rem; }
}

@media (max-width: 576px) {
  .about-image { min-height: 300px; }
  .experience-badge { bottom: -0.5rem; right: -0.5rem; padding: 0.75rem; }
  .years { font-size: 1.75rem; }
  .shop-images .col-md-4 { flex: 0 0 100%; max-width: 100%; }
  .shop-images img { height: 160px; max-width: 400px; margin: 0 auto; }
  .shop-images .row { gap: 1rem; }
  .about-content h1 { font-size: 1.8rem; }
  .seo-content { padding: 1.5rem; }
}
</style>

<?php include 'includes/header.php'; ?>

<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="assets/images/yummycake.jpg" alt="Yummy Cake Online Shop Sri Lanka" class="img-fluid rounded">
                    <div class="experience-badge">
                        <span class="years">8+</span>
                        <span class="text">Years of Baking</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h1 class="mb-4">About Yummy Cake - Sri Lanka's Favorite Online Cake Shop</h1>
                    <p class="lead">Freshly baked cakes, cupcakes, and pastries delivered across Sri Lanka</p>
                    
                    <div class="about-text">
                        <p>Welcome to <strong>Yummy Cake</strong> (yummycake.lk), your premier online destination for all types of cakes and sweet treats. We specialize in creating delicious cakes for birthdays, weddings, and all special occasions.</p>
                        
                        <p>Since our inception, we've focused on quality ingredients, artistic designs, and prompt delivery, making us one of the most trusted online cake shops in Sri Lanka.</p>
                        
                        <div class="specialties mt-4">
                            <h2>Why Choose Yummy Cake?</h2>
                            <ul>
                                <li><i class="fas fa-check-circle"></i> Wide variety of <strong>cakes, cupcakes & pastries</strong></li>
                                <li><i class="fas fa-check-circle"></i> Customizable <strong>birthday & wedding cakes</strong></li>
                                <li><i class="fas fa-check-circle"></i> 100% fresh ingredients & hygienic baking</li>
                                <li><i class="fas fa-check-circle"></i> Islandwide <strong>delivery available</strong></li>
                                <li><i class="fas fa-check-circle"></i> Friendly customer support & personalized orders</li>
                                <li><i class="fas fa-check-circle"></i> Affordable pricing with <strong>premium quality</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shop Images Section -->
        <div class="shop-images mt-5">
            <h2 class="text-center mb-4">Our Cake Creations</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <img src="assets/images/yummycake.jpg" alt="Freshly baked chocolate cake" class="img-fluid rounded shadow-sm">
                </div>
                <div class="col-md-4">
                    <img src="assets/images/yummycake.jpg" alt="Beautifully decorated birthday cake" class="img-fluid rounded shadow-sm">
                </div>
                <div class="col-md-4">
                    <img src="assets/images/yummycake.jpg" alt="Delicious cupcakes and pastries" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
        
        <!-- Achievements -->
        <div class="achievements mt-5 py-4">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="achievement-item">
                        <h3>50,000+</h3>
                        <p>Cakes Delivered</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-item">
                        <h3>10,000+</h3>
                        <p>Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-item">
                        <h3>500+</h3>
                        <p>Cake Designs</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="achievement-item">
                        <h3>100%</h3>
                        <p>Freshness Guaranteed</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional SEO Content -->
        <div class="seo-content mt-5">
            <h2 class="mb-4">Yummy Cake - Your Sweet Partner in Sri Lanka</h2>
            <p>At <strong>Yummy Cake (yummycake)</strong>, we believe every celebration deserves a perfect cake. Our mission is to deliver freshly baked, high-quality cakes across Sri Lanka with utmost care and love.</p>
            
            <h3 class="mt-4">Our Cake Collection</h3>
            <p>We offer a wide range of cakes including chocolate, vanilla, fruit, customized birthday cakes, and wedding cakes. Every cake is made using fresh ingredients and beautifully decorated.</p>
            
            <h3 class="mt-4">Ordering & Delivery</h3>
            <p>Order online via our website and get your favorite cakes delivered anywhere in Sri Lanka. We ensure timely delivery and safe packaging for all orders.</p>
            
            <h3 class="mt-4">Visit Us Today</h3>
            <p>Explore our gallery, choose your favorite cake, and place your order at <strong>yummycake</strong> Celebrate every moment with Yummy Cake's delicious creations!</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
