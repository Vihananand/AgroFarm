<?php
require_once '../config.php';
require_once '../db.php';
require_once '../auth_functions.php';
require_once '../order_functions.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    $response['message'] = 'You must be logged in to perform this action';
    echo json_encode($response);
    exit;
}

// Check if order_id is provided
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    $response['message'] = 'Order ID is required';
    echo json_encode($response);
    exit;
}

$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$delete_completely = isset($_POST['delete_completely']) && $_POST['delete_completely'] === 'true';
$admin_action = isset($_POST['admin_action']) && $_POST['admin_action'] === 'true';

// Only admins can perform admin actions
if ($admin_action && !$is_admin) {
    $response['message'] = 'Unauthorized action';
    echo json_encode($response);
    exit;
}

try {
    // Get order details to verify ownership
    $order = getOrderById($order_id);
    
    if (!$order) {
        $response['message'] = 'Order not found';
        echo json_encode($response);
        exit;
    }
    
    // Only the owner or admin can cancel the order
    if ($order['user_id'] != $user_id && !$is_admin) {
        $response['message'] = 'You do not have permission to cancel this order';
        echo json_encode($response);
        exit;
    }
    
    // Check if order can be cancelled (only pending or processing orders)
    if (!$is_admin && $order['status'] !== 'pending' && $order['status'] !== 'processing') {
        $response['message'] = 'This order cannot be cancelled';
        echo json_encode($response);
        exit;
    }
    
    // Process the cancel or delete action
    if ($delete_completely && $is_admin) {
        // Only admins can delete orders completely
        $result = deleteOrderCompletely($order_id);
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Order has been deleted completely';
        } else {
            $response['message'] = 'Failed to delete order';
        }
    } else {
        // Cancel order
        $result = cancelOrder($order_id);
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Order has been cancelled successfully';
        } else {
            $response['message'] = 'Failed to cancel order';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    // Log the error
    error_log("Order cancellation error: " . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit; 