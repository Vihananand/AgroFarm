<?php
// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration and database connection
require_once '../config.php';
require_once '../db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request ID is provided
if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

$request_id = intval($_GET['request_id']);

try {
    // Get request details
    $stmt = $pdo->prepare("
        SELECT cr.*, u.first_name, u.last_name, u.email, u.phone
        FROM custom_requests cr
        LEFT JOIN users u ON cr.user_id = u.id
        WHERE cr.id = :request_id
    ");
    
    $stmt->execute([':request_id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit;
    }
    
    // Check if the user is admin or the owner of the request
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $request['user_id']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You do not have permission to view this request']);
        exit;
    }
    
    // Format dates
    $request['created_at'] = date('F j, Y, g:i a', strtotime($request['created_at']));
    if ($request['updated_at']) {
        $request['updated_at'] = date('F j, Y, g:i a', strtotime($request['updated_at']));
    } else {
        $request['updated_at'] = 'Not updated yet';
    }
    
    // Status labels and classes
    $statusLabels = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];
    
    $statusClasses = [
        'pending' => 'bg-warning',
        'processing' => 'bg-info',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    
    $request['status_label'] = $statusLabels[$request['status']] ?? ucfirst($request['status']);
    $request['status_class'] = $statusClasses[$request['status']] ?? 'bg-secondary';
    
    // Set customer name
    $request['customer_name'] = $request['first_name'] . ' ' . $request['last_name'];
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $request]);
    
} catch (PDOException $e) {
    // Log the error
    error_log('Database error: ' . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching the request details']);
    exit;
} 