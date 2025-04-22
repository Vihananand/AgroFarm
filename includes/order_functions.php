<?php
require_once 'db_connect.php';
require_once 'product_functions.php';
require_once 'cart_functions.php';

/**
 * Order Functions
 * 
 * Functions for managing orders in the AgroFarm application
 */

/**
 * Create a new order
 * @param int $user_id User ID
 * @param array $order_data Order data including shipping and payment info
 * @return array Response with status and message
 */
function createOrder($user_id, $order_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to create order',
        'order_id' => null
    ];
    
    // Get cart items
    $cart = getCartItems($user_id);
    
    if (empty($cart['items'])) {
        $response['message'] = 'Your cart is empty';
        return $response;
    }
    
    // Check stock availability for all items
    foreach ($cart['items'] as $item) {
        if ($item['quantity'] > $item['stock']) {
            $response['message'] = 'Not enough stock for ' . $item['name'] . '. Only ' . $item['stock'] . ' available.';
            return $response;
        }
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, total_amount, shipping_address, shipping_phone, 
                shipping_email, status, payment_method, payment_status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");
        
        $stmt->execute([
            $user_id,
            $cart['total'],
            $order_data['shipping_address'],
            $order_data['shipping_phone'],
            $order_data['shipping_email'],
            'pending',
            $order_data['payment_method'],
            'pending'
        ]);
        
        $order_id = $conn->lastInsertId();
        
        // Add order items
        foreach ($cart['items'] as $item) {
            $price = isset($item['sale_price']) && $item['sale_price'] > 0 ? $item['sale_price'] : $item['price'];
            
            $insert_item = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            $insert_item->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $price
            ]);
            
            // Update product stock
            $update_stock = $conn->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ?
            ");
            
            $update_stock->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }
        
        // Clear the user's cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->execute([$user_id]);
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Order created successfully';
        $response['order_id'] = $order_id;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get orders for a specific user with pagination
 * 
 * @param int $user_id User ID
 * @param int $page Current page number
 * @param int $per_page Items per page
 * @return array Array containing orders and pagination data
 */
function getUserOrders($user_id, $page = 1, $per_page = 10) {
    global $conn;
    
    try {
        // Get total number of orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_orders = $stmt->fetchColumn();
        
        // Calculate total pages
        $total_pages = ceil($total_orders / $per_page);
        
        // Ensure page is within valid range
        $page = max(1, min($page, $total_pages));
        
        // Calculate offset
        $offset = ($page - 1) * $per_page;
        
        // Get orders for current page
        $stmt = $conn->prepare("
            SELECT o.*, 
                   COUNT(oi.id) as total_items,
                   GROUP_CONCAT(oi.product_id) as product_ids
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $per_page, $offset]);
        $orders = $stmt->fetchAll();
        
        return [
            'orders' => $orders,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    } catch (PDOException $e) {
        error_log("Error in getUserOrders: " . $e->getMessage());
        return [
            'orders' => [],
            'total_pages' => 0,
            'current_page' => 1
        ];
    }
}

/**
 * Get detailed information about a specific order
 * 
 * @param int $order_id Order ID
 * @return array|false Order details or false if not found
 */
function getOrderDetails($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}

/**
 * Get items in a specific order
 * 
 * @param int $order_id Order ID
 * @return array Array of order items
 */
function getOrderItems($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                           LEFT JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Update order status
 * 
 * @param int $order_id Order ID
 * @param string $status New status
 * @return bool Success or failure
 */
function updateOrderStatus($order_id, $status) {
    global $conn;
    
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (!in_array($status, $valid_statuses)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

/**
 * Update payment status for an order
 * 
 * @param int $order_id Order ID
 * @param string $payment_status New payment status
 * @return bool Success or failure
 */
function updatePaymentStatus($order_id, $payment_status) {
    global $conn;
    
    $valid_statuses = ['pending', 'completed', 'failed', 'refunded'];
    
    if (!in_array($payment_status, $valid_statuses)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_status, $order_id);
    return $stmt->execute();
}

/**
 * Cancel an order if it's in a cancellable state
 * 
 * @param int $order_id Order ID
 * @param int $user_id User ID (for verification)
 * @return bool Success or failure
 */
function cancelOrder($order_id, $user_id) {
    global $conn;
    
    // Get order details
    $order = getOrderDetails($order_id);
    
    // Check if order exists and belongs to the user
    if (!$order || $order['user_id'] != $user_id) {
        return false;
    }
    
    // Check if order can be cancelled (only pending or processing orders)
    if (!in_array($order['status'], ['pending', 'processing'])) {
        return false;
    }
    
    // Update order status to cancelled
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    return $stmt->execute();
}

/**
 * Get all orders (admin function)
 * @param array $options Options for filtering and pagination
 * @return array Orders with pagination info
 */
function getAllOrders($options = []) {
    global $conn;
    
    // Set default options
    $default_options = [
        'limit' => null,
        'offset' => 0,
        'status' => null,
        'search' => null,
        'sort_by' => 'created_at',
        'sort_order' => 'DESC'
    ];
    
    $options = array_merge($default_options, $options);
    
    $orders = [];
    $total = 0;
    
    try {
        // Build query
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Add filters
        if ($options['status']) {
            $sql .= " AND o.status = ?";
            $params[] = $options['status'];
        }
        
        if ($options['search']) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
            $search_term = '%' . $options['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Count total
        $count_sql = str_replace("SELECT o.*, u.first_name, u.last_name, u.email", "SELECT COUNT(*) as total", $sql);
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch()['total'];
        
        // Add sorting
        $valid_sort_fields = ['id', 'created_at', 'total_amount', 'status', 'payment_status'];
        $valid_sort_orders = ['ASC', 'DESC'];
        
        $sort_field = in_array($options['sort_by'], $valid_sort_fields) ? $options['sort_by'] : 'created_at';
        $sort_order = in_array(strtoupper($options['sort_order']), $valid_sort_orders) ? strtoupper($options['sort_order']) : 'DESC';
        
        $sql .= " ORDER BY o.$sort_field $sort_order";
        
        // Add pagination
        if ($options['limit']) {
            $sql .= " LIMIT ?, ?";
            $params[] = (int)$options['offset'];
            $params[] = (int)$options['limit'];
        }
        
        // Execute query
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            $orders = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return [
        'orders' => $orders,
        'total' => $total
    ];
}

/**
 * Get order statistics (admin function)
 * @return array Statistics
 */
function getOrderStatistics() {
    global $conn;
    
    $stats = [
        'total_orders' => 0,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,
        'delivered_orders' => 0,
        'cancelled_orders' => 0,
        'total_revenue' => 0,
        'paid_revenue' => 0,
        'pending_revenue' => 0
    ];
    
    try {
        // Total orders
        $stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $stmt->fetch()['count'];
        
        // Orders by status
        $status_stmt = $conn->query("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        
        while ($row = $status_stmt->fetch()) {
            $status_key = $row['status'] . '_orders';
            $stats[$status_key] = $row['count'];
        }
        
        // Revenue
        $revenue_stmt = $conn->query("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $revenue_stmt->fetch()['total'] ?: 0;
        
        // Paid revenue
        $paid_stmt = $conn->query("
            SELECT SUM(total_amount) as total 
            FROM orders 
            WHERE payment_status = 'paid'
        ");
        $stats['paid_revenue'] = $paid_stmt->fetch()['total'] ?: 0;
        
        // Pending revenue
        $pending_stmt = $conn->query("
            SELECT SUM(total_amount) as total 
            FROM orders 
            WHERE payment_status = 'pending'
        ");
        $stats['pending_revenue'] = $pending_stmt->fetch()['total'] ?: 0;
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return $stats;
}
?> 