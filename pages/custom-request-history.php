<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Custom Request History";
$page_description = "View history of your custom product requests";

require_once '../includes/config.php';
require_once '../includes/db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Redirect to login page with return URL
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error'] = 'You must be logged in to view your request history.';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Check user role
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$user_id = $_SESSION['user_id'];

// Pagination settings
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Status filter
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$valid_statuses = ['pending', 'processing', 'completed', 'cancelled', 'rejected'];
if (!in_array($status_filter, $valid_statuses) && !empty($status_filter)) {
    $status_filter = '';
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

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

try {
    // Check if the table exists
    $table_exists = $pdo->query("SHOW TABLES LIKE 'custom_requests'")->rowCount() > 0;
    
    if (!$table_exists) {
        // Handle table doesn't exist case
        $_SESSION['error'] = 'The custom requests system is not set up yet. Please contact the administrator.';
        $custom_requests = [];
        $total_items = 0;
        $total_pages = 1;
    } else {
        // Build query parameters
        $sql_count = "SELECT COUNT(*) FROM custom_requests";
        $sql = "SELECT * FROM custom_requests";
        $where_conditions = [];
        $params = [];
        
        // Add user filter for non-admin users
        if (!$is_admin) {
            $where_conditions[] = "user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        // Add status filter if selected
        if (!empty($status_filter)) {
            $where_conditions[] = "status = :status";
            $params[':status'] = $status_filter;
        }
        
        // Add search if provided
        if (!empty($search)) {
            $where_conditions[] = "(title LIKE :search OR description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        // Combine where conditions
        if (!empty($where_conditions)) {
            $sql_count .= " WHERE " . implode(" AND ", $where_conditions);
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Add order and limit
        $sql .= " ORDER BY created_at DESC LIMIT :offset, :items_per_page";
        
        // Get total count for pagination
        $stmt = $pdo->prepare($sql_count);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total_items = $stmt->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);
        
        // Get the paginated results
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $custom_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while retrieving the requests. Please try again later.';
    $custom_requests = [];
    $total_items = 0;
    $total_pages = 1;
}

include_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-3"><?php echo $page_title; ?></h1>
            <p class="text-muted">
                <?php echo $page_description; ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?php echo SITE_URL; ?>/user/submit-request.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Request
            </a>
        </div>
    </div>
    
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
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_message']['message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search by title or description" 
                               name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-5">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($status_labels as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/custom-request-history.php" class="btn btn-outline-secondary w-100">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Requests List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($custom_requests)): ?>
                <div class="text-center py-5">
                    <div class="py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h2 class="h5">No custom requests found</h2>
                        <p class="text-muted mb-4">
                            <?php if (!empty($search) || !empty($status_filter)): ?>
                                Try clearing your filters or creating a new request.
                            <?php else: ?>
                                You haven't submitted any custom requests yet.
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo SITE_URL; ?>/user/submit-request.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New Request
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <?php if ($is_admin): ?>
                                <th>User</th>
                                <?php endif; ?>
                                <th width="30%">Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Last Update</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_requests as $request): ?>
                                <tr>
                                    <td>#<?php echo $request['id']; ?></td>
                                    <?php if ($is_admin): ?>
                                    <td>
                                        <?php 
                                            echo isset($request['name']) ? htmlspecialchars($request['name']) : 
                                                 'User ID: ' . $request['user_id']; 
                                        ?>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request['id']; ?>" class="fw-medium text-dark">
                                            <?php echo htmlspecialchars($request['title'] ?? $request['request_details']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $status_classes[$request['status']]; ?> py-2 px-3">
                                            <?php echo $status_labels[$request['status']]; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($request['updated_at'])); ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        
                                        <?php if ($is_admin): ?>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Update
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($valid_statuses as $status): ?>
                                                    <?php if ($status !== $request['status']): ?>
                                                    <li>
                                                        <a class="dropdown-item update-status" href="#" 
                                                           data-id="<?php echo $request['id']; ?>" 
                                                           data-status="<?php echo $status; ?>">
                                                            Mark as <?php echo $status_labels[$status]; ?>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php elseif ($request['status'] === 'pending'): ?>
                                            <a href="<?php echo SITE_URL; ?>/user/edit-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4 p-3 border-top">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for handling status updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status update
    const updateLinks = document.querySelectorAll('.update-status');
    updateLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            
            if (confirm(`Are you sure you want to update this request to "${status}"?`)) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo SITE_URL; ?>/includes/ajax/update_request_status.php';
                
                const idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'request_id';
                idField.value = id;
                
                const statusField = document.createElement('input');
                statusField.type = 'hidden';
                statusField.name = 'status';
                statusField.value = status;
                
                form.appendChild(idField);
                form.appendChild(statusField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?> 