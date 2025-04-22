<?php
// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration and database connection
require_once '../config.php';
require_once '../db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default response array
$response = [
    'success' => false,
    'message' => 'An error occurred while processing your request.'
];

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    $response['message'] = 'You must be logged in to cancel a request.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Check if request_id is provided
if (!isset($_POST['request_id']) || !is_numeric($_POST['request_id'])) {
    $response['message'] = 'Invalid request ID.';
    echo json_encode($response);
    exit;
}

$request_id = (int)$_POST['request_id'];

// Verify the request belongs to the current user and is in a status that can be cancelled
try {
    $stmt = $pdo->prepare("
        SELECT id, status FROM custom_requests 
        WHERE id = :id AND user_id = :user_id 
        LIMIT 1
    ");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        $response['message'] = 'Request not found or you do not have permission to cancel it.';
        echo json_encode($response);
        exit;
    }
    
    // Only allow cancellation if request is in pending or processing status
    if ($request['status'] !== 'pending' && $request['status'] !== 'processing') {
        $response['message'] = 'This request cannot be cancelled in its current status.';
        echo json_encode($response);
        exit;
    }
    
    // Update the request status to cancelled
    $stmt = $pdo->prepare("
        UPDATE custom_requests 
        SET status = 'cancelled', updated_at = NOW() 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Add a comment regarding cancellation
        $comment = "Request cancelled by customer.";
        
        $stmt = $pdo->prepare("
            INSERT INTO request_comments (request_id, user_id, comment, created_at) 
            VALUES (:request_id, :user_id, :comment, NOW())
        ");
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        
        $response['success'] = true;
        $response['message'] = 'Your request has been cancelled successfully.';
        
        // Set redirect URL in the response
        $response['redirect'] = SITE_URL . '/user/request-details.php?id=' . $request_id;
    } else {
        $response['message'] = 'Failed to cancel the request. Please try again.';
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $response['message'] = 'An error occurred while processing your request. Please try again later.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response); 