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
    $_SESSION['error'] = 'You must be logged in to view request details.';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Request Details";
$error_message = null;

// Check if request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request ID.';
    header('Location: ' . SITE_URL . '/user/custom-requests.php');
    exit;
}

$request_id = (int)$_GET['id'];

// Fetch the request details and ensure it belongs to the current user
try {
    $stmt = $pdo->prepare("
        SELECT cr.*, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email AS customer_email
        FROM custom_requests cr
        JOIN users u ON cr.user_id = u.id
        WHERE cr.id = :id AND cr.user_id = :user_id
        LIMIT 1
    ");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        $_SESSION['error'] = 'Request not found or you do not have permission to view it.';
        header('Location: ' . SITE_URL . '/user/custom-requests.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while retrieving the request details. Please try again later.';
    header('Location: ' . SITE_URL . '/user/custom-requests.php');
    exit;
}

// Process comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    
    if (empty($comment)) {
        $error_message = 'Comment cannot be empty.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO request_comments (request_id, user_id, comment, created_at)
                VALUES (:request_id, :user_id, :comment, NOW())
            ");
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Update the request's updated_at timestamp
                $stmt = $pdo->prepare("
                    UPDATE custom_requests
                    SET updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Redirect to remove the form submission
                header('Location: ' . SITE_URL . '/user/request-details.php?id=' . $request_id . '&comment_added=1');
                exit;
            } else {
                $error_message = 'Failed to add comment. Please try again.';
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $error_message = 'An error occurred while adding your comment. Please try again later.';
        }
    }
}

// Fetch comments for this request
try {
    $stmt = $pdo->prepare("
        SELECT rc.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email, u.role
        FROM request_comments rc
        JOIN users u ON rc.user_id = u.id
        WHERE rc.request_id = :request_id
        ORDER BY rc.created_at ASC
    ");
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $comments = [];
}

// Define status labels and classes
$status_labels = [
    'pending' => 'Pending Review',
    'processing' => 'In Progress',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    'rejected' => 'Rejected'
];

$status_classes = [
    'pending' => 'bg-warning text-dark',
    'processing' => 'bg-info text-white',
    'completed' => 'bg-success text-white',
    'cancelled' => 'bg-danger text-white',
    'rejected' => 'bg-danger text-white'
];

// Include header
include_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Alerts -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['comment_added']) && $_GET['comment_added'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Your comment has been added successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Request Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Request #<?php echo $request['id']; ?></h1>
                    <span class="badge <?php echo $status_classes[$request['status']]; ?> py-2 px-3">
                        <?php echo $status_labels[$request['status']]; ?>
                    </span>
                </div>
                <div class="card-body">
                    <h2 class="h4 mb-3"><?php echo htmlspecialchars($request['title']); ?></h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Submitted:</strong> <?php echo date('F j, Y, g:i a', strtotime($request['created_at'])); ?></p>
                            <p class="mb-0"><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($request['updated_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="h5 mb-2">Description</h3>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($request['description'])); ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3 class="h5 mb-2">Quantity</h3>
                            <p><?php echo $request['quantity']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h5 mb-2">Budget Per Unit</h3>
                            <p><?php echo $request['budget'] ? '$' . number_format($request['budget'], 2) : 'Not specified'; ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($request['additional_info'])): ?>
                        <div class="mb-4">
                            <h3 class="h5 mb-2">Additional Information</h3>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($request['additional_info'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Action buttons -->
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <a href="<?php echo SITE_URL; ?>/user/custom-requests.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Requests
                        </a>
                        
                        <div>
                            <?php if ($request['status'] === 'pending'): ?>
                                <a href="<?php echo SITE_URL; ?>/user/edit-request.php?id=<?php echo $request['id']; ?>" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Request
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($request['status'] === 'pending' || $request['status'] === 'processing'): ?>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                                    <i class="fas fa-times me-1"></i> Cancel Request
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h4 mb-0">Communication</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments text-muted fa-3x mb-3"></i>
                            <h3 class="h5">No comments yet</h3>
                            <p class="text-muted">Start the conversation by adding a comment below.</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-3 pb-3 <?php echo ($comment['user_id'] == $user_id) ? 'border-bottom' : 'border-bottom border-light'; ?>">
                                    <div class="flex-shrink-0">
                                        <div class="<?php echo ($comment['role'] === 'admin') ? 'bg-primary' : 'bg-light'; ?> text-<?php echo ($comment['role'] === 'admin') ? 'white' : 'dark'; ?> rounded-circle p-2 text-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-<?php echo ($comment['role'] === 'admin') ? 'user-tie' : 'user'; ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h4 class="h6 mb-0">
                                                <?php echo htmlspecialchars($comment['user_name']); ?>
                                                <?php if ($comment['role'] === 'admin'): ?>
                                                    <span class="badge bg-primary ms-1">Staff</span>
                                                <?php endif; ?>
                                            </h4>
                                            <small class="text-muted"><?php echo date('M j, Y, g:i a', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($request['status'] !== 'cancelled' && $request['status'] !== 'rejected'): ?>
                        <!-- Add comment form -->
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="comment" class="form-label">Add a comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i> Your comment will be visible to our staff and can help clarify your request.
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Comment
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-1"></i> This request is <?php echo strtolower($status_labels[$request['status']]); ?>, so you cannot add new comments.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this request? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <small><i class="fas fa-exclamation-triangle me-1"></i> If your request is already in processing status, our team may have already started working on it.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                <button type="button" id="confirmCancelBtn" class="btn btn-danger" data-request-id="<?php echo $request['id']; ?>">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Yes, Cancel Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle cancel request
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            const spinner = this.querySelector('.spinner-border');
            const originalText = this.innerHTML;
            
            // Disable button and show spinner
            this.disabled = true;
            spinner.classList.remove('d-none');
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('request_id', requestId);
            
            fetch('<?php echo SITE_URL; ?>/includes/ajax/cancel_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alertElement = document.createElement('div');
                    alertElement.className = 'alert alert-success';
                    alertElement.innerHTML = data.message;
                    document.querySelector('.modal-body').appendChild(alertElement);
                    
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '<?php echo SITE_URL; ?>/user/custom-requests.php';
                    }, 1500);
                } else {
                    // Show error message
                    const alertElement = document.createElement('div');
                    alertElement.className = 'alert alert-danger';
                    alertElement.innerHTML = data.message;
                    document.querySelector('.modal-body').appendChild(alertElement);
                    
                    // Re-enable button
                    this.disabled = false;
                    spinner.classList.add('d-none');
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show error message
                const alertElement = document.createElement('div');
                alertElement.className = 'alert alert-danger';
                alertElement.innerHTML = 'An error occurred while processing your request. Please try again.';
                document.querySelector('.modal-body').appendChild(alertElement);
                
                // Re-enable button
                this.disabled = false;
                spinner.classList.add('d-none');
                this.innerHTML = originalText;
            });
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?> 