<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'discord_webhook_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Discord Webhook Portal');
define('SITE_URL', 'http://localhost/discord-webhook-portal');

// Credit system
define('CREDITS_PER_MINUTE', 3);
define('WEBHOOK_CREDIT_COST', 90);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
