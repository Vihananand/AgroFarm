<?php
require_once 'db_connect.php';
require_once 'auth_functions.php';

// Only process if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in and admin
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if file is uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = 'No file uploaded or upload error occurred';
    if (isset($_FILES['image']['error'])) {
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'The uploaded file exceeds the maximum file size';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error = 'A PHP extension stopped the file upload';
                break;
        }
    }
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

// Get uploaded file
$file = $_FILES['image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: JPEG, PNG, GIF, WebP']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds limit of 5MB']);
    exit;
}

// Create upload directory if not exists
$upload_dir = '../uploads/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$filename = uniqid('product_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$dest_path = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $dest_path)) {
    // Return success response with path
    $image_url = 'uploads/products/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'image_url' => $image_url,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?> 