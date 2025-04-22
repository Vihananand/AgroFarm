<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Check if there are already records
    $check = $conn->query("SELECT COUNT(*) FROM custom_requests")->fetchColumn();
    
    if ($check == 0) {
        // Insert a test record
        $stmt = $conn->prepare("INSERT INTO custom_requests (user_id, name, email, phone, request_details, status, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        // Get the first user id from the users table or use NULL
        $user_id = null;
        $user_query = $conn->query("SELECT id FROM users LIMIT 1");
        if ($user_query->rowCount() > 0) {
            $user_id = $user_query->fetchColumn();
        }
        
        $stmt->execute([
            $user_id,
            'Test User',
            'test@example.com',
            '555-123-4567',
            'This is a test request for organic fertilizer. Please help me find a supplier for my farm.',
            'pending'
        ]);
        
        echo "Test custom request created successfully";
    } else {
        echo "Custom requests table already has data";
    }
} catch (PDOException $e) {
    echo "Error creating test custom request: " . $e->getMessage();
}
?> 