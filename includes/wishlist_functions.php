<?php
require_once 'db_connect.php';
require_once 'product_functions.php';

/**
 * Add product to wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return array Response with status and message
 */
function addToWishlist($user_id, $product_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to add product to wishlist',
        'wishlist_count' => 0
    ];
    
    // Validate product_id
    if (empty($product_id) || !is_numeric($product_id)) {
        $response['message'] = 'Invalid product ID';
        return $response;
    }
    
    // Get product
    $product = getProductById($product_id);
    if (!$product) {
        $response['message'] = 'Product not found';
        return $response;
    }
    
    try {
        // Check if product already exists in wishlist
        $stmt = $conn->prepare("SELECT * FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Product is already in your wishlist';
        } else {
            // Add to wishlist
            $insert_stmt = $conn->prepare("INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
            $result = $insert_stmt->execute([$user_id, $product_id]);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Product added to wishlist successfully';
            }
        }
        
        // Get wishlist count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $response['wishlist_count'] = $count_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Remove product from wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return array Response with status and message
 */
function removeFromWishlist($user_id, $product_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to remove product from wishlist',
        'wishlist_count' => 0
    ];
    
    // Validate product_id
    if (empty($product_id) || !is_numeric($product_id)) {
        $response['message'] = 'Invalid product ID';
        return $response;
    }
    
    try {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$user_id, $product_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Product removed from wishlist successfully';
        }
        
        // Get wishlist count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $response['wishlist_count'] = $count_stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get wishlist items
 * @param int $user_id User ID
 * @return array Wishlist items with product details
 */
function getWishlistItems($user_id) {
    global $conn;
    
    $wishlist_items = [];
    
    try {
        $stmt = $conn->prepare("
            SELECT wi.*, p.name, p.slug, p.image, p.price, p.sale_price, p.stock, c.name as category_name, c.slug as category_slug
            FROM wishlist_items wi
            JOIN products p ON wi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE wi.user_id = ?
            ORDER BY wi.created_at DESC
        ");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            $wishlist_items = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
    }
    
    return [
        'items' => $wishlist_items,
        'count' => count($wishlist_items)
    ];
}

/**
 * Clear wishlist
 * @param int $user_id User ID
 * @return array Response with status and message
 */
function clearWishlist($user_id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to clear wishlist'
    ];
    
    try {
        $stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Wishlist cleared successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Check if product is in wishlist
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool True if product is in wishlist, false otherwise
 */
function isInWishlist($user_id, $product_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get wishlist count
 * @param int $user_id User ID
 * @return int Wishlist count
 */
function getWishlistCount($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return 0;
    }
}
?> 