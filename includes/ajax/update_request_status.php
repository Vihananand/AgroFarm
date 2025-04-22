<?php
// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration and database connection
require_once '../config.php';
require_once '../db.php';
session_start();

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate input data
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate request ID
if ($request_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Update the request status
    $stmt = $pdo->prepare("UPDATE custom_requests SET status = :status, updated_at = NOW() WHERE id = :id");
    $result = $stmt->execute([
        ':status' => $status,
        ':id' => $request_id
    ]);

    if ($result && $stmt->rowCount() > 0) {
        // Set success message
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Request status has been updated to ' . ucfirst($status)
        ];
    } else {
        // Set error message
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Failed to update request status. Request not found.'
        ];
    }

    // Redirect back to the request history page
    header('Location: ' . SITE_URL . '/pages/custom-request-history.php');
    exit;

} catch (PDOException $e) {
    // Log the error
    error_log('Database error: ' . $e->getMessage());
    
    // Set error message
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'An error occurred while updating the request status'
    ];
    
    // Redirect back to the request history page
    header('Location: ' . SITE_URL . '/pages/custom-request-history.php');
    exit;
} 