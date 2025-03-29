<?php
/**
 * Ajax handler for adding items to wishlist
 */

// Include configuration file
require_once '../config.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Initialize the response array
$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'wishlist_count' => 0
];

// Check if the user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to add items to wishlist.';
    echo json_encode($response);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id'])) {
    $response['message'] = 'Missing required parameter: product_id.';
    echo json_encode($response);
    exit;
}

// Get and validate product ID
$product_id = (int)$_POST['product_id'];

// Validate product ID
if ($product_id <= 0) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit;
}

// Get user ID
$user_id = getUserId();

try {
    // Check if the database is connected
    if (!$db_connected) {
        throw new Exception('Database connection failed.');
    }
    
    // Check if the product exists
    $stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $response['message'] = 'Product not found or unavailable.';
        echo json_encode($response);
        exit;
    }
    
    // Check if product is already in wishlist
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Product already in wishlist
        $response['success'] = true;
        $response['message'] = 'Product is already in your wishlist.';
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $product_id]);
        
        $response['success'] = true;
        $response['message'] = 'Product added to wishlist successfully.';
    }
    
    // Get updated wishlist count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlist_count = $stmt->fetch();
    
    $response['wishlist_count'] = $wishlist_count['count'] ?? 0;
    
} catch (Exception $e) {
    // Log the error but don't expose details to the client
    error_log('Wishlist error: ' . $e->getMessage());
    $response['message'] = 'A system error occurred. Please try again later.';
}

// Return the JSON response
echo json_encode($response);
exit; 