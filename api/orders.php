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
        'message' => 'Please login to access orders'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle GET request for order details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order ID is required'
        ]);
        exit;
    }

    $order_id = (int)$_GET['id'];

    try {
        // Fetch order details
        $stmt = $conn->prepare("
            SELECT o.*, 
                   COUNT(oi.id) as total_items,
                   SUM(oi.quantity * oi.price) as total_amount
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.id = ? AND o.user_id = ?
            GROUP BY o.id
        ");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found'
            ]);
            exit;
        }

        // Fetch order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();

        // Calculate totals
        $subtotal = array_sum(array_map(function($item) {
            return $item['quantity'] * $item['price'];
        }, $items));
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $tax;

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $order['id'],
                'status' => $order['status'],
                'payment_method' => $order['payment_method'],
                'created_at' => $order['created_at'],
                'items' => $items,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]
        ]);

    } catch (PDOException $e) {
        error_log("Order details error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while fetching order details'
        ]);
    }
    exit;
}

// Handle POST request for creating new order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['items']) || empty($data['items'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No items in order'
        ]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, status, payment_method)
            VALUES (?, 'pending', ?)
        ");
        $stmt->execute([$user_id, $data['payment_method'] ?? 'credit_card']);
        $order_id = $conn->lastInsertId();

        // Add order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($data['items'] as $item) {
            // Verify product exists and has enough stock
            $check_stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
            $check_stmt->execute([$item['product_id']]);
            $product = $check_stmt->fetch();

            if (!$product) {
                throw new Exception("Product not found");
            }

            if ($product['stock'] < $item['quantity']) {
                throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
            }

            // Add order item
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $product['price']
            ]);

            // Update product stock
            $update_stmt = $conn->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ?
            ");
            $update_stmt->execute([$item['quantity'], $item['product_id']]);
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order_id' => $order_id
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Order creation error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Handle PUT request for updating order status
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id']) || !isset($data['status'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order ID and status are required'
        ]);
        exit;
    }

    $order_id = (int)$data['order_id'];
    $status = $data['status'];

    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status'
        ]);
        exit;
    }

    try {
        // Verify order belongs to user
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found'
            ]);
            exit;
        }

        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);

    } catch (PDOException $e) {
        error_log("Order status update error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while updating order status'
        ]);
    }
    exit;
} 