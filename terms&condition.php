<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Yummy Cake | Terms & Conditions & Privacy Policy";

?>

<style>
    :root {
        --primary: #FF6F61;
        --secondary: #FFB347;
        --accent: #FFD700;
        --bg-gradient: linear-gradient(135deg, #FFF0F5, #FFE5EC);
        --card-bg: #FFF8F0;
        --text-primary: #4B3832;
        --text-secondary: #7F5F3F;
        --radius-lg: 16px;
        --space-sm: 1rem;
        --space-md: 1.5rem;
        --space-lg: 2rem;
        --shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        --font-main: 'Poppins', 'Noto Sans Sinhala', sans-serif;
    }

    body {
        font-family: var(--font-main);
        background: var(--bg-gradient);
        color: var(--text-primary);
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    .container {
        /* max-width: 1100px; */
        margin: 0 auto;
    }

    h1,
    h2,
    h3 {
        margin-bottom: var(--space-sm);
    }

    h1 {
        text-align: center;
        font-size: 2.5rem;
        color: var(--primary);
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: var(--space-lg);
    }

    h2 {
        font-size: 1.6rem;
        color: var(--secondary);
        border-bottom: 2px solid var(--secondary);
        padding-bottom: 0.3rem;
        margin-top: var(--space-md);
    }

    h3 {
        font-size: 1.2rem;
        color: var(--primary);
        margin-top: var(--space-md);
    }

    .policy-section {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: var(--space-md);
        margin-bottom: var(--space-md);
        box-shadow: var(--shadow);
    }

    p,
    li {
        color: var(--text-secondary);
        /* margin-bottom: 0.5rem; */
    }

    .ulclz {
        margin-left: var(--space-md);
        margin-bottom: var(--space-sm);
    }

    a {
        color: var(--primary);
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    .last-updated {
        text-align: right;
        font-style: italic;
        color: var(--text-secondary);
        margin-bottom: var(--space-md);
    }

    @media (max-width: 768px) {
        .container {
            padding: var(--space-md);
        }

        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.4rem;
        }
    }
</style>

<?php include 'includes/header.php'; ?>

<div class="container" style="padding-top: 40px; max-width: 1100px;">
    <h1>Terms & Conditions | Privacy Policy</h1>
    <div class="last-updated">Last Updated: <?php echo date('F j, Y'); ?></div>

    <div class="policy-section">
        <h2>1. Terms of Service</h2>

        <h3>1.1 General Terms</h3>
        <p>By using Yummy Cake's website (yummycake.lk) or in-store services, you agree to follow these terms. All customers and visitors must comply with these policies.</p>

        <h3>1.2 Product Orders</h3>
        <ul class="ulclz">
            <li>All prices are in Sri Lankan Rupees (LKR)</li>
            <li>Product availability may change without notice</li>
            <li>We reserve the right to refuse service to anyone</li>
            <li>Orders are final unless otherwise stated in promotions</li>
        </ul>

        <h3>1.3 Delivery & Pickup</h3>
        <ul class="ulclz">
            <li>Delivery estimates are provided but not guaranteed</li>
            <li>Customers should verify their order details</li>
            <li>Unclaimed orders will be held for 3 days</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>2. Privacy Policy</h2>

        <h3>2.1 Information Collection</h3>
        <p>We collect personal information when you:</p>
        <ul class="ulclz">
            <li>Place an order online or in-store</li>
            <li>Request custom cake services</li>
            <li>Contact us via phone, email, or social media</li>
            <li>Subscribe to promotions or newsletters</li>
        </ul>

        <h3>2.2 Data Usage</h3>
        <p>Your information may be used to:</p>
        <ul class="ulclz">
            <li>Process orders and deliver products</li>
            <li>Improve services and customer support</li>
            <li>Send promotions or offers (you can unsubscribe anytime)</li>
            <li>Comply with legal requirements</li>
        </ul>

        <h3>2.3 Data Protection</h3>
        <p>We implement measures to protect your data:</p>
        <ul class="ulclz">
            <li>Secure servers & databases</li>
            <li>Limited employee access to sensitive information</li>
            <li>Regular security audits</li>
        </ul>
        <p>However, no method of internet transmission is 100% secure.</p>
    </div>

    <div class="policy-section">
        <h2>3. Returns & Custom Orders</h2>

        <h3>3.1 Cake Warranty</h3>
        <ul class="ulclz">
            <li>Custom cakes are made fresh and cannot be returned</li>
            <li>We ensure high-quality ingredients and baking</li>
        </ul>

        <h3>3.2 Return Policy</h3>
        <ul class="ulclz">
            <li>Defective products should be reported within 24 hours</li>
            <li>Refunds or replacements will be issued after verification</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>4. Limitation of Liability</h2>
        <p>Yummy Cake is not responsible for:</p>
        <ul class="ulclz">
            <li>Indirect or incidental damages</li>
            <li>Orders lost in transit</li>
            <li>Misuse of products by customers</li>
            <li>Events beyond our control (e.g., weather, natural disasters)</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>5. Updates</h2>
        <p>We may update these policies periodically. Updated terms will appear on this page with a new "Last Updated" date. Continued use of our services implies acceptance.</p>
    </div>

    <div class="policy-section">
        <h2>6. Contact Us</h2>
        <p>For questions about these terms or privacy practices:</p>
        <ul class="ulclz">
            <li>
                <i class="fas fa-phone"></i>
                <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank">
                    +<?php echo WHATSAPP_NUMBER; ?>
                </a>
            </li>
            <li>
                <i class="fas fa-envelope"></i>
                <a href="mailto:<?php echo EMAIL; ?>">
                    <?php echo EMAIL; ?>
                </a>
            </li>
            <li>
                <i class="fas fa-map-marker-alt"></i>
                <?php echo LOCATION; ?>
            </li>
        </ul>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>

</html>