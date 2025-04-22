<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    $response = [
        'success' => true,
        'message' => 'Dashboard statistics retrieved successfully'
    ];
    
    // Debug info - this will help us diagnose issues
    $response['debug'] = [
        'session' => [
            'user_id' => $_SESSION['user_id'] ?? 'not set',
            'user_role' => $_SESSION['user_role'] ?? 'not set'
        ],
        'queries_run' => []
    ];
    
    // Total products
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM products");
        $response['total_products'] = (int)$stmt->fetchColumn();
        $response['debug']['queries_run'][] = "SELECT COUNT(*) FROM products";
    } catch (PDOException $e) {
        $response['debug']['product_count_error'] = $e->getMessage();
        $response['total_products'] = 0;
    }

    // Total orders
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        $response['total_orders'] = (int)$stmt->fetchColumn();
        $response['debug']['queries_run'][] = "SELECT COUNT(*) FROM orders";
    } catch (PDOException $e) {
        $response['debug']['orders_count_error'] = $e->getMessage();
        $response['total_orders'] = 0;
    }

    // Total customers
    try {
        // First try with user_role column
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE user_role = 'customer'");
            $response['total_customers'] = (int)$stmt->fetchColumn();
            $response['debug']['queries_run'][] = "SELECT COUNT(*) FROM users WHERE user_role = 'customer'";
        } catch (PDOException $e) {
            // If that fails, try with role column
            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
            $response['total_customers'] = (int)$stmt->fetchColumn();
            $response['debug']['queries_run'][] = "SELECT COUNT(*) FROM users WHERE role = 'customer'";
        }
    } catch (PDOException $e) {
        $response['debug']['customers_count_error'] = $e->getMessage();
        $response['total_customers'] = 0;
    }

    // Total revenue
    try {
        // Sum total from all orders regardless of status
        $stmt = $conn->query("SELECT SUM(total_amount) FROM orders");
        $total_revenue = $stmt->fetchColumn();
        $response['debug']['queries_run'][] = "SELECT SUM(total_amount) FROM orders";
        
        // Convert to float and handle null values
        if ($total_revenue === null || $total_revenue === false) {
            $response['total_revenue'] = 0;
            $response['debug']['revenue_note'] = "No revenue found or sum returned null";
        } else {
            $response['total_revenue'] = (float)$total_revenue;
            $response['debug']['raw_revenue'] = $total_revenue;
        }
    } catch (PDOException $e) {
        $response['debug']['revenue_count_error'] = $e->getMessage();
        $response['total_revenue'] = 0;
    }
    
    // Get table structures for debugging
    try {
        // Check what tables exist
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $response['debug']['tables'] = $tables;
        
        // Check orders table structure
        if (in_array('orders', $tables)) {
            $order_columns = $conn->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
            $response['debug']['orders_columns'] = $order_columns;
        }
        
        // Check users table structure
        if (in_array('users', $tables)) {
            $user_columns = $conn->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
            $response['debug']['users_columns'] = $user_columns;
        }
    } catch (Exception $e) {
        $response['debug']['schema_error'] = $e->getMessage();
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Dashboard stats API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Dashboard stats API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 