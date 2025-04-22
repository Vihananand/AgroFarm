<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use absolute path for includes
$base_path = realpath(dirname(__FILE__) . '/../..');
require_once $base_path . '/includes/config.php';

// Try both possible database include files
if (file_exists($base_path . '/includes/db_connect.php')) {
    require_once $base_path . '/includes/db_connect.php';
} elseif (file_exists($base_path . '/includes/db.php')) {
    require_once $base_path . '/includes/db.php';
} else {
    die('Database connection file not found!');
}

require_once $base_path . '/includes/auth_functions.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'note' => null
];

// Check if user is logged in and is admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if required fields are provided
if (!isset($_POST['order_id']) || empty($_POST['order_id']) || 
    !isset($_POST['note']) || empty($_POST['note'])) {
    $response['message'] = 'Order ID and note content are required';
    echo json_encode($response);
    exit;
}

$order_id = intval($_POST['order_id']);
$note_content = trim($_POST['note']);
$admin_id = $_SESSION['user_id'];

// Validate order_id
if ($order_id <= 0) {
    $response['message'] = 'Invalid order ID';
    echo json_encode($response);
    exit;
}

// Validate note content
if (strlen($note_content) > 1000) {
    $response['message'] = 'Note content is too long (maximum 1000 characters)';
    echo json_encode($response);
    exit;
}

try {
    global $conn;
    
    // Check if order exists
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'Order not found';
        echo json_encode($response);
        exit;
    }
    
    // Check if order_notes table exists, create it if it doesn't
    $tableExists = $conn->query("SHOW TABLES LIKE 'order_notes'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the order_notes table
        $sql = "CREATE TABLE `order_notes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `admin_id` INT(11) NOT NULL,
            `note` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `order_id` (`order_id`),
            KEY `admin_id` (`admin_id`),
            CONSTRAINT `order_notes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `order_notes_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
    }
    
    // Insert the new note
    $stmt = $conn->prepare("
        INSERT INTO order_notes (order_id, admin_id, note, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->execute([$order_id, $admin_id, $note_content]);
    
    if ($stmt->rowCount() > 0) {
        $note_id = $conn->lastInsertId();
        
        // Get the newly created note with admin details
        $stmt = $conn->prepare("
            SELECT n.id, n.note, n.created_at, 
                   CONCAT(u.first_name, ' ', u.last_name) as admin_name
            FROM order_notes n
            JOIN users u ON n.admin_id = u.id
            WHERE n.id = ?
        ");
        $stmt->execute([$note_id]);
        $new_note = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['message'] = 'Note added successfully';
        $response['note'] = $new_note;
    } else {
        $response['message'] = 'Failed to add note';
    }
    
} catch (Exception $e) {
    error_log("Add order note error: " . $e->getMessage());
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?> 