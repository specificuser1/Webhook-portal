<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit();
} else {
    header('Location: pages/login.php');
    exit();
}
?>
