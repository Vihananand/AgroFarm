<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'wishlist_count' => 0
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['product_id'])) {
    $response['message'] = 'Missing required parameter: product_id.';
    echo json_encode($response);
    exit;
}

$product_id = (int)$_POST['product_id'];

if ($product_id <= 0) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit;
}

try {
    $all_products = [
        1 => [
            'id' => 1,
            'name' => 'Organic Fertilizer',
        ],
        2 => [
            'id' => 2,
            'name' => 'Premium Garden Hoe',
        ],
        3 => [
            'id' => 3,
            'name' => 'Organic Tomato Seeds',
        ],
        4 => [
            'id' => 4,
            'name' => 'Mini Tractor',
        ],
        5 => [
            'id' => 5,
            'name' => 'Fresh Apples (5kg)',
        ],
        6 => [
            'id' => 6,
            'name' => 'Gardening Gloves',
        ],
        7 => [
            'id' => 7,
            'name' => 'Carrot Seeds',
        ],
        8 => [
            'id' => 8,
            'name' => 'Irrigation System',
        ],
        9 => [
            'id' => 9,
            'name' => 'Potato Harvester',
        ],
        10 => [
            'id' => 10,
            'name' => 'Organic Strawberries (1kg)',
        ]
    ];
    
    if (!isset($all_products[$product_id])) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }
    
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    
    if (in_array($product_id, $_SESSION['wishlist'])) {
        $response['success'] = true;
        $response['message'] = 'Product is already in your wishlist.';
    } else {
        $_SESSION['wishlist'][] = $product_id;
        $response['success'] = true;
        $response['message'] = 'Product added to wishlist successfully.';
    }
    
    $response['wishlist_count'] = count($_SESSION['wishlist']);
    
} catch (Exception $e) {
    error_log('Wishlist error: ' . $e->getMessage());
    $response['message'] = 'A system error occurred. Please try again later.';
}

echo json_encode($response);
exit; 