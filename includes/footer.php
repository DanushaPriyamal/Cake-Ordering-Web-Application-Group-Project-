<?php if (!isset($font_awesome_loaded)): ?>
    <?php $font_awesome_loaded = true; ?>
<?php endif; ?>

<style>
    /*  FOOTER BASE */
    .site-footer {
        background: linear-gradient(180deg, #fff0f5, #ffe4ec);
        color: #4b5563;
        padding: 3rem 1.5rem 1rem;
        margin-top: 0rem;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
    }

    /*GRID LAYOUT */
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* FOOTER COLUMN  */
    .footer-column h3 {
        color: #ec4899;
        margin-bottom: 1rem;
        font-weight: 700;
        font-size: 1.4rem;
        position: relative;
    }

    .footer-column h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -6px;
        height: 3px;
        width: 40px;
        background: linear-gradient(90deg, #ec4899, #fbbf24);
        border-radius: 3px;
    }

    .footer-column p {
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        color: #555;
    }

    /*  LINKS  */
    .footer-column ul {
        list-style: none;
        padding: 0;
    }

    .footer-column ul li {
        margin-bottom: 0.6rem;
    }

    .footer-column ul li a {
        color: #4b5563;
        text-decoration: none;
        transition: color 0.3s ease, transform 0.3s ease;
        display: inline-block;
    }

    .footer-column ul li a:hover {
        color: #ec4899;
        transform: translateX(5px);
    }

    /* SOCIAL ICONS */
    .social-links a {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin-right: 0.6rem;
        width: 38px;
        height: 38px;
        background: #ec4899;
        color: #fff;
        border-radius: 50%;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        background: #fbbf24;
        transform: translateY(-3px);
        color: #fff;
    }

    /* CONTACT INFO*/
    .contact-info li {
        display: flex;
        align-items: center;
        margin-bottom: 0.8rem;
        font-size: 0.95rem;
    }

    .contact-info i {
        color: #ec4899;
        margin-right: 0.6rem;
        font-size: 1.1rem;
    }

    /*COPYRIGHT */
    .copyright {
        text-align: center;
        margin-top: 2.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        padding-top: 1rem;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .copyright a {
        color: #ec4899;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .copyright a:hover {
        color: #fbbf24;
    }

    /*RESPONSIVE  */
    @media (max-width: 768px) {
        .footer-column h3 {
            font-size: 1.3rem;
        }

        .footer-column p,
        .contact-info li,
        .footer-column ul li a {
            font-size: 0.9rem;
        }

        .social-links a {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }
</style>


<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <!-- About -->
            <div class="footer-column">
                <h3>Yummy Cake</h3>
                <p>We bake happiness! Explore delicious, handcrafted cakes made with love and sweetness in every bite.</p>
                <div class="social-links">
                    <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/cakes">Our Cakes</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact">Contact</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/order">Order Now</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> 45 Main Street, Matara, Sri Lanka</li>
                    <li><i class="fas fa-phone"></i>+<?php echo WHATSAPP_NUMBER; ?></li>
                    <li><i class="fas fa-envelope"></i> <?php echo EMAIL; ?></li>
                </ul>
            </div>

        </div>

        <!-- Copyright -->
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Yummy Cake (Pvt) Ltd. All Rights Reserved              
                <br>
                <a href="<?php echo SITE_URL; ?>/terms&condition">Terms & Conditions</a>
            </p>
        </div>

    </div>
</footer>


<script>
//  Keyboard Shortcut: Ctrl + Shift + L
document.addEventListener('keydown', function(e) {
  if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'l') {
    window.location.href = '<?php echo SITE_URL; ?>admin/spk-st-wl';
  }
});



</script>


</body>
</html>