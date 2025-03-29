<?php
session_start();

// Site configuration
define('SITE_NAME', 'AgroFarm');
define('SITE_URL', 'http://localhost/AgroFarm');
define('ADMIN_EMAIL', 'admin@agrofarm.com');

// Include database connection
require_once __DIR__ . '/db.php';

// Database connection variable
$db_connected = false;

// Set timezone
date_default_timezone_set('UTC');

// Helper functions
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCartItemCount() {
    global $conn;
    
    // If not logged in, return 0
    if (!isLoggedIn()) {
        return 0;
    }
    
    try {
        $user_id = getUserId();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        // Log error but don't crash
        error_log('Error getting cart count: ' . $e->getMessage());
        return 0;
    }
}

function getWishlistItemCount() {
    global $conn;
    
    // If not logged in, return 0
    if (!isLoggedIn()) {
        return 0;
    }
    
    try {
        $user_id = getUserId();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        // Log error but don't crash
        error_log('Error getting wishlist count: ' . $e->getMessage());
        return 0;
    }
}

// Flash message helper
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