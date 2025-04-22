<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to wishlist'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if product_id is provided
    if (!isset($_POST['product_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    $product_id = (int)$_POST['product_id'];

    try {
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }
        
        // Check if product already in wishlist
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Product already in wishlist'
            ]);
            exit;
        }
        
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        
        // Also add to wishlist_items table for consistency
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Product added to wishlist successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Wishlist error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while adding to wishlist'
        ]);
    }
    exit;
} 