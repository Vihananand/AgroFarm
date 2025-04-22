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
    $_SESSION['error'] = 'You must be logged in to view custom requests.';
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "My Custom Requests";

// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query with filters
$sql_count = "SELECT COUNT(*) FROM custom_requests WHERE user_id = :user_id";
$sql = "
    SELECT * FROM custom_requests 
    WHERE user_id = :user_id
";

$params = [':user_id' => $user_id];

// Add status filter if provided
if (!empty($status_filter)) {
    $sql .= " AND status = :status";
    $sql_count .= " AND status = :status";
    $params[':status'] = $status_filter;
}

// Add search if provided
if (!empty($search)) {
    $sql .= " AND (title LIKE :search OR description LIKE :search)";
    $sql_count .= " AND (title LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

// Complete the queries
$sql .= " ORDER BY created_at DESC LIMIT :offset, :items_per_page";

// Get total records count for pagination
try {
    $stmt = $pdo->prepare($sql_count);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_items = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $total_items = 0;
}

$total_pages = ceil($total_items / $items_per_page);

// Get requests
try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $requests = [];
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
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-3">My Custom Requests</h1>
            <p class="text-muted">
                View and manage your custom product or service requests.
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
                        <a href="<?php echo SITE_URL; ?>/user/custom-requests.php" class="btn btn-outline-secondary w-100">
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
            <?php if (empty($requests)): ?>
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
                                <th width="30%">Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Last Update</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?php echo $request['id']; ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request['id']; ?>" class="fw-medium text-dark">
                                            <?php echo htmlspecialchars($request['title']); ?>
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
                                        <?php if ($request['status'] === 'pending'): ?>
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

<?php include_once '../includes/footer.php'; ?> 