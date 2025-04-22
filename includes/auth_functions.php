<?php
require_once 'db_connect.php';
require_once 'config.php';

/**
 * Register a new user
 * @param array $user_data User data including first_name, last_name, email, password
 * @return array Response with status and message
 */
function registerUser($user_data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Registration failed'
    ];
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (!isset($user_data[$field]) || empty(trim($user_data[$field]))) {
            $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            return $response;
        }
    }
    
    // Validate email format
    if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        return $response;
    }
    
    // Check if passwords match
    if ($user_data['password'] !== $user_data['confirm_password']) {
        $response['message'] = 'Passwords do not match';
        return $response;
    }
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->execute([$user_data['email']]);
    
    if ($check_email->rowCount() > 0) {
        $response['message'] = 'Email already exists';
        return $response;
    }
    
    // Hash password
    $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
    
    try {
        // Insert user into database
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, zip_code, country) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_data['first_name'],
            $user_data['last_name'],
            $user_data['email'],
            $hashed_password,
            $user_data['phone'] ?? null,
            $user_data['address'] ?? null,
            $user_data['city'] ?? null,
            $user_data['state'] ?? null,
            $user_data['zip_code'] ?? null,
            $user_data['country'] ?? null
        ]);
        
        if ($stmt->rowCount()) {
            $response['success'] = true;
            $response['message'] = 'Registration successful. You can now log in.';
            $response['user_id'] = $conn->lastInsertId();
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Login a user
 * @param string $email User email
 * @param string $password User password
 * @return array Response with status and message
 */
function loginUser($email, $password) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Login failed'
    ];
    
    // Validate email and password
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email and password are required';
        return $response;
    }
    
    try {
        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            $response['message'] = 'Invalid email or password';
            return $response;
        }
        
        $user = $stmt->fetch();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            $_SESSION['is_logged_in'] = true;
            
            // Success response
            $response['success'] = true;
            $response['message'] = 'Login successful';
            $response['user'] = [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'customer'
            ];
        } else {
            $response['message'] = 'Invalid email or password';
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Get current user ID
 * @return int|null User ID if logged in, null otherwise
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null User role if logged in, null otherwise
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && getUserRole() === 'admin';
}

/**
 * Log out the current user
 * @return void
 */
function logoutUser() {
    session_start();
    session_destroy();
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

/**
 * Get current user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    
    try {
        // Only select columns that we know exist in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Ensure required fields exist even if db doesn't have them
            if (!isset($user['first_name'])) $user['first_name'] = $_SESSION['first_name'] ?? 'User';
            if (!isset($user['last_name'])) $user['last_name'] = $_SESSION['last_name'] ?? '';
            if (!isset($user['role'])) $user['role'] = $_SESSION['user_role'] ?? 'customer';
            return $user;
        }
    } catch (PDOException $e) {
        error_log('Database error in getCurrentUser: ' . $e->getMessage());
    }
    
    // If we can't get from DB, return data from session as a fallback
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? '',
        'first_name' => $_SESSION['first_name'] ?? 'User',
        'last_name' => $_SESSION['last_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Update user profile
 * @param array $user_data User data to update
 * @return array Response with status and message
 */
function updateUserProfile($user_data) {
    if (!isLoggedIn()) {
        return [
            'success' => false,
            'message' => 'You must be logged in to update your profile'
        ];
    }
    
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update profile'
    ];
    
    try {
        $fields = [
            'first_name' => $user_data['first_name'] ?? null,
            'last_name' => $user_data['last_name'] ?? null,
            'phone' => $user_data['phone'] ?? null,
            'address' => $user_data['address'] ?? null,
            'city' => $user_data['city'] ?? null,
            'state' => $user_data['state'] ?? null,
            'zip_code' => $user_data['zip_code'] ?? null,
            'country' => $user_data['country'] ?? null
        ];
        
        // Filter out null values
        $fields = array_filter($fields, function($value) {
            return $value !== null;
        });
        
        if (empty($fields)) {
            $response['message'] = 'No fields to update';
            return $response;
        }
        
        // Build SQL query
        $sql = "UPDATE users SET ";
        $sql_parts = [];
        $values = [];
        
        foreach ($fields as $field => $value) {
            $sql_parts[] = "$field = ?";
            $values[] = $value;
        }
        
        $sql .= implode(', ', $sql_parts);
        $sql .= " WHERE id = ?";
        $values[] = $_SESSION['user_id'];
        
        // Execute update
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully';
            
            // Update session variables if needed
            if (isset($fields['first_name']) || isset($fields['last_name'])) {
                $user = getCurrentUser();
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            }
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Change user password
 * @param string $current_password Current password
 * @param string $new_password New password
 * @param string $confirm_password Confirm new password
 * @return array Response with status and message
 */
function changePassword($current_password, $new_password, $confirm_password) {
    if (!isLoggedIn()) {
        return [
            'success' => false,
            'message' => 'You must be logged in to change your password'
        ];
    }
    
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to change password'
    ];
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = 'All password fields are required';
        return $response;
    }
    
    if ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match';
        return $response;
    }
    
    try {
        // Get current user's password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $response['message'] = 'Current password is incorrect';
            return $response;
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Password changed successfully';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $response;
}

/**
 * Get cart item count
 * @return int Number of items in cart
 */
function getCartItemCount() {
    global $conn;
    
    if (!isLoggedIn()) {
        return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([getUserId()]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get wishlist item count
 * @return int Number of items in wishlist
 */
function getWishlistItemCount() {
    global $conn;
    
    if (!isLoggedIn()) {
        return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist_items WHERE user_id = ?");
        $stmt->execute([getUserId()]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return 0;
    }
}
?> 