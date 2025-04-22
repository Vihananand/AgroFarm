<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your wishlist'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request for adding to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing product ID'
        ]);
        exit;
    }

    $product_id = (int)$data['product_id'];

    try {
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }

        // Check if already in wishlist
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Product already in wishlist'
            ]);
            exit;
        }

        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Product added to wishlist'
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

// Handle DELETE request for removing from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing product ID'
        ]);
        exit;
    }

    $product_id = (int)$data['product_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Product removed from wishlist'
        ]);

    } catch (PDOException $e) {
        error_log("Wishlist error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while removing from wishlist'
        ]);
    }
    exit;
}

// Handle GET request for wishlist items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT w.*, p.name, p.price, p.image, p.stock, p.sale_price 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $wishlist_items = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $wishlist_items
        ]);

    } catch (PDOException $e) {
        error_log("Wishlist error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while fetching wishlist items'
        ]);
    }
    exit;
} 