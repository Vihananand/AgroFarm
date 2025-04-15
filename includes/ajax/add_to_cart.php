<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'cart_count' => 0
];

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

try {
    $all_products = [
        1 => [
            'id' => 1,
            'name' => 'Organic Fertilizer',
            'stock' => 15,
        ],
        2 => [
            'id' => 2,
            'name' => 'Premium Garden Hoe',
            'stock' => 8,
        ],
        3 => [
            'id' => 3,
            'name' => 'Organic Tomato Seeds',
            'stock' => 50,
        ],
        4 => [
            'id' => 4,
            'name' => 'Mini Tractor',
            'stock' => 0,
        ],
        5 => [
            'id' => 5,
            'name' => 'Fresh Apples (5kg)',
            'stock' => 20,
        ],
        6 => [
            'id' => 6,
            'name' => 'Gardening Gloves',
            'stock' => 30,
        ],
        7 => [
            'id' => 7,
            'name' => 'Carrot Seeds',
            'stock' => 45,
        ],
        8 => [
            'id' => 8,
            'name' => 'Irrigation System',
            'stock' => 10,
        ],
        9 => [
            'id' => 9,
            'name' => 'Potato Harvester',
            'stock' => 5,
        ],
        10 => [
            'id' => 10,
            'name' => 'Organic Strawberries (1kg)',
            'stock' => 15,
        ]
    ];
    
    if (!isset($all_products[$product_id])) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }
    
    $product = $all_products[$product_id];
    
    if ($product['stock'] < $quantity) {
        $response['message'] = 'Not enough stock available. Only ' . $product['stock'] . ' items left.';
        echo json_encode($response);
        exit;
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
        
        if ($new_quantity > $product['stock']) {
            $response['message'] = 'Cannot add more of this item. Stock limit reached.';
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['cart'][$product_id] = $new_quantity;
        $response['success'] = true;
        $response['message'] = 'Cart updated successfully.';
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
        $response['success'] = true;
        $response['message'] = 'Item added to cart successfully.';
    }
    
    $response['cart_count'] = array_sum($_SESSION['cart']);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit; 