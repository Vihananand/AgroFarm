<?php
require_once 'db_connect.php';
require_once 'auth_functions.php';

/**
 * Get all users (admin function)
 * @param array $options Options for filtering and pagination
 * @return array Users with pagination info
 */
function getAllUsers($options = []) {
    global $conn;
    
    // Check if user is admin
    if (!isAdmin()) {
        return [
            'users' => [],
            'total' => 0,
            'error' => 'Access denied'
        ];
    }
    
    // Set default options
    $default_options = [
        'limit' => null,
        'offset' => 0,
        'role' => null,
        'search' => null,
        'sort_by' => 'id',
        'sort_order' => 'ASC'
    ];
    
    $options = array_merge($default_options, $options);
    
    $users = [];
    $total = 0;
    
    try {
        // Build query
        $sql = "
            SELECT id, first_name, last_name, email, phone, city, role, created_at
            FROM users
            WHERE 1=1
        ";
        
        $params = [];
        
        // Add filters
        if ($options['role']) {
            $sql .= " AND role = ?";
            $params[] = $options['role'];
        }
        
        if ($options['search']) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $search_term = '%' . $options['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Count total
        $count_sql = str_replace("SELECT id, first_name, last_name, email, phone, city, role, created_at", "SELECT COUNT(*) as total", $sql);
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch()['total'];
        
        // Add sorting
        $valid_sort_fields = ['id', 'first_name', 'last_name', 'email', 'role', 'created_at'];
        $valid_sort_orders = ['ASC', 'DESC'];
        
        $sort_field = in_array($options['sort_by'], $valid_sort_fields) ? $options['sort_by'] : 'id';
        $sort_order = in_array(strtoupper($options['sort_order']), $valid_sort_orders) ? strtoupper($options['sort_order']) : 'ASC';
        
        $sql .= " ORDER BY $sort_field $sort_order";
        
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
            $users = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return [
        'users' => $users,
        'total' => $total
    ];
}

/**
 * Get user details (admin function)
 * @param int $user_id User ID
 * @return array|null User details or null if not found
 */
function getUserDetails($user_id) {
    global $conn;
    
    // Check if user is admin
    if (!isAdmin()) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, email, phone, address, city, state, zip_code, country, role, created_at
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return null;
}

/**
 * Update user role (admin function)
 * @param int $user_id User ID
 * @param string $role New role
 * @return array Response with status and message
 */
function updateUserRole($user_id, $role) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update user role'
    ];
    
    // Check if user is admin
    if (!isAdmin()) {
        $response['message'] = 'Access denied';
        return $response;
    }
    
    // Validate role
    $valid_roles = ['user', 'admin'];
    if (!in_array($role, $valid_roles)) {
        $response['message'] = 'Invalid role';
        return $response;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $result = $stmt->execute([$role, $user_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'User role updated successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Delete user (admin function)
 * @param int $user_id User ID
 * @return array Response with status and message
 */
function deleteUser($user_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to delete user'
    ];
    
    // Check if user is admin
    if (!isAdmin()) {
        $response['message'] = 'Access denied';
        return $response;
    }
    
    // Cannot delete yourself
    if ($_SESSION['user_id'] == $user_id) {
        $response['message'] = 'You cannot delete your own account';
        return $response;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get user statistics (admin function)
 * @return array Statistics
 */
function getUserStatistics() {
    global $conn;
    
    // Check if user is admin
    if (!isAdmin()) {
        return [];
    }
    
    $stats = [
        'total_users' => 0,
        'admin_users' => 0,
        'regular_users' => 0,
        'new_users_today' => 0,
        'new_users_week' => 0,
        'new_users_month' => 0
    ];
    
    try {
        // Total users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Users by role
        $role_stmt = $conn->query("
            SELECT role, COUNT(*) as count 
            FROM users 
            GROUP BY role
        ");
        
        while ($row = $role_stmt->fetch()) {
            if ($row['role'] === 'admin') {
                $stats['admin_users'] = $row['count'];
            } else {
                $stats['regular_users'] = $row['count'];
            }
        }
        
        // New users today
        $today_stmt = $conn->query("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stats['new_users_today'] = $today_stmt->fetch()['count'];
        
        // New users this week
        $week_stmt = $conn->query("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stats['new_users_week'] = $week_stmt->fetch()['count'];
        
        // New users this month
        $month_stmt = $conn->query("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stats['new_users_month'] = $month_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Get dashboard statistics (admin function)
 * @return array Statistics
 */
function getDashboardStatistics() {
    global $conn;
    
    // Check if user is admin
    if (!isAdmin()) {
        return [];
    }
    
    $stats = [
        'total_products' => 0,
        'out_of_stock_products' => 0,
        'total_categories' => 0,
        'total_orders' => 0,
        'pending_orders' => 0,
        'total_users' => 0,
        'total_revenue' => 0,
        'recent_orders' => []
    ];
    
    try {
        // Product stats
        $product_stmt = $conn->query("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $product_stmt->fetch()['count'];
        
        $out_of_stock_stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock = 0");
        $stats['out_of_stock_products'] = $out_of_stock_stmt->fetch()['count'];
        
        // Category stats
        $category_stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
        $stats['total_categories'] = $category_stmt->fetch()['count'];
        
        // Order stats
        $order_stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $order_stmt->fetch()['count'];
        
        $pending_stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $pending_stmt->fetch()['count'];
        
        // User stats
        $user_stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $user_stmt->fetch()['count'];
        
        // Revenue
        $revenue_stmt = $conn->query("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $revenue_stmt->fetch()['total'] ?: 0;
        
        // Recent orders
        $recent_stmt = $conn->query("
            SELECT o.id, o.total_amount, o.status, o.created_at, u.first_name, u.last_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $stats['recent_orders'] = $recent_stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return $stats;
}
?> 