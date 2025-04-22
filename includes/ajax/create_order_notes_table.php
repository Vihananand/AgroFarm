<?php
require_once '../config.php';
require_once '../db.php';
require_once '../auth_functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Unauthorized access";
    exit;
}

try {
    global $conn;
    
    // Check if order_notes table already exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'order_notes'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the order_notes table
        $sql = "CREATE TABLE `order_notes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `admin_id` INT(11) NOT NULL,
            `note` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            KEY `admin_id` (`admin_id`),
            CONSTRAINT `order_notes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `order_notes_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "Order notes table created successfully";
    } else {
        echo "Order notes table already exists";
    }
} catch (Exception $e) {
    error_log("Create order notes table error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?> 