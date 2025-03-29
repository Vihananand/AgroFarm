<?php
/**
 * Ajax handler for adding items to cart
 */

// Include configuration file
require_once '../config.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Initialize the response array
$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'cart_count' => 0
];

// Check if the user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to add items to cart.';
    echo json_encode($response);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Check if product_id and quantity are provided
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

// Get and validate product ID and quantity
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// Validate product ID
if ($product_id <= 0) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit;
}

// Validate quantity
if ($quantity <= 0) {
    $response['message'] = 'Quantity must be at least 1.';
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
    
    // Check if the product exists and is in stock
    $stmt = $conn->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $response['message'] = 'Product not found or unavailable.';
        echo json_encode($response);
        exit;
    }
    
    // Check if product is in stock
    if ($product['stock'] < $quantity) {
        $response['message'] = 'Not enough stock available. Only ' . $product['stock'] . ' items left.';
        echo json_encode($response);
        exit;
    }
    
    // Check if the product is already in the cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Update quantity if already in cart
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if the new quantity exceeds the stock
        if ($new_quantity > $product['stock']) {
            $response['message'] = 'Cannot add more of this item. Stock limit reached.';
            echo json_encode($response);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
        
        $response['success'] = true;
        $response['message'] = 'Cart updated successfully.';
    } else {
        // Add new item to cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$user_id, $product_id, $quantity]);
        
        $response['success'] = true;
        $response['message'] = 'Item added to cart successfully.';
    }
    
    // Get updated cart count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetch();
    
    $response['cart_count'] = $cart_count['count'] ?? 0;
    
} catch (PDOException $e) {
    // Log the error but don't expose details to the client
    error_log('Database error: ' . $e->getMessage());
    $response['message'] = 'A database error occurred. Please try again later.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return the JSON response
echo json_encode($response);
exit; 