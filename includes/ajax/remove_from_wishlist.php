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
    'message' => 'Failed to remove item from wishlist',
    'wishlist_count' => isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0
];

// Process remove from wishlist request
try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if product_id is provided
        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
            $product_id = (int)$_POST['product_id'];
            
            // Check if product exists in wishlist
            $key = array_search($product_id, $_SESSION['wishlist']);
            if ($key !== false) {
                // Remove product from wishlist
                unset($_SESSION['wishlist'][$key]);
                
                // Reindex array
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
                
                // Update response
                $response = [
                    'success' => true,
                    'message' => 'Item removed from wishlist successfully',
                    'wishlist_count' => count($_SESSION['wishlist'])
                ];
            } else {
                $response['message'] = 'Item is not in your wishlist';
            }
        } else {
            $response['message'] = 'Product ID is required';
        }
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