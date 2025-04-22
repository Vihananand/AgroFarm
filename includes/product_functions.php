<?php
require_once 'db_connect.php';
require_once 'config.php';

/**
 * Get all products
 * @param array $options Optional parameters (limit, offset, category_id, search, sort_by)
 * @return array Array containing products and total count
 */
function getAllProducts($options = []) {
    global $conn;
    
    // Set default options
    $default_options = [
        'limit' => null,
        'offset' => 0,
        'category_id' => null,
        'search' => null,
        'sort_by' => 'id',
        'sort_order' => 'DESC',
        'featured_only' => false,
        'status' => 'active'
    ];
    
    $options = array_merge($default_options, $options);
    
    // Build SQL query
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1";
    
    $params = [];
    
    // Add filters
    if ($options['status']) {
        $sql .= " AND p.status = ?";
        $params[] = $options['status'];
    }
    
    if ($options['category_id']) {
        $sql .= " AND p.category_id = ?";
        $params[] = $options['category_id'];
    }
    
    if ($options['search']) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search_term = '%' . $options['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($options['featured_only']) {
        $sql .= " AND p.featured = 1";
    }
    
    // Count total records before applying limit
    $count_sql = str_replace("SELECT p.*, c.name as category_name, c.slug as category_slug", "SELECT COUNT(*) as total", $sql);
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetch()['total'];
    
    // Add sorting
    $valid_sort_fields = ['id', 'name', 'price', 'created_at'];
    $valid_sort_orders = ['ASC', 'DESC'];
    
    $sort_field = in_array($options['sort_by'], $valid_sort_fields) ? $options['sort_by'] : 'id';
    $sort_order = in_array(strtoupper($options['sort_order']), $valid_sort_orders) ? strtoupper($options['sort_order']) : 'DESC';
    
    $sql .= " ORDER BY p.$sort_field $sort_order";
    
    // Add pagination
    if ($options['limit']) {
        $sql .= " LIMIT ?, ?";
        $params[] = (int)$options['offset'];
        $params[] = (int)$options['limit'];
    }
    
    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    return [
        'products' => $products,
        'total' => $total
    ];
}

/**
 * Get product by ID
 * @param int $id Product ID
 * @return array|null Product data or null if not found
 */
function getProductById($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Get product by slug
 * @param string $slug Product slug
 * @return array|null Product data or null if not found
 */
function getProductBySlug($slug) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Add a new product
 * @param array $product_data Product data
 * @return array Response with status and message
 */
function addProduct($product_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to add product'
    ];
    
    // Validate required fields
    $required_fields = ['name', 'description', 'price', 'category_id'];
    foreach ($required_fields as $field) {
        if (!isset($product_data[$field]) || ($field !== 'description' && empty(trim($product_data[$field])))) {
            $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            return $response;
        }
    }
    
    // Create slug from name
    $slug = createSlug($product_data['name']);
    
    // Check if slug already exists
    $check_slug = $conn->prepare("SELECT id FROM products WHERE slug = ?");
    $check_slug->execute([$slug]);
    
    if ($check_slug->rowCount() > 0) {
        // Add random string to make slug unique
        $slug = $slug . '-' . substr(md5(time()), 0, 5);
    }
    
    try {
        // Insert product into database
        $stmt = $conn->prepare("
            INSERT INTO products (
                name, slug, description, image, price, sale_price, stock, 
                category_id, status, featured
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");
        
        $stmt->execute([
            $product_data['name'],
            $slug,
            $product_data['description'],
            $product_data['image'] ?? null,
            $product_data['price'],
            $product_data['sale_price'] ?? null,
            $product_data['stock'] ?? 0,
            $product_data['category_id'],
            $product_data['status'] ?? 'active',
            $product_data['featured'] ?? 0
        ]);
        
        if ($stmt->rowCount()) {
            $response['success'] = true;
            $response['message'] = 'Product added successfully';
            $response['product_id'] = $conn->lastInsertId();
            $response['slug'] = $slug;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Update a product
 * @param int $id Product ID
 * @param array $product_data Product data to update
 * @return array Response with status and message
 */
function updateProduct($id, $product_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update product'
    ];
    
    // Check if product exists
    $product = getProductById($id);
    if (!$product) {
        $response['message'] = 'Product not found';
        return $response;
    }
    
    // Prepare fields to update
    $fields = [];
    $values = [];
    
    // Only update fields that are provided
    if (isset($product_data['name']) && !empty(trim($product_data['name']))) {
        $fields[] = 'name = ?';
        $values[] = $product_data['name'];
        
        // Update slug if name is changed
        if ($product_data['name'] !== $product['name']) {
            $slug = createSlug($product_data['name']);
            
            // Check if new slug already exists for another product
            $check_slug = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $check_slug->execute([$slug, $id]);
            
            if ($check_slug->rowCount() > 0) {
                // Add random string to make slug unique
                $slug = $slug . '-' . substr(md5(time()), 0, 5);
            }
            
            $fields[] = 'slug = ?';
            $values[] = $slug;
        }
    }
    
    if (isset($product_data['description'])) {
        $fields[] = 'description = ?';
        $values[] = $product_data['description'];
    }
    
    if (isset($product_data['image'])) {
        $fields[] = 'image = ?';
        $values[] = $product_data['image'];
    }
    
    if (isset($product_data['price'])) {
        $fields[] = 'price = ?';
        $values[] = $product_data['price'];
    }
    
    if (array_key_exists('sale_price', $product_data)) {
        $fields[] = 'sale_price = ?';
        $values[] = $product_data['sale_price'];
    }
    
    if (isset($product_data['stock'])) {
        $fields[] = 'stock = ?';
        $values[] = $product_data['stock'];
    }
    
    if (isset($product_data['category_id'])) {
        $fields[] = 'category_id = ?';
        $values[] = $product_data['category_id'];
    }
    
    if (isset($product_data['status'])) {
        $fields[] = 'status = ?';
        $values[] = $product_data['status'];
    }
    
    if (isset($product_data['featured'])) {
        $fields[] = 'featured = ?';
        $values[] = $product_data['featured'];
    }
    
    if (empty($fields)) {
        $response['message'] = 'No fields to update';
        return $response;
    }
    
    try {
        // Build update query
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;
        
        // Execute update
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully';
            
            if (isset($slug)) {
                $response['slug'] = $slug;
            }
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Delete a product
 * @param int $id Product ID
 * @return array Response with status and message
 */
function deleteProduct($id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to delete product'
    ];
    
    // Check if product exists
    $product = getProductById($id);
    if (!$product) {
        $response['message'] = 'Product not found';
        return $response;
    }
    
    try {
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get all categories
 * @return array Array of categories
 */
function getAllCategories() {
    global $conn;
    
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Get category by ID
 * @param int $id Category ID
 * @return array|null Category data or null if not found
 */
function getCategoryById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Get category by slug
 * @param string $slug Category slug
 * @return array|null Category data or null if not found
 */
function getCategoryBySlug($slug) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    
    if ($stmt->rowCount() > 0) {
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Add a new category
 * @param array $category_data Category data
 * @return array Response with status and message
 */
function addCategory($category_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to add category'
    ];
    
    // Validate required fields
    if (!isset($category_data['name']) || empty(trim($category_data['name']))) {
        $response['message'] = 'Category name is required';
        return $response;
    }
    
    // Create slug from name
    $slug = createSlug($category_data['name']);
    
    // Check if slug already exists
    $check_slug = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $check_slug->execute([$slug]);
    
    if ($check_slug->rowCount() > 0) {
        // Add random string to make slug unique
        $slug = $slug . '-' . substr(md5(time()), 0, 5);
    }
    
    try {
        // Insert category into database
        $stmt = $conn->prepare("
            INSERT INTO categories (name, slug, description) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $category_data['name'],
            $slug,
            $category_data['description'] ?? null
        ]);
        
        if ($stmt->rowCount()) {
            $response['success'] = true;
            $response['message'] = 'Category added successfully';
            $response['category_id'] = $conn->lastInsertId();
            $response['slug'] = $slug;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Update a category
 * @param int $id Category ID
 * @param array $category_data Category data to update
 * @return array Response with status and message
 */
function updateCategory($id, $category_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update category'
    ];
    
    // Check if category exists
    $category = getCategoryById($id);
    if (!$category) {
        $response['message'] = 'Category not found';
        return $response;
    }
    
    // Prepare fields to update
    $fields = [];
    $values = [];
    
    // Only update fields that are provided
    if (isset($category_data['name']) && !empty(trim($category_data['name']))) {
        $fields[] = 'name = ?';
        $values[] = $category_data['name'];
        
        // Update slug if name is changed
        if ($category_data['name'] !== $category['name']) {
            $slug = createSlug($category_data['name']);
            
            // Check if new slug already exists for another category
            $check_slug = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $check_slug->execute([$slug, $id]);
            
            if ($check_slug->rowCount() > 0) {
                // Add random string to make slug unique
                $slug = $slug . '-' . substr(md5(time()), 0, 5);
            }
            
            $fields[] = 'slug = ?';
            $values[] = $slug;
        }
    }
    
    if (isset($category_data['description'])) {
        $fields[] = 'description = ?';
        $values[] = $category_data['description'];
    }
    
    if (empty($fields)) {
        $response['message'] = 'No fields to update';
        return $response;
    }
    
    try {
        // Build update query
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;
        
        // Execute update
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Category updated successfully';
            
            if (isset($slug)) {
                $response['slug'] = $slug;
            }
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Delete a category
 * @param int $id Category ID
 * @return array Response with status and message
 */
function deleteCategory($id) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to delete category'
    ];
    
    // Check if category exists
    $category = getCategoryById($id);
    if (!$category) {
        $response['message'] = 'Category not found';
        return $response;
    }
    
    // Check if category has associated products
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        $response['message'] = 'Cannot delete category with associated products';
        return $response;
    }
    
    try {
        // Delete category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Category deleted successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Create a slug from a string
 * @param string $string String to convert to slug
 * @return string Slug
 */
function createSlug($string) {
    // Replace spaces with hyphens
    $string = str_replace(' ', '-', $string);
    // Remove special characters
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    // Convert to lowercase
    $string = strtolower($string);
    // Remove multiple hyphens
    $string = preg_replace('/-+/', '-', $string);
    // Remove leading/trailing hyphens
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Get related products
 * @param int $product_id Current product ID
 * @param int $category_id Category ID
 * @param int $limit Number of products to get
 * @return array Array of related products
 */
function getRelatedProducts($product_id, $category_id, $limit = 4) {
    global $conn;
    
    $sql = "
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id != ? AND p.category_id = ? AND p.status = 'active'
        ORDER BY RAND()
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$product_id, $category_id, $limit]);
    
    return $stmt->fetchAll();
}

/**
 * Search products
 * @param string $query Search query
 * @param int $limit Number of products to get
 * @return array Array of products
 */
function searchProducts($query, $limit = 20) {
    global $conn;
    
    $sql = "
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' AND (
            p.name LIKE ? OR 
            p.description LIKE ? OR
            c.name LIKE ?
        )
        ORDER BY p.name ASC
        LIMIT ?
    ";
    
    $search_term = '%' . $query . '%';
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$search_term, $search_term, $search_term, $limit]);
    
    return $stmt->fetchAll();
}

/**
 * Upload a product image
 * @param array $file $_FILES array
 * @return array Response with status, message and image path
 */
function uploadProductImage($file) {
    $response = [
        'success' => false,
        'message' => 'Failed to upload image',
        'image_path' => null
    ];
    
    // Check if file was uploaded
    if (!isset($file) || !isset($file['name']) || empty($file['name'])) {
        $response['message'] = 'No file was uploaded';
        return $response;
    }
    
    // Define allowed file extensions
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Get file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file extension
    if (!in_array($file_extension, $allowed_extensions)) {
        $response['message'] = 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions);
        return $response;
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File size too large. Maximum 5MB allowed.';
        return $response;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $filename = uniqid('product_') . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $response['success'] = true;
        $response['message'] = 'Image uploaded successfully';
        $response['image_path'] = 'uploads/products/' . $filename;
    } else {
        $response['message'] = 'Failed to move uploaded file';
    }
    
    return $response;
}
?> 