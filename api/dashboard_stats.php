<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get the requested action
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    // First check if the necessary tables exist
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $debug = ['tables' => $tables];
    
    switch ($action) {
        case 'low_stock_products':
            // Check if products table exists
            if (!in_array('products', $tables)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Products table does not exist in the database. Please make sure your database is properly set up.',
                    'debug' => $debug
                ]);
                exit;
            }
            
            // Get products with low stock
            $stmt = $conn->query("
                SELECT * FROM products 
                WHERE stock < 10 
                ORDER BY stock ASC 
                LIMIT 5
            ");
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'products' => $products
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
    }
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