<?php
// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_id(uniqid());
    session_start();
}

// Include database connection file
require_once '../config.php';

// Default response
$response = [
    'success' => false,
    'message' => 'Failed to clear wishlist',
    'wishlist_count' => isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0
];

// Process clear wishlist request
try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Clear wishlist
        $_SESSION['wishlist'] = [];
        
        // Update response
        $response = [
            'success' => true,
            'message' => 'Wishlist cleared successfully',
            'wishlist_count' => 0
        ];
    } else {
        $response['message'] = 'Invalid request method';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 