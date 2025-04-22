<?php
// Simple order details view - for debugging only
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_functions.php';

// Start session if not already started
if (!isset($_SESSION)) {
    session_start();
}

// Header that will make output easier to read in browser
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Simple Order View</h1>";

// Check if user is logged in
if (!isLoggedIn()) {
    die("<p style='color:red'>Not logged in. Please <a href='" . SITE_URL . "/pages/login.php'>login</a> first.</p>");
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p style='color:red'>No order ID provided or invalid ID format.</p>");
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

echo "<p>Looking up order ID: $order_id for user ID: $user_id</p>";
echo "<p>Session details: " . htmlspecialchars(print_r($_SESSION, true)) . "</p>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn) {
    echo "<p style='color:green'>Database connection is working</p>";
} else {
    die("<p style='color:red'>Database connection failed</p>");
}

// Check if orders table exists
echo "<h2>Database Tables</h2>";
try {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables in database: " . implode(", ", $tables) . "</p>";
    
    if (!in_array('orders', $tables)) {
        die("<p style='color:red'>Orders table does not exist!</p>");
    }
} catch (PDOException $e) {
    die("<p style='color:red'>Error checking tables: " . $e->getMessage() . "</p>");
}

// Try to fetch the order
echo "<h2>Order Query</h2>";
try {
    echo "<p>Query: SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id</p>";
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo "<p style='color:red'>No order found with ID $order_id for user $user_id</p>";
        
        // Check if the order exists but belongs to a different user
        $check_stmt = $conn->prepare("SELECT id, user_id FROM orders WHERE id = ?");
        $check_stmt->execute([$order_id]);
        $check_order = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($check_order) {
            echo "<p style='color:orange'>Order #$order_id exists but belongs to user #" . $check_order['user_id'] . "</p>";
        } else {
            echo "<p style='color:red'>Order #$order_id does not exist in the database at all</p>";
        }
    } else {
        echo "<h2>Order Found</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        
        foreach ($order as $key => $value) {
            echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        
        echo "</table>";
        
        // Get order items
        echo "<h2>Order Items</h2>";
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name, p.image as product_image
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($items)) {
            echo "<p>No items found for this order</p>";
        } else {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
            
            $total = 0;
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                echo "<td>" . $item['quantity'] . "</td>";
                echo "<td>$" . number_format($item['price'], 2) . "</td>";
                echo "<td>$" . number_format($subtotal, 2) . "</td>";
                echo "</tr>";
            }
            
            echo "<tr><td colspan='3' align='right'><strong>Total:</strong></td>";
            echo "<td><strong>$" . number_format($total, 2) . "</strong></td></tr>";
            echo "</table>";
        }
    }
} catch (PDOException $e) {
    die("<p style='color:red'>Database error: " . $e->getMessage() . "</p>");
}
?> 