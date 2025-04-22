<?php
require_once '../config.php';
require_once '../db.php';
require_once '../auth_functions.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
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

// Check if note_id is provided
if (!isset($_POST['note_id']) || empty($_POST['note_id'])) {
    $response['message'] = 'Note ID is required';
    echo json_encode($response);
    exit;
}

$note_id = intval($_POST['note_id']);

// Validate note_id
if ($note_id <= 0) {
    $response['message'] = 'Invalid note ID';
    echo json_encode($response);
    exit;
}

try {
    global $conn;
    
    // Check if note exists and belongs to an order
    $stmt = $conn->prepare("
        SELECT n.id, n.order_id 
        FROM order_notes n 
        WHERE n.id = ?
    ");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        $response['message'] = 'Note not found';
        echo json_encode($response);
        exit;
    }
    
    // Delete the note
    $stmt = $conn->prepare("DELETE FROM order_notes WHERE id = ?");
    $stmt->execute([$note_id]);
    
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Note deleted successfully';
    } else {
        $response['message'] = 'Failed to delete note';
    }
    
} catch (Exception $e) {
    error_log("Delete order note error: " . $e->getMessage());
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?> 