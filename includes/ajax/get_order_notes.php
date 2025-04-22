<?php
require_once '../config.php';
require_once '../db.php';
require_once '../auth_functions.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'notes' => []
];

// Check if user is logged in and is admin or customer viewing their own order
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $response['message'] = 'Order ID is required';
    echo json_encode($response);
    exit;
}

$order_id = intval($_GET['order_id']);

// Validate order_id
if ($order_id <= 0) {
    $response['message'] = 'Invalid order ID';
    echo json_encode($response);
    exit;
}

try {
    global $conn;
    
    // Check if order exists and if user has permission to view it
    if ($_SESSION['user_role'] === 'admin') {
        // Admin can view any order
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
    } else {
        // Regular user can only view their own orders
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
    }
    
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'Order not found or you do not have permission to view it';
        echo json_encode($response);
        exit;
    }
    
    // Get all notes for the order
    $stmt = $conn->prepare("
        SELECT n.id, n.note, n.created_at, 
               CONCAT(u.first_name, ' ', u.last_name) as admin_name
        FROM order_notes n
        JOIN users u ON n.admin_id = u.id
        WHERE n.order_id = ?
        ORDER BY n.created_at DESC
    ");
    
    $stmt->execute([$order_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['message'] = 'Notes retrieved successfully';
    $response['notes'] = $notes;
    
} catch (Exception $e) {
    error_log("Get order notes error: " . $e->getMessage());
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?> 