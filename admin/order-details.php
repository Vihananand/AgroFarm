<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    setFlashMessage('error', 'Order ID is required');
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

try {
    // Get order details with customer information
    $query = "SELECT o.*, u.email, u.first_name, u.last_name, u.phone
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              WHERE o.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        setFlashMessage('error', 'Order not found');
        header('Location: orders.php');
        exit;
    }

    // Get order items with product details
    $query = "SELECT oi.*, p.name as product_name, p.image as product_image
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error fetching order details: " . $e->getMessage());
    setFlashMessage('error', 'Error fetching order details');
    header('Location: orders.php');
    exit;
}

$page_title = "Order Details - Admin Dashboard";
include_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Order Details #<?php echo $order_id; ?></h1>
                <a href="orders.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Back to Orders
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Information</h3>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Full name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email address</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($order['email']); ?>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Phone number</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo htmlspecialchars($order['phone']); ?>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Order status</dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            <form action="orders.php" method="POST" class="inline-block">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" 
                                        class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 rounded-md
                                        <?php
                                        switch($order['status']) {
                                            case 'pending':
                                                echo 'text-yellow-800 bg-yellow-100 border-yellow-200';
                                                break;
                                            case 'processing':
                                                echo 'text-blue-800 bg-blue-100 border-blue-200';
                                                break;
                                            case 'completed':
                                                echo 'text-green-800 bg-green-100 border-green-200';
                                                break;
                                            case 'cancelled':
                                                echo 'text-red-800 bg-red-100 border-red-200';
                                                break;
                                            default:
                                                echo 'text-gray-800 bg-gray-100 border-gray-200';
                                        }
                                        ?>">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Order date</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Order Items -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Items</h3>
                </div>
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover product-image" 
                                                     data-image="uploads/products/<?php echo $item['product_image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        $<?php echo number_format($item['quantity'] * $item['price'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    Total Amount:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    $<?php echo number_format($order['total_amount'], 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Actions -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Actions</h3>
                </div>
                <div class="border-t border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if ($order['status'] !== 'cancelled'): ?>
                            <button type="button" onclick="cancelOrder(<?php echo $order['id']; ?>, false)" 
                                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-md transition-colors">
                                Mark as Cancelled
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" onclick="cancelOrder(<?php echo $order['id']; ?>, true)" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md transition-colors">
                            Delete Order Completely
                        </button>
                        
                        <button type="button" onclick="addOrderNote(<?php echo $order['id']; ?>)" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors">
                            Add Note
                        </button>
                        
                        <button type="button" onclick="window.location.href='orders.php'" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md transition-colors">
                            Back to Orders
                        </button>
                    </div>
                    
                    <div id="note-form" class="mt-4 hidden">
                        <div class="border border-gray-300 rounded-md p-4">
                            <h4 class="text-md font-medium mb-2">Add Note to Order</h4>
                            <textarea id="order-note" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                            <div class="mt-2 flex justify-end">
                                <button type="button" onclick="submitNote(<?php echo $order['id']; ?>)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded-md transition-colors text-sm">
                                    Save Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>

<script>
function getImageUrl(imagePath) {
    // Check if path is absolute URL
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // Check if path starts with /
    if (imagePath.startsWith('/')) {
        return imagePath;
    }
    
    // Otherwise, assume relative path and add base URL
    const baseUrl = '<?php echo SITE_URL; ?>';
    return `${baseUrl}/${imagePath}`;
}

// Initialize all product images
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-image').forEach(img => {
        const imagePath = img.getAttribute('data-image');
        const imgUrl = getImageUrl(imagePath);
        
        img.src = imgUrl;
        img.onerror = function() {
            this.src = '<?php echo SITE_URL; ?>/assets/images/default-product.jpg';
        };
    });
});

function cancelOrder(orderId, deleteCompletely) {
    let message = deleteCompletely ? 
        'Are you sure you want to completely delete this order? This action cannot be undone and will remove all order records from the database.' : 
        'Are you sure you want to mark this order as cancelled? This will restore the product stock but keep the order record.';
    
    if (confirm(message)) {
        // Show loading state
        const buttons = document.querySelectorAll('button[onclick^="cancelOrder"]');
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Processing...';
        });
        
        const formData = new FormData();
        formData.append('order_id', orderId);
        if (deleteCompletely) {
            formData.append('delete_completely', 'true');
        }
        formData.append('admin_action', 'true');
        
        fetch('<?php echo SITE_URL; ?>/includes/ajax/cancel_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (deleteCompletely) {
                    window.location.href = 'orders.php';
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Failed to process order action');
                
                // Reset button state
                buttons.forEach(btn => {
                    const originalText = deleteCompletely ? 'Delete Order Completely' : 'Mark as Cancelled';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
            
            // Reset button state
            buttons.forEach(btn => {
                const originalText = deleteCompletely ? 'Delete Order Completely' : 'Mark as Cancelled';
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }
}

function addOrderNote(orderId) {
    const noteForm = document.getElementById('note-form');
    noteForm.classList.toggle('hidden');
}

function submitNote(orderId) {
    const noteText = document.getElementById('order-note').value.trim();
    
    if (!noteText) {
        alert('Please enter a note');
        return;
    }
    
    const saveButton = document.querySelector('button[onclick^="submitNote"]');
    saveButton.disabled = true;
    saveButton.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Saving...';
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('note', noteText);
    
    // Ensure we're using the absolute URL with SITE_URL
    const url = '<?php echo SITE_URL; ?>/includes/ajax/add_order_note.php';
    console.log('Submitting to URL:', url); // Add debugging
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Note added successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to add note');
            saveButton.disabled = false;
            saveButton.innerHTML = 'Save Note';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the note');
        saveButton.disabled = false;
        saveButton.innerHTML = 'Save Note';
    });
}
</script> 