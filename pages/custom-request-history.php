<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Custom Request History";
$page_description = "View history of your custom product requests";

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

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
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
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
    $table_exists = $conn->query("SHOW TABLES LIKE 'custom_requests'")->rowCount() > 0;

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
        $stmt = $conn->prepare($sql_count);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total_items = $stmt->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);

        // Get the paginated results
        $stmt = $conn->prepare($sql);
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

<!-- Page Header -->
<div class="bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-3xl font-bold mb-2"><?php echo $page_title; ?></h1>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="<?php echo SITE_URL; ?>/user/dashboard.php"
                                class="text-gray-600 hover:text-green-600">Dashboard</a></li>
                        <li class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-700">Custom Requests</span>
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="<?php echo SITE_URL; ?>/user/submit-request.php"
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Request
            </a>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-4 border-l-4 border-green-500 bg-green-50 rounded-r-lg flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-green-700"><?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?></p>
            </div>
            <button type="button" class="text-green-600 hover:text-green-800" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 border-l-4 border-red-500 bg-red-50 rounded-r-lg flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-red-700"><?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?></p>
            </div>
            <button type="button" class="text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-4">
            <form action="" method="get" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search requests..."
                            class="form-input w-full pl-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                </div>
                <div class="flex-1">
                    <select name="status" onchange="this.form.submit()"
                        class="form-select w-full py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <?php foreach ($status_labels as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <div class="md:w-48">
                        <a href="<?php echo SITE_URL; ?>/pages/custom-request-history.php"
                            class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Requests List -->
    <div class="bg-white rounded-lg shadow-sm">
        <?php if (empty($custom_requests)): ?>
            <div class="py-16 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold mb-2">No Custom Requests Found</h2>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        No requests match your current filters. Try adjusting your search criteria or clearing the filters.
                    <?php else: ?>
                        You haven't submitted any custom requests yet. Create your first request to get started.
                    <?php endif; ?>
                </p>
                <div class="flex gap-3 justify-center">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/custom-request-history.php"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear Filters
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/user/submit-request.php"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New Request
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto h-[40vh]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                            </th>
                            <?php if ($is_admin): ?>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                                </th>
                            <?php endif; ?>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                                Update</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($custom_requests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?php echo $request['id']; ?>
                                </td>
                                <?php if ($is_admin): ?>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo isset($request['name']) ? htmlspecialchars($request['name']) : 'User ID: ' . $request['user_id']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4">
                                    <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request['id']; ?>"
                                        class="text-sm font-medium text-gray-900 hover:text-green-600">
                                        <?php echo htmlspecialchars($request['title'] ?? $request['request_details']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <?php echo date('M j, Y', strtotime($request['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_styles = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'rejected' => 'bg-red-100 text-red-800'
                                    ];
                                    $status_icons = [
                                        'pending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                                        'processing' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
                                        'completed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                                        'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                                        'rejected' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />'
                                    ];
                                    ?>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_styles[$request['status']]; ?>">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php echo $status_icons[$request['status']]; ?>
                                        </svg>
                                        <?php echo $status_labels[$request['status']]; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <?php echo date('M j, Y', strtotime($request['updated_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="<?php echo SITE_URL; ?>/user/request-details.php?id=<?php echo $request['id']; ?>"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>

                                        <?php if ($is_admin): ?>
                                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                                <button type="button" @click="open = !open"
                                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Update
                                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false"
                                                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none">
                                                    <div class="py-1">
                                                        <?php foreach ($valid_statuses as $status): ?>
                                                            <?php if ($status !== $request['status']): ?>
                                                                <a href="#"
                                                                    class="update-status group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                                    data-id="<?php echo $request['id']; ?>"
                                                                    data-status="<?php echo $status; ?>">
                                                                    <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500"
                                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <?php echo $status_icons[$status]; ?>
                                                                    </svg>
                                                                    Mark as <?php echo $status_labels[$status]; ?>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($request['status'] === 'pending'): ?>
                                            <a href="<?php echo SITE_URL; ?>/user/edit-request.php?id=<?php echo $request['id']; ?>"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="border-t border-gray-200 px-4 py-3 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                            class="<?php echo ($page <= 1) ? 'opacity-50 cursor-not-allowed' : ''; ?> relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                            class="<?php echo ($page >= $total_pages) ? 'opacity-50 cursor-not-allowed' : ''; ?> ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo ($offset + 1); ?></span> to
                                <span class="font-medium"><?php echo min($offset + $items_per_page, $total_items); ?></span> of
                                <span class="font-medium"><?php echo $total_items; ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="?page=1&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                                    class="<?php echo ($page <= 1) ? 'opacity-50 cursor-not-allowed' : ''; ?> relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">First</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M15.707 15.707a1 1 0 01-1.414 0L9 10.414V13a1 1 0 11-2 0V7a1 1 0 011-1h6a1 1 0 110 2h-2.586l5.293 5.293a1 1 0 010 1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>

                                <?php if ($start_page > 1): ?>
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium <?php echo $i === $page ? 'z-10 bg-green-50 border-green-500 text-green-600' : 'bg-white text-gray-500 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                <?php endif; ?>

                                <a href="?page=<?php echo $total_pages; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                                    class="<?php echo ($page >= $total_pages) ? 'opacity-50 cursor-not-allowed' : ''; ?> relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Last</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 15.707a1 1 0 001.414 0L11 10.414V13a1 1 0 102 0V7a1 1 0 00-1-1H6a1 1 0 100 2h2.586l-5.293 5.293a1 1 0 000 1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </nav>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for handling status updates -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle status update
        const updateLinks = document.querySelectorAll('.update-status');
        updateLinks.forEach(link => {
            link.addEventListener('click', function (e) {
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