<?php
require_once 'db_connect.php';
require_once 'product_functions.php';

/**
 * Add product to cart
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @param int $quantity Quantity to add
 * @return array Response with status and message
 */
function addToCart($user_id, $product_id, $quantity = 1) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to add product to cart',
        'cart_count' => 0
    ];
    
    // Validate product_id and quantity
    if (empty($product_id) || !is_numeric($product_id) || empty($quantity) || !is_numeric($quantity)) {
        $response['message'] = 'Invalid product ID or quantity';
        return $response;
    }
    
    // Get product
    $product = getProductById($product_id);
    if (!$product) {
        $response['message'] = 'Product not found';
        return $response;
    }
    
    // Check if product is in stock
    if ($product['stock'] <= 0) {
        $response['message'] = 'Product is out of stock';
        return $response;
    }
    
    // Check if quantity is valid
    if ($quantity <= 0) {
        $response['message'] = 'Quantity must be greater than 0';
        return $response;
    }
    
    // Check if quantity is available
    if ($quantity > $product['stock']) {
        $response['message'] = 'Not enough stock available. Only ' . $product['stock'] . ' left.';
        return $response;
    }
    
    try {
        // Check if product already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            // Update quantity
            $cart_item = $stmt->fetch();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            // Check if new quantity is available
            if ($new_quantity > $product['stock']) {
                $response['message'] = 'Not enough stock available. Only ' . $product['stock'] . ' left.';
                return $response;
            }
            
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $result = $update_stmt->execute([$new_quantity, $cart_item['id']]);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Cart updated successfully';
            }
        } else {
            // Add new cart item
            $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $result = $insert_stmt->execute([$user_id, $product_id, $quantity]);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Product added to cart successfully';
            }
        }
        
        // Get cart count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $response['cart_count'] = $count_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Remove product from cart
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return array Response with status and message
 */
function removeFromCart($user_id, $product_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to remove product from cart',
        'cart_count' => 0
    ];
    
    // Validate product_id
    if (empty($product_id) || !is_numeric($product_id)) {
        $response['message'] = 'Invalid product ID';
        return $response;
    }
    
    try {
        // Remove cart item
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$user_id, $product_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Product removed from cart successfully';
        }
        
        // Get cart count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $response['cart_count'] = $count_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Update cart item quantity
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @param int $quantity New quantity
 * @return array Response with status and message
 */
function updateCartItemQuantity($user_id, $product_id, $quantity) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update cart',
        'cart_count' => 0,
        'subtotal' => 0
    ];
    
    // Validate product_id and quantity
    if (empty($product_id) || !is_numeric($product_id) || empty($quantity) || !is_numeric($quantity)) {
        $response['message'] = 'Invalid product ID or quantity';
        return $response;
    }
    
    // Get product
    $product = getProductById($product_id);
    if (!$product) {
        $response['message'] = 'Product not found';
        return $response;
    }
    
    // Check if quantity is valid
    if ($quantity <= 0) {
        // Remove item from cart if quantity is 0 or negative
        return removeFromCart($user_id, $product_id);
    }
    
    // Check if quantity is available
    if ($quantity > $product['stock']) {
        $response['message'] = 'Not enough stock available. Only ' . $product['stock'] . ' left.';
        return $response;
    }
    
    try {
        // Check if product exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() === 0) {
            $response['message'] = 'Product not found in cart';
            return $response;
        }
        
        // Update quantity
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $result = $update_stmt->execute([$quantity, $user_id, $product_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Cart updated successfully';
            
            // Calculate item subtotal
            $price = isset($product['sale_price']) && $product['sale_price'] > 0 ? $product['sale_price'] : $product['price'];
            $response['subtotal'] = $price * $quantity;
        }
        
        // Get cart count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $response['cart_count'] = $count_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get cart items
 * @param int $user_id User ID
 * @return array Cart items with product details
 */
function getCartItems($user_id) {
    global $conn;
    
    $cart_items = [];
    $total = 0;
    
    try {
        $stmt = $conn->prepare("
            SELECT ci.*, p.name, p.slug, p.image, p.price, p.sale_price, p.stock, c.name as category_name, c.slug as category_slug
            FROM cart ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ci.user_id = ?
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            $cart_items = $stmt->fetchAll();
            
            // Calculate subtotal and total
            foreach ($cart_items as &$item) {
                $price = isset($item['sale_price']) && $item['sale_price'] > 0 ? $item['sale_price'] : $item['price'];
                $item['subtotal'] = $price * $item['quantity'];
                $total += $item['subtotal'];
            }
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return [
        'items' => $cart_items,
        'total' => $total,
        'count' => count($cart_items)
    ];
}

/**
 * Clear cart
 * @param int $user_id User ID
 * @return array Response with status and message
 */
function clearCart($user_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to clear cart'
    ];
    
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get cart count
 * @param int $user_id User ID
 * @return int Cart count
 */
function getCartCount($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return 0;
    }
}
?> 