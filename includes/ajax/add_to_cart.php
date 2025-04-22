<?php
require_once '../config.php';
require_once '../db_connect.php';
require_once '../cart_functions.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'cart_count' => 0
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login to add items to cart';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit;
}

if ($quantity <= 0) {
    $response['message'] = 'Quantity must be at least 1.';
    echo json_encode($response);
    exit;
}

// Add to cart using the cart functions
$result = addToCart($user_id, $product_id, $quantity);
echo json_encode($result);
exit;