<?php
$page_title = "Contact Messages History";
$page_description = "View history of contact form submissions";

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Redirect to login page with return URL
    setFlashMessage('error', 'Please log in to view contact history');
    header('Location: ' . SITE_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Only admins can view all messages
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Pagination settings
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = " WHERE (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

try {
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) FROM contact_messages" . $search_condition;
    $count_stmt = $conn->prepare($count_sql);
    
    if (!empty($search_params)) {
        $count_stmt->execute($search_params);
    } else {
        $count_stmt->execute();
    }
    
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get contact messages with pagination
    $sql = "SELECT id, name, email, subject, message, created_at 
            FROM contact_messages" . $search_condition . " 
            ORDER BY created_at DESC 
            LIMIT :offset, :records_per_page";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
    
    if (!empty($search_params)) {
        foreach ($search_params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
    }
    
    $stmt->execute();
    $contact_messages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching contact messages: " . $e->getMessage());
    $error_message = 'An error occurred while retrieving contact messages.';
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="bg-green-700 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Contact Messages History</h1>
            <p class="text-xl text-green-100">Review past inquiries and messages from our customers</p>
        </div>
    </div>
</section>

<!-- Messages List Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            
            <!-- Search and Filters -->
            <div class="mb-6 flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-lg shadow-sm">
                <div class="mb-4 md:mb-0">
                    <h2 class="text-xl font-semibold text-gray-800">Contact Messages</h2>
                    <p class="text-gray-500">Total: <?php echo $total_records; ?> messages</p>
                </div>
                
                <form action="" method="GET" class="flex w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search messages..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        <button type="submit" class="absolute right-0 top-0 h-full px-3 bg-green-600 text-white rounded-r-md hover:bg-green-700 transition-colors duration-300">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Messages Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <?php if (isset($error_message)): ?>
                    <div class="p-4 bg-red-100 text-red-700 border-l-4 border-red-500">
                        <?php echo $error_message; ?>
                    </div>
                <?php elseif (empty($contact_messages)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block opacity-30"></i>
                        <p>No messages found<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($contact_messages as $message): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y g:i A', strtotime($message['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($message['name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-blue-600 hover:underline">
                                                <?php echo htmlspecialchars($message['email']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 truncate max-w-xs">
                                            <?php echo htmlspecialchars($message['subject']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button 
                                            onclick="viewMessage(<?php echo $message['id']; ?>, '<?php echo addslashes(htmlspecialchars($message['name'])); ?>', '<?php echo addslashes(htmlspecialchars($message['email'])); ?>', '<?php echo addslashes(htmlspecialchars($message['subject'])); ?>', '<?php echo addslashes(htmlspecialchars($message['message'])); ?>', '<?php echo date('M d, Y g:i A', strtotime($message['created_at'])); ?>')"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing <?php echo min(($current_page - 1) * $records_per_page + 1, $total_records); ?> to 
                            <?php echo min($current_page * $records_per_page, $total_records); ?> of 
                            <?php echo $total_records; ?> results
                        </div>
                        
                        <div class="flex space-x-1">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 text-center">
                <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Contact Form
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Message View Modal -->
<div id="messageModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Message Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-500 mb-1">From</div>
                <div id="modalName" class="font-medium"></div>
                <div id="modalEmail" class="text-blue-600"></div>
            </div>
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Subject</div>
                <div id="modalSubject" class="font-medium"></div>
            </div>
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Date</div>
                <div id="modalDate" class="text-gray-600"></div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-500 mb-1">Message</div>
                <div id="modalMessage" class="bg-gray-50 p-4 rounded-md border border-gray-200 text-gray-800 whitespace-pre-wrap"></div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                Close
            </button>
            <?php if ($is_admin): ?>
            <a id="replyEmail" href="#" class="ml-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                <i class="fas fa-reply mr-1"></i> Reply
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include_once '../includes/footer.php'; ?>

<script>
function viewMessage(id, name, email, subject, message, date) {
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalEmail').textContent = email;
    document.getElementById('modalSubject').textContent = subject;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('modalDate').textContent = date;
    
    // Set reply email link only if the button exists (for admin users)
    const replyButton = document.getElementById('replyEmail');
    if (replyButton) {
        replyButton.href = 'mailto:' + email + '?subject=Re: ' + subject;
    }
    
    // Show modal
    document.getElementById('messageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Re-enable scrolling
}

// Close modal when clicking outside of it
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('messageModal').classList.contains('hidden')) {
        closeModal();
    }
});
</script> 