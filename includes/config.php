<?php
session_start();

define('SITE_NAME', 'AgroFarm');
define('SITE_URL', 'http://localhost/AgroFarm');
define('ADMIN_EMAIL', 'admin@agrofarm.com');

require_once __DIR__ . '/db.php';

$db_connected = false;

date_default_timezone_set('UTC');

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
    }
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCartItemCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

function getWishlistItemCount() {
    if (!isset($_SESSION['wishlist'])) {
        return 0;
    }
    
    return count($_SESSION['wishlist']);
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?> 