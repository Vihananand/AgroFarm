<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Use the existing database connection
    if (!isset($conn) || !$conn) {
        // Create a new connection if needed
        $database = new Database();
        $conn = $database->connect();
        
        if (!$conn) {
            throw new Exception('Failed to connect to database');
        }
    }
    
    $response = ['success' => true, 'data' => []];
    
    // Get categories
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['data']['categories'] = $categories;
    
    // Get products with category information
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure category information is properly set
    foreach ($products as &$product) {
        // If product has no category, set to default
        if (!isset($product['category_id']) || !$product['category_id']) {
            $product['category_name'] = 'Uncategorized';
            $product['category_slug'] = 'uncategorized';
        }
        
        // Debug information
        error_log("Product ID: {$product['id']}, Name: {$product['name']}, Category: {$product['category_name']}, Slug: {$product['category_slug']}");
    }
    
    $response['data']['products'] = $products;
    
    // Log the response for debugging
    error_log('API Response: ' . count($products) . ' products, ' . count($categories) . ' categories');
    
    echo json_encode($response);
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} 