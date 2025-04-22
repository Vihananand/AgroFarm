<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters for better security
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Define constants
define('SITE_NAME', 'AgroFarm');
define('SITE_URL', 'http://localhost/AgroFarm');
define('ADMIN_EMAIL', 'admin@agrofarm.com');

// Include database connection
require_once __DIR__ . '/db_connect.php';

// Include auth functions
require_once __DIR__ . '/auth_functions.php';

$db_connected = false;

date_default_timezone_set('UTC');

// Helper functions
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