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
    $_SESSION['error'] = 'You must be logged in to submit a custom request.';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Submit Custom Request";
$errors = [];
$success = false;

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
    
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO custom_requests (
                        user_id, title, description, quantity, budget, additional_info, status, created_at, updated_at
                    ) VALUES (
                        :user_id, :title, :description, :quantity, :budget, :additional_info, 'pending', NOW(), NOW()
                    )";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':budget', $budget, PDO::PARAM_STR);
            $stmt->bindParam(':additional_info', $additional_info, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $request_id = $pdo->lastInsertId();
                $_SESSION['success'] = 'Your custom request has been submitted successfully.';
                header('Location: ' . SITE_URL . '/user/request-details.php?id=' . $request_id);
                exit;
            } else {
                $errors[] = 'Failed to submit your request. Please try again.';
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $errors[] = 'An error occurred while processing your request. Please try again later.';
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
                    <h1 class="h3 mb-0">Submit Custom Request</h1>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Can't find what you're looking for? Submit a custom request for products or services not available in our catalog.
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
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   required maxlength="100">
                            <div class="form-text">Brief title describing what you're looking for (max 100 characters)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <div class="form-text">Provide specific details about the product or service you're requesting</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '1'; ?>" 
                                       min="1" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="budget" class="form-label">Budget Per Unit (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="budget" name="budget" 
                                           value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>" 
                                           min="0" step="0.01">
                                </div>
                                <div class="form-text">Your estimated budget per unit (if applicable)</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="additional_info" class="form-label">Additional Information (Optional)</label>
                            <textarea class="form-control" id="additional_info" name="additional_info" rows="3"><?php echo isset($_POST['additional_info']) ? htmlspecialchars($_POST['additional_info']) : ''; ?></textarea>
                            <div class="form-text">Any other specifications, requirements, or questions</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/user/custom-requests.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Requests
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2 text-primary"></i>What Happens Next?</h5>
                    <div class="row g-4 mt-2">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fw-bold">1</div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6>Review</h6>
                                    <p class="text-muted small mb-0">Our team will review your request within 1-2 business days.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fw-bold">2</div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6>Communication</h6>
                                    <p class="text-muted small mb-0">We may contact you for clarification or additional details.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fw-bold">3</div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6>Solution</h6>
                                    <p class="text-muted small mb-0">We'll provide a quote or solution based on your requirements.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 