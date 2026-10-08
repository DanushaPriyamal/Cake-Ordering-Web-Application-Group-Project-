<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Contact Yummy Cake - Online Cake Shop Sri Lanka | Custom Cakes & Desserts";
?>

<style>
    :root {
        --primary: #FF6F61;
        --secondary: #FFB347;
        --accent: #F7CAC9;
        --dark-bg: #FFF5F5;
        --card-bg: #FFFFFF;
        --text-primary: #333333;
        --text-secondary: #666666;
        --icon-cake: #FF6F61;
        --icon-cupcake: #FFB347;
        --icon-dessert: #F7CAC9;
        --icon-whatsapp: #25D366;
        --icon-email: #FF6F61;
        --icon-phone: #FF6F61;
        --icon-location: #FF6F61;
        --icon-time: #FFB347;
        --space-xs: 0.5rem;
        --space-sm: 1rem;
        --space-md: 1.5rem;
        --space-lg: 2rem;
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.2);
        --transition-fast: 0.2s ease;
        --transition-normal: 0.3s ease;
        --transition-slow: 0.5s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', 'Noto Sans Sinhala', system-ui, sans-serif;
        background-color: var(--dark-bg);
        color: var(--text-primary);
        line-height: 1.6;
    }

    h1,
    h2,
    h3 {
        font-weight: 700;
        line-height: 1.3;
    }

    h1 {
        font-size: clamp(1.75rem, 5vw, 2.5rem);
        text-align: center;
        margin: var(--space-lg) 0;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        position: relative;
    }

    h1::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 2px;
    }

    h2 {
        font-size: clamp(1.25rem, 3vw, 1.75rem);
        margin-bottom: var(--space-md);
        display: flex;
        align-items: center;
        gap: var(--space-xs);
    }

    h3 {
        font-size: clamp(1.1rem, 2vw, 1.3rem);
        margin-bottom: var(--space-sm);
    }

    .contact-section {
        padding: var(--space-lg) var(--space-sm);
        min-height: 100vh;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        /* padding: 0 var(--space-sm); */
    }

    @media (max-width: 768px) {
    .container {
        padding: 0 10px; 
    }
}

    .row {
        display: flex;
        flex-direction: column;
        gap: var(--space-md);
    }

    @media (min-width: 768px) {
        .row {
            flex-direction: row;
        }

        .col-lg-6 {
            width: 50%;
        }
    }

    .col-lg-6 {
        width: 100%;
    }

    .contact-card,
    .map-container {
        background-color: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: var(--space-md);
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: var(--transition-normal);
    }

    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .services-list {
        list-style: none;
        margin-bottom: var(--space-lg);
    }

    .services-list li {
        padding: var(--space-sm);
        margin-bottom: var(--space-xs);
        background-color: rgba(255, 111, 97, 0.08);
        border-radius: var(--radius-sm);
        transition: var(--transition-fast);
        display: flex;
        align-items: center;
        border-left: 3px solid transparent;
    }

    .services-list li:hover {
        background-color: rgba(255, 111, 97, 0.15);
        border-left: 3px solid var(--primary);
        transform: translateX(5px);
    }

    .services-list i,
    .contact-method i,
    .business-hours i {
        margin-right: var(--space-xs);
        font-size: 1.1em;
        min-width: 24px;
        text-align: center;
    }

    .contact-methods {
        display: flex;
        flex-direction: column;
        gap: var(--space-xs);
    }

    .contact-method {
        display: flex;
        align-items: center;
        padding: var(--space-sm);
        background-color: rgba(255, 111, 97, 0.08);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        text-decoration: none;
        transition: var(--transition-fast);
    }

    .contact-method:hover {
        background-color: rgba(255, 111, 97, 0.15);
        transform: translateX(5px);
    }

    .contact-method i {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        margin-right: var(--space-sm);
    }

    .map-container {
        height: 100%;
    }

    .ratio-16x9 {
        position: relative;
        width: 100%;
        overflow: hidden;
        border-radius: var(--radius-md);
    }

    .ratio-16x9::before {
        display: block;
        content: "";
        padding-top: 56.25%;
    }

    .ratio-16x9 iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    .business-hours {
        background-color: rgba(255, 179, 71, 0.08);
        border-radius: var(--radius-md);
        padding: var(--space-md);
        margin-top: var(--space-md);
    }

    .business-hours ul {
        list-style: none;
    }

    .business-hours li {
        padding: var(--space-xs) 0;
        display: flex;
        align-items: center;
        color: var(--text-secondary);
    }

    .business-hours li::before {
        content: "→";
        color: var(--icon-time);
        margin-right: var(--space-xs);
        font-weight: bold;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .contact-card {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .map-container {
        animation: fadeInUp 0.6s ease-out 0.2s forwards;
        opacity: 0;
    }

    @media (max-width: 576px) {
        .contact-section {
            padding: var(--space-md) var(--space-xs);
        }

        h1::after {
            width: 60px;
            bottom: -8px;
        }

        .contact-card,
        .map-container {
            padding: var(--space-sm);
        }

        .services-list li,
        .contact-method {
            padding: var(--space-xs);
            font-size: 0.95rem;
        }

        .ratio-16x9::before {
            padding-top: 75%;
        }

        .contact-method i {
            width: 28px;
            height: 28px;
            font-size: 0.9rem;
            margin-right: var(--space-xs);
        }
    }

    .seo-content {
        /* background-color: var(--card-bg); */
        border-radius: var(--radius-lg);
        margin-top: 30px;
        /* mt-5 replace */
        padding: 30px;
        /* p-4 replace */
    }
</style>

<?php include 'includes/header.php'; ?>

<section class="contact-section">
    <div class="container">
        <h1 class="text-center mb-4">Contact <span><?php echo SITE_NAME; ?></span> - Online Cake Shop Sri Lanka</h1>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="contact-card p-4">
                    <h2><i class="fas fa-birthday-cake me-2"></i>Our Cake & Dessert Services</h2>
                    <ul class="services-list">
                        <li><i class="fas fa-birthday-cake me-2"></i>Custom Cakes & Cupcakes</li>
                        <li><i class="fas fa-ice-cream me-2"></i>Pastries & Desserts</li>
                        <li><i class="fas fa-cookie me-2"></i>Birthday & Celebration Cakes</li>
                        <li><i class="fas fa-gift me-2"></i>Special Occasion Orders</li>
                        <li><i class="fas fa-truck me-2"></i>Delivery Across Sri Lanka</li>
                    </ul>

                    <h2 class="mt-4"><i class="fas fa-paper-plane me-2"></i>Contact <?php echo SITE_NAME; ?></h2>
                    <div class="contact-methods">
                        <a href="https://wa.me/<?php echo WHATSAPP_ADMIN_NUMBER; ?>" target="_blank" class="contact-method">
                            <i class="fab fa-whatsapp"></i>WhatsApp: +<?php echo WHATSAPP_ADMIN_NUMBER; ?>
                        </a>

                        <a href="mailto:<?php echo EMAIL; ?>" class="contact-method">
                            <i class="fas fa-envelope"></i>Email: <?php echo EMAIL; ?>
                        </a>

                        <div class="contact-method">
                            <i class="fas fa-phone"></i>Phone: <?php echo WHATSAPP_ADMIN_NUMBER; ?>
                        </div>

                        <div class="contact-method">
                        <i class="fas fa-map-marker-alt"></i>  <?php echo LOCATION; ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="map-container">
                    <h2><i class="fas fa-map-marked-alt me-2"></i>Visit <?php echo SITE_NAME; ?></h2>
                    <div class="ratio ratio-16x9">
                        <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.029879046779!2d80.53664597499709!3d5.954920294055378!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae13fdf16f61b5f%3A0x72e34b587f08e451!2sMatara!5e0!3m2!1sen!2slk!4v1730999999999!5m2!1sen!2slk"
                        allowfullscreen="" loading="lazy" title="Yummy Cake Location Map">
                        </iframe>
                    </div>
                    <div class="business-hours mt-4 p-3">
                        <h3><i class="fas fa-clock me-2"></i>Business Hours</h3>
                        <ul>
                            <li>Monday - Sunday: 8:00 AM - 8:00 PM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="seo-content mt-5 p-4" style="background-color: var(--card-bg); border-radius: var(--radius-lg);">
            <h2>Why Order from <?php echo SITE_NAME; ?>?</h2>
            <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                <li>Delicious cakes and desserts made with fresh ingredients</li>
                <li>Customizable cakes for birthdays, weddings & events</li>
                <li>Fast and safe delivery across Sri Lanka</li>
                <li>Professional and friendly service</li>
            </ul>
            <br>
            <h3>How to Order</h3>
            <p>Contact us via WhatsApp, phone, or email to place your order. Choose your cake design, flavor, and delivery date. We make your celebrations sweeter and memorable!</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>