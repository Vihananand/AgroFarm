<?php
// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration and database connection
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error'] = 'You must be logged in to edit your custom request.';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Edit Custom Request";
$errors = [];

// Check if request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request ID.';
    header('Location: ' . SITE_URL . '/user/custom-requests.php');
    exit;
}

$request_id = (int)$_GET['id'];

// Fetch the request details and ensure it belongs to the current user and is in 'pending' status
try {
    $stmt = $pdo->prepare("
        SELECT * FROM custom_requests 
        WHERE id = :id AND user_id = :user_id 
        LIMIT 1
    ");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        $_SESSION['error'] = 'Request not found or you do not have permission to edit it.';
        header('Location: ' . SITE_URL . '/user/custom-requests.php');
        exit;
    }
    
    // Only allow editing of pending requests
    if ($request['status'] !== 'pending') {
        $_SESSION['error'] = 'Only pending requests can be edited.';
        header('Location: ' . SITE_URL . '/user/request-details.php?id=' . $request_id);
        exit;
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while retrieving the request details. Please try again later.';
    header('Location: ' . SITE_URL . '/user/custom-requests.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $budget = isset($_POST['budget']) ? (float)$_POST['budget'] : 0;
    $additional_info = trim($_POST['additional_info'] ?? '');
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 100) {
        $errors[] = 'Title cannot exceed 100 characters';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if ($quantity <= 0) {
        $errors[] = 'Quantity must be greater than zero';
    }
    
    // If no errors, proceed with database update
    if (empty($errors)) {
        try {
            $sql = "UPDATE custom_requests SET 
                    title = :title,
                    description = :description,
                    quantity = :quantity,
                    budget = :budget,
                    additional_info = :additional_info,
                    updated_at = NOW()
                WHERE id = :id AND user_id = :user_id AND status = 'pending'";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':budget', $budget, PDO::PARAM_STR);
            $stmt->bindParam(':additional_info', $additional_info, PDO::PARAM_STR);
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Your custom request has been updated successfully.';
                header('Location: ' . SITE_URL . '/user/request-details.php?id=' . $request_id);
                exit;
            } else {
                $errors[] = 'Failed to update your request. Please try again.';
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $errors[] = 'An error occurred while updating your request. Please try again later.';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h1 class="h3 mb-0">Edit Custom Request</h1>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Update the details of your custom request below. Only pending requests can be edited.
                    </p>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Request Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($request['title']); ?>" 
                                   required maxlength="100">
                            <div class="form-text">Brief title describing what you're looking for (max 100 characters)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($request['description']); ?></textarea>
                            <div class="form-text">Provide specific details about the product or service you're requesting</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : htmlspecialchars($request['quantity']); ?>" 
                                       min="1" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="budget" class="form-label">Budget Per Unit (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="budget" name="budget" 
                                           value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : htmlspecialchars($request['budget']); ?>" 
                                           min="0" step="0.01">
                                </div>
                                <div class="form-text">Your estimated budget per unit (if applicable)</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="additional_info" class="form-label">Additional Information (Optional)</label>
                            <textarea class="form-control" id="additional_info" name="additional_info" rows="3"><?php echo isset($_POST['additional_info']) ? htmlspecialchars($_POST['additional_info']) : htmlspecialchars($request['additional_info']); ?></textarea>
                            <div class="form-text">Any other specifications, requirements, or questions</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 