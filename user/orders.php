<?php
$page_title = "My Orders - AgroFarm";
$page_description = "View your order history";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: /AgroFarm/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user's orders
    $stmt = $conn->prepare("
        SELECT o.*, 
               COUNT(oi.id) as total_items,
               SUM(oi.quantity * oi.price) as total_amount
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    
    // For each order, fetch the products
    foreach ($orders as &$order) {
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name, p.image as product_image 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            LIMIT 3
        ");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
        
        // Get total number of products if more than shown
        if ($order['total_items'] > count($order['items'])) {
            $order['more_items'] = $order['total_items'] - count($order['items']);
        }
    }
    unset($order); // Break the reference

} catch (Exception $e) {
    error_log("Orders page error: " . $e->getMessage());
    $error_message = "An error occurred while loading your orders.";
    $orders = [];
}
?>

<!-- Orders Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">My Orders</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Track and manage your orders
        </p>
    </div>
</section>

<!-- Orders Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="text-center py-12">
                <i class="fas fa-shopping-bag text-6xl text-gray-400 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-600 mb-4">No orders yet</h2>
                <p class="text-gray-500 mb-8">Start shopping to see your orders here.</p>
                <a href="/AgroFarm/pages/shop.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Order #<?php echo $order['id']; ?></h3>
                                    <p class="text-gray-600">
                                        Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
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

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Items:</span>
                                    <span><?php echo $order['total_items']; ?></span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Total Amount:</span>
                                    <span class="font-semibold">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Payment Method:</span>
                                    <span><?php echo ucfirst($order['payment_method']); ?></span>
                                </div>
                            </div>

                            <!-- Product Preview Section -->
                            <?php if (!empty($order['items'])): ?>
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Products:</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    <?php foreach ($order['items'] as $item): ?>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-md object-cover product-image" 
                                                 data-image="<?php echo $item['product_image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                            <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (isset($order['more_items']) && $order['more_items'] > 0): ?>
                                    <div class="text-xs text-gray-500 mt-1">
                                        + <?php echo $order['more_items']; ?> more item(s)
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                   class="text-green-600 hover:text-green-700 font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>

<script>
function getImageUrl(imagePath) {
    // Check if path is absolute URL
    if (imagePath && (imagePath.startsWith('http://') || imagePath.startsWith('https://'))) {
        return imagePath;
    }
    
    // Check if path starts with /
    if (imagePath && imagePath.startsWith('/')) {
        return imagePath;
    }
    
    // Otherwise, assume relative path and add base URL
    const baseUrl = '<?php echo SITE_URL; ?>';
    return `${baseUrl}/assets/images/products/${imagePath}`;
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
</script> 