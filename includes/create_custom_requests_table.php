<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Drop existing table if user confirms
    if (isset($_GET['drop']) && $_GET['drop'] === 'yes') {
        $conn->exec("DROP TABLE IF EXISTS custom_requests");
        echo "Existing table dropped.<br>";
    }
    
    // Check if the table already exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'custom_requests'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the custom_requests table
        $sql = "CREATE TABLE `custom_requests` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NULL DEFAULT NULL,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NULL DEFAULT NULL,
            `request_details` TEXT NOT NULL,
            `status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `custom_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "Custom requests table created successfully";
        
        // Insert a test record
        $stmt = $conn->prepare("INSERT INTO custom_requests (name, email, phone, request_details, status) 
                              VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Test User',
            'test@example.com',
            '555-123-4567',
            'This is a test request for organic fertilizer. Please help me find a supplier for my farm.',
            'pending'
        ]);
        
        echo "<br>Test record inserted.";
    } else {
        echo "Custom requests table already exists.<br>";
        echo "To drop and recreate the table, add ?drop=yes to the URL.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 