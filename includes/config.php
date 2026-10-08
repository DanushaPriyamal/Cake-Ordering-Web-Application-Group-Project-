<?php

define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'yummycakes'); 

// Site configuration
define('SITE_URL', 'http://localhost/cake-website/');
define('SITE_NAME', 'YUMMY CAKE');




// PayPal Configuration
define('PAYPAL_EMAIL', 'sb-nlnys43837697@personal.example.com');
define('PAYPAL_SANDBOX', true); //TRUE FOR SANDBOX
define('PAYPAL_CURRENCY', 'USD');
define('LKR_TO_USD_RATE', 0.0031); //TEMPORY

// Email configuration
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io'); //SMTP SERVER 
define('SMTP_PORT',  587);
define('SMTP_USERNAME', 'cdf275a17abbe5'); 
define('SMTP_PASSWORD', 'a80a5bb598463b'); 
define('FROM_EMAIL', 'no-reply@yummycake.lk'); 
define('FROM_NAME', 'no-reply@yummycake.lk'); 

// WhatsApp configuration
define('WHATSAPP_NUMBER', '94715710806');
define('WHATSAPP_MESSAGE', 'Hello! I have a question about your product.');
define('WHATSAPP_ADMIN_NUMBER', '94715710805');
define('EMAIL', 'methmina06@gmail.com'); 
define('LOCATION', 'Main Street, Matara, Sri Lanka'); 
// session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
 ?>