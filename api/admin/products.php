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
    // Get query parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $low_stock = isset($_GET['low_stock']) ? (bool)$_GET['low_stock'] : false;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
    $sort_order = isset($_GET['sort_order']) ? strtoupper($_GET['sort_order']) : 'DESC';
    
    // Base query
    $query = "
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add filters
    if ($category_id) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($low_stock) {
        $query .= " AND p.stock < 10";
    }
    
    // Count total products that match the criteria
    $countQuery = str_replace(
        "SELECT p.*, c.name as category_name, c.slug as category_slug", 
        "SELECT COUNT(*)", 
        $query
    );
    
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    // Add ordering and pagination
    $validSortFields = ['id', 'name', 'price', 'stock', 'created_at'];
    $validSortOrders = ['ASC', 'DESC'];
    
    $sortField = in_array($sort_by, $validSortFields) ? $sort_by : 'id';
    $sortOrder = in_array($sort_order, $validSortOrders) ? $sort_order : 'DESC';
    
    $query .= " ORDER BY p.$sortField $sortOrder LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Get products
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Format products for response with proper image URLs
    $formattedProducts = [];
    foreach ($products as $product) {
        // Process image URL
        $image = $product['image'];
        if (!$image) {
            $image = 'default-product.jpg';
        }
        
        // Add to formatted products
        $formattedProducts[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'stock' => $product['stock'],
            'image' => $image,
            'category_id' => $product['category_id'],
            'category_name' => $product['category_name'] ?? 'Uncategorized',
            'category_slug' => $product['category_slug'] ?? 'uncategorized',
            'featured' => $product['featured'],
            'status' => $product['status'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'total' => $total,
        'page' => floor($offset / $limit) + 1,
        'total_pages' => ceil($total / $limit),
        'products' => $formattedProducts
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Products API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Products API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 