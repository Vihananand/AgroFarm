<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if request ID is provided
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Request ID is required'
    ]);
    exit;
}

$request_id = (int)$_GET['id'];

try {
    // Get request details
    $stmt = $conn->prepare("
        SELECT cr.*, u.first_name, u.last_name 
        FROM custom_requests cr
        LEFT JOIN users u ON cr.user_id = u.id
        WHERE cr.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
        exit;
    }

    // Format dates
    $request['created_at'] = date('M d, Y H:i', strtotime($request['created_at']));
    $request['updated_at'] = date('M d, Y H:i', strtotime($request['updated_at']));

    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
} catch (PDOException $e) {
    error_log("Error fetching request details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching request details'
    ]);
} 