<?php
// Enable error reporting for debugging - REMOVE IN PRODUCTION
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = "Order Details - AgroFarm";
$page_description = "View details of your order";

// Include necessary files
include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/auth_functions.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store debug info
$_SESSION['debug_info'] = "Session ID: " . session_id() . 
                         " | User ID: " . ($_SESSION['user_id'] ?? 'none') . 
                         " | Logged in: " . (isLoggedIn() ? 'Yes' : 'No');

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to view order details";
    header("Location: " . SITE_URL . "/pages/login.php");
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: " . SITE_URL . "/user/orders.php");
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$_SESSION['debug_info'] .= " | Looking up Order ID: $order_id";

// Initialize variables
$order = null;
$order_items = [];
$order_notes = [];

try {
    // Check if orders table exists
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('orders', $tables)) {
        throw new Exception("Orders table does not exist in the database");
    }
    
    // Check order table structure
    $columns = $conn->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
    $_SESSION['debug_info'] .= " | Orders columns: " . implode(", ", $columns);
    
    if (!in_array('id', $columns) || !in_array('user_id', $columns)) {
        throw new Exception("Orders table is missing required columns");
    }
    
    // Try to get order directly by ID first
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $_SESSION['error'] = "Order #$order_id not found";
        header("Location: " . SITE_URL . "/user/orders.php");
        exit;
    }
    
    // Now verify ownership or admin rights
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
    if (!$is_admin && $order['user_id'] != $user_id) {
        $_SESSION['error'] = "You don't have permission to view this order";
        header("Location: " . SITE_URL . "/user/orders.php");
        exit;
    }
    
    // Get order items
    if (in_array('order_items', $tables)) {
        // Check if product table exists and has required columns
        if (in_array('products', $tables)) {
            try {
                $stmt = $conn->prepare("
                    SELECT oi.*, p.name as product_name, p.image as product_image, p.id as product_id
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If join fails, try fetching just order items
                $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt->execute([$order_id]);
                $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            // Just get order items without product details
            $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Get order notes if available
    if (in_array('order_notes', $tables)) {
        $stmt = $conn->prepare("SELECT * FROM order_notes WHERE order_id = ? ORDER BY created_at DESC");
        $stmt->execute([$order_id]);
        $order_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    error_log("Order details error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header("Location: " . SITE_URL . "/user/orders.php");
    exit;
}

// Include header and navbar
include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Order Details Header -->
<section class="bg-green-50 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Order #<?php echo $order['id']; ?></h1>
                <p class="text-gray-600">
                    Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                    <?php
                    switch ($order['status']) {
                        case 'pending':
                            echo 'bg-yellow-100 text-yellow-800';
                            break;
                        case 'processing':
                            echo 'bg-blue-100 text-blue-800';
                            break;
                        case 'shipped':
                            echo 'bg-purple-100 text-purple-800';
                            break;
                        case 'delivered':
                            echo 'bg-green-100 text-green-800';
                            break;
                        case 'cancelled':
                            echo 'bg-red-100 text-red-800';
                            break;
                        default:
                            echo 'bg-gray-100 text-gray-800';
                    }
                    ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Order Details Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold">Order Items</h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img data-image="<?php echo $item['product_image']; ?>" 
                                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                             class="h-10 w-10 rounded-md object-cover product-image">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a href="<?php echo SITE_URL; ?>/pages/product.php?id=<?php echo $item['product_id']; ?>" class="hover:text-green-600">
                                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                $<?php echo number_format($item['price'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                                <?php echo $item['quantity']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                            Subtotal:
                                        </td>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                            $<?php echo number_format($order['total_amount'] - ($order['shipping_cost'] ?? 0), 2); ?>
                                        </td>
                                    </tr>
                                    <?php if (isset($order['shipping_cost']) && $order['shipping_cost'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                            Shipping:
                                        </td>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                            $<?php echo number_format($order['shipping_cost'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                            <strong>Total:</strong>
                                        </td>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-green-600">
                                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Updates -->
                <?php if (!empty($order_notes)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold">Order Updates</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($order_notes as $note): ?>
                                <div class="border-l-4 
                                    <?php 
                                    switch($note['status']) {
                                        case 'shipped':
                                            echo 'border-blue-500';
                                            break;
                                        case 'delivered':
                                            echo 'border-green-500';
                                            break;
                                        case 'cancelled':
                                            echo 'border-red-500';
                                            break;
                                        default:
                                            echo 'border-gray-300';
                                    }
                                    ?> pl-4 py-2">
                                    <h3 class="text-md font-medium"><?php echo ucfirst($note['status']); ?></h3>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($note['note']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M d, Y h:i A', strtotime($note['created_at'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Information -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Order Actions -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold">Order Actions</h2>
                    </div>
                    <div class="p-6">
                        <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                            <div class="space-y-4">
                                <button type="button" onclick="cancelOrder(<?php echo $order['id']; ?>, false)" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md transition-colors">
                                    Cancel Order
                                </button>
                                
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <button type="button" onclick="cancelOrder(<?php echo $order['id']; ?>, true)" 
                                        class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md transition-colors">
                                    Delete Order Completely
                                </button>
                                <?php endif; ?>
                                
                                <div id="cancel-message" class="mt-2 text-sm text-gray-600 hidden">
                                    Cancelling an order will release the reserved items back to inventory.
                                </div>
                            </div>
                        <?php else: ?>
                            <button type="button" onclick="window.location.href='<?php echo SITE_URL; ?>/pages/shop.php'" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors">
                                Shop Again
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shipping Information -->
                <?php if (isset($order['shipping_address']) && !empty($order['shipping_address'])): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold">Shipping Information</h2>
                    </div>
                    <div class="p-6">
                        <h3 class="font-medium mb-2"><?php echo htmlspecialchars($order['shipping_name'] ?? ''); ?></h3>
                        <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></p>
                        <?php if (isset($order['shipping_city']) || isset($order['shipping_state']) || isset($order['shipping_zip'])): ?>
                        <p class="text-gray-600 mb-1">
                            <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>
                            <?php if (isset($order['shipping_city']) && isset($order['shipping_state'])): ?>, <?php endif; ?>
                            <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?> 
                            <?php echo htmlspecialchars($order['shipping_zip'] ?? ''); ?>
                        </p>
                        <?php endif; ?>
                        <?php if (isset($order['shipping_country']) && !empty($order['shipping_country'])): ?>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Information -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-xl font-semibold">Payment Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium"><?php echo ucfirst($order['payment_method'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Status:</span>
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                <?php
                                if (isset($order['payment_status'])) {
                                    switch ($order['payment_status']) {
                                        case 'paid':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'failed':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                } else {
                                    echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php echo ucfirst($order['payment_status'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function getImageUrl(imagePath) {
    // Check if path is absolute URL
    if (imagePath && (imagePath.startsWith('http://') || imagePath.startsWith('https://'))) {
        return imagePath;
    }
    
    // Check if path starts with /
    if (imagePath && imagePath.startsWith('/')) {
        return '<?php echo SITE_URL; ?>' + imagePath;
    }
    
    // Otherwise, assume relative path and add base URL
    return '<?php echo SITE_URL; ?>/assets/images/products/' + imagePath;
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
    
    // Show cancel message on hover
    const cancelButtons = document.querySelectorAll('button[onclick^="cancelOrder"]');
    const cancelMessage = document.getElementById('cancel-message');
    
    if (cancelButtons.length > 0 && cancelMessage) {
        cancelButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                cancelMessage.classList.remove('hidden');
            });
            
            button.addEventListener('mouseleave', function() {
                cancelMessage.classList.add('hidden');
            });
        });
    }
});

function cancelOrder(orderId, deleteCompletely) {
    let message = deleteCompletely ? 
        'Are you sure you want to completely delete this order? This cannot be undone.' : 
        'Are you sure you want to cancel this order? This action cannot be undone.';
    
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
        
        fetch('<?php echo SITE_URL; ?>/includes/ajax/cancel_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(data.message);
                window.location.reload();
            } else {
                // Show error message
                alert(data.message || 'Failed to cancel order');
                
                // Reset button state
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.innerHTML = deleteCompletely ? 'Delete Order Completely' : 'Cancel Order';
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
            
            // Reset button state
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = deleteCompletely ? 'Delete Order Completely' : 'Cancel Order';
            });
        });
    }
}
</script>

<?php include_once '../includes/footer.php'; ?> 