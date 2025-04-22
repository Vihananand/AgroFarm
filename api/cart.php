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
        'message' => 'Please login to add items to cart'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request for adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        exit;
    }

    $product_id = (int)$data['product_id'];
    $quantity = (int)$data['quantity'];

    try {
        // Check if product exists and has enough stock
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }

        if ($product['stock'] < $quantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock available'
            ]);
            exit;
        }

        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            // Update existing cart item
            $new_quantity = $cart_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ]);
                exit;
            }

            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $cart_item['id']]);
        } else {
            // Add new cart item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);

    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while adding to cart'
        ]);
    }
    exit;
}

// Handle GET request for cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.image, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $cart_items
        ]);

    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while fetching cart items'
        ]);
    }
    exit;
}

// Handle PUT request for updating cart quantity
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['cart_id']) || !isset($data['action'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters'
        ]);
        exit;
    }

    $cart_id = (int)$data['cart_id'];
    $action = $data['action'];

    try {
        // Get current cart item
        $stmt = $conn->prepare("
            SELECT c.*, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$cart_id, $user_id]);
        $cart_item = $stmt->fetch();

        if (!$cart_item) {
            echo json_encode([
                'success' => false,
                'message' => 'Cart item not found'
            ]);
            exit;
        }

        // Calculate new quantity
        $new_quantity = $action === 'increase' 
            ? $cart_item['quantity'] + 1 
            : $cart_item['quantity'] - 1;

        // Validate new quantity
        if ($new_quantity < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Quantity cannot be less than 1'
            ]);
            exit;
        }

        if ($new_quantity > $cart_item['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock available'
            ]);
            exit;
        }

        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);

    } catch (PDOException $e) {
        error_log("Cart update error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while updating the cart'
        ]);
    }
    exit;
}

// Handle DELETE request for removing cart item
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['cart_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing cart ID'
        ]);
        exit;
    }

    $cart_id = (int)$data['cart_id'];

    try {
        // Verify cart item belongs to user
        $stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Cart item not found'
            ]);
            exit;
        }

        // Delete cart item
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cart_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);

    } catch (PDOException $e) {
        error_log("Cart delete error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while removing the item'
        ]);
    }
    exit;
} 