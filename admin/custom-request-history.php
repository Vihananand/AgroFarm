<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('/pages/login.php');
}

$page_title = "Custom Request History - Admin Dashboard";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = $status_filter ? "AND status = '$status_filter'" : "";

// Date range filter
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$date_condition = '';
if ($start_date && $end_date) {
    $date_condition = "AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
}

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = $search ? "AND (name LIKE '%$search%' OR email LIKE '%$search%' OR request_details LIKE '%$search%')" : "";

try {
    // Get total number of requests
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM custom_requests 
        WHERE 1=1 $status_condition $date_condition $search_condition
    ");
    $stmt->execute();
    $total_requests = $stmt->fetchColumn();
    $total_pages = ceil($total_requests / $per_page);

    // Get requests for current page
    $stmt = $conn->prepare("
        SELECT cr.*, u.first_name, u.last_name 
        FROM custom_requests cr
        LEFT JOIN users u ON cr.user_id = u.id
        WHERE 1=1 $status_condition $date_condition $search_condition
        ORDER BY cr.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll();

    // Get statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
            SUM(CASE WHEN status = 'in-review' THEN 1 ELSE 0 END) as in_review_requests,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests
        FROM custom_requests
        WHERE 1=1 $date_condition
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching custom requests: " . $e->getMessage());
    setFlashMessage('error', 'Error fetching custom requests');
    $requests = [];
    $total_pages = 0;
    $stats = [
        'total_requests' => 0,
        'pending_requests' => 0,
        'in_review_requests' => 0,
        'approved_requests' => 0,
        'rejected_requests' => 0
    ];
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Main Content -->
<main class="py-12">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Custom Request History</h1>
            <a href="/admin/custom-requests.php" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back to Active Requests
            </a>
        </div>

        <?php if ($flash = getFlashMessage()): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900">Total Requests</h3>
                <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_requests']; ?></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-yellow-800">Pending</h3>
                <p class="text-3xl font-bold text-yellow-800"><?php echo $stats['pending_requests']; ?></p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-blue-800">In Review</h3>
                <p class="text-3xl font-bold text-blue-800"><?php echo $stats['in_review_requests']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-green-800">Approved</h3>
                <p class="text-3xl font-bold text-green-800"><?php echo $stats['approved_requests']; ?></p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-red-800">Rejected</h3>
                <p class="text-3xl font-bold text-red-800"><?php echo $stats['rejected_requests']; ?></p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="mb-6 grid md:grid-cols-3 gap-4">
            <!-- Status Filter -->
            <form method="GET" class="flex items-center space-x-4">
                <label for="status" class="text-sm font-medium text-gray-700">Filter by Status:</label>
                <select id="status" name="status" class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">All Requests</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in-review" <?php echo $status_filter === 'in-review' ? 'selected' : ''; ?>>In Review</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                    Filter
                </button>
            </form>

            <!-- Date Range Filter -->
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="<?php echo $start_date; ?>" 
                           class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <span class="text-gray-500">to</span>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="<?php echo $end_date; ?>" 
                           class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <button type="submit" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                    Filter
                </button>
            </form>

            <!-- Search Form -->
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search requests</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name, email, or details" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <button type="submit" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                    Search
                </button>
                <?php if ($search || $status_filter || $start_date || $end_date): ?>
                    <a href="/admin/custom-request-history.php" class="text-blue-600 hover:text-blue-800">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No custom requests found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #<?php echo $request['id']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($request['name']); ?>
                                    </div>
                                    <?php if ($request['user_id']): ?>
                                        <div class="text-sm text-gray-500">
                                            Registered User
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($request['email']); ?>
                                    </div>
                                    <?php if ($request['phone']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($request['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($request['request_details']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch ($request['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'in-review':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button" 
                                            onclick="viewRequest(<?php echo $request['id']; ?>)"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $start_date ? '&start_date=' . $start_date : ''; ?><?php echo $end_date ? '&end_date=' . $end_date : ''; ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $start_date ? '&start_date=' . $start_date : ''; ?><?php echo $end_date ? '&end_date=' . $end_date : ''; ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $start_date ? '&start_date=' . $start_date : ''; ?><?php echo $end_date ? '&end_date=' . $end_date : ''; ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- View Request Modal -->
<div id="requestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Request Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="requestDetails" class="space-y-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Show modal
    document.getElementById('requestModal').classList.remove('hidden');
    
    // Fetch request details
    fetch(`/admin/ajax/get-request-details.php?id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                document.getElementById('requestDetails').innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-700">Customer Information</h4>
                            <p class="text-sm text-gray-600">Name: ${request.name}</p>
                            <p class="text-sm text-gray-600">Email: ${request.email}</p>
                            ${request.phone ? `<p class="text-sm text-gray-600">Phone: ${request.phone}</p>` : ''}
                            ${request.user_id ? `<p class="text-sm text-gray-600">Registered User: Yes</p>` : ''}
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700">Request Information</h4>
                            <p class="text-sm text-gray-600">Status: ${request.status}</p>
                            <p class="text-sm text-gray-600">Submitted: ${request.created_at}</p>
                            <p class="text-sm text-gray-600">Last Updated: ${request.updated_at}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">Request Details</h4>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">${request.request_details}</p>
                    </div>
                `;
            } else {
                document.getElementById('requestDetails').innerHTML = `
                    <div class="text-red-600">
                        Error loading request details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('requestDetails').innerHTML = `
                <div class="text-red-600">
                    Error loading request details: ${error.message}
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('requestModal').classList.add('hidden');
}
</script>

<?php include_once '../includes/footer.php'; ?> 