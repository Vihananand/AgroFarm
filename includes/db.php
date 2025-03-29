<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'agrofarm');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection
$conn = null;

// Try connecting to the database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Set character set
    $conn->exec("set names utf8mb4");
    
    // Mark database as connected
    $db_connected = true;
} catch(PDOException $e) {
    // Log the error but don't expose details
    error_log("Database Connection Error: " . $e->getMessage());
    $db_connected = false;
    
    // Don't throw the exception here, we'll handle it gracefully
    // For development purposes, you might want to uncomment the following line:
    // echo "Connection failed: " . $e->getMessage();
}
?> 