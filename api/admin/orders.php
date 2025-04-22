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
    // First check if the orders table exists
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('orders', $tables)) {
        echo json_encode([
            'success' => false,
            'message' => 'Orders table does not exist in the database. Please make sure your database is properly set up.',
            'debug' => [
                'tables' => $tables
            ]
        ]);
        exit;
    }
    
    // Get query parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    
    // Debug info
    $debug = [];
    $debug['session'] = [
        'user_id' => $_SESSION['user_id'] ?? 'not set',
        'user_role' => $_SESSION['user_role'] ?? 'not set'
    ];
    
    // Basic query
    $query = "SELECT o.*, u.email, u.first_name, u.last_name 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id
              WHERE 1=1";
    
    $params = [];
    
    // Add filters
    if ($status) {
        // First check if the status column exists in the orders table
        try {
            $columns = $conn->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
            $debug['order_columns'] = $columns;
            
            if (in_array('status', $columns)) {
                $query .= " AND o.status = ?";
                $params[] = $status;
            } else if (in_array('payment_status', $columns)) {
                $query .= " AND o.payment_status = ?";
                $params[] = $status;
            }
        } catch (PDOException $e) {
            // If we can't determine columns, just try with status
            $query .= " AND (o.status = ? OR o.payment_status = ?)";
            $params[] = $status;
            $params[] = $status;
            $debug['columns_error'] = $e->getMessage();
        }
    }
    
    if ($search) {
        $query .= " AND (o.id LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Count total orders that match the criteria
    $countQuery = str_replace(
        "SELECT o.*, u.email, u.first_name, u.last_name", 
        "SELECT COUNT(*)", 
        $query
    );
    
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    $debug['total_count'] = $total;
    
    // Add ordering and pagination
    $query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $debug['query'] = $query;
    $debug['params'] = $params;
    
    // Get orders
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['orders_found'] = count($orders);
    $debug['raw_orders'] = array_slice($orders, 0, 2); // Include a sample of raw orders for debugging
    
    // Format orders for response
    $formattedOrders = [];
    foreach ($orders as $order) {
        $formattedOrder = [
            'id' => $order['id'],
            'customer_name' => ($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''),
            'email' => $order['email'] ?? 'Unknown',
            'total_amount' => $order['total_amount'] ?? 0,
            'created_at' => $order['created_at'] ?? '',
            'updated_at' => $order['updated_at'] ?? ''
        ];
        
        // Add status field based on available columns
        if (isset($order['status'])) {
            $formattedOrder['status'] = $order['status'];
        } else if (isset($order['payment_status'])) {
            $formattedOrder['status'] = $order['payment_status'];
        } else {
            $formattedOrder['status'] = 'unknown';
        }
        
        $formattedOrders[] = $formattedOrder;
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'total' => $total,
        'page' => floor($offset / $limit) + 1,
        'total_pages' => ceil($total / $limit),
        'orders' => $formattedOrders,
        'debug' => $debug
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Orders API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Orders API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 