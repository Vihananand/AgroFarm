<?php
// Debug script to check order data directly
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to plain text for easier reading
header('Content-Type: text/plain');

echo "=== Database Connection Test ===\n";
if ($conn) {
    echo "Database connection successful\n";
} else {
    echo "Database connection failed\n";
    exit;
}

echo "\n=== Tables Check ===\n";
try {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . implode(", ", $tables) . "\n";
} catch (PDOException $e) {
    echo "Error listing tables: " . $e->getMessage() . "\n";
}

echo "\n=== Orders Table Structure ===\n";
try {
    $structure = $conn->query("DESCRIBE orders");
    $columns = $structure->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error describing orders table: " . $e->getMessage() . "\n";
}

echo "\n=== Orders Data ===\n";
try {
    $stmt = $conn->query("SELECT * FROM orders LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders)) {
        echo "No orders found in database\n";
    } else {
        echo "Found " . count($orders) . " orders\n";
        foreach ($orders as $order) {
            echo "\nOrder #" . $order['id'] . "\n";
            foreach ($order as $key => $value) {
                echo "  - $key: $value\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "Error fetching orders: " . $e->getMessage() . "\n";
}

// Check specific order ID if provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    echo "\n=== Order #$orderId Details ===\n";
    
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo "Order #$orderId not found\n";
        } else {
            foreach ($order as $key => $value) {
                echo "  - $key: $value\n";
            }
            
            // Get order items
            $stmt = $conn->prepare("
                SELECT oi.*, p.name as product_name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\n  Items in order:\n";
            if (empty($items)) {
                echo "  - No items found\n";
            } else {
                foreach ($items as $index => $item) {
                    echo "  - Item " . ($index + 1) . ": " . $item['product_name'] . 
                         " (Qty: " . $item['quantity'] . ", Price: $" . $item['price'] . ")\n";
                }
            }
        }
    } catch (PDOException $e) {
        echo "Error fetching order: " . $e->getMessage() . "\n";
    }
}
?> 