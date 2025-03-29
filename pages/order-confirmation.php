<?php
$page_title = "Order Confirmation";
$page_description = "Your order has been successfully placed.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/pages/login.php');
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    redirect(SITE_URL . '/pages/account.php');
}

$order_id = (int)$_GET['order_id'];

// Get order details
try {
    $stmt = $conn->prepare("
        SELECT o.*, u.first_name, u.last_name, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, getUserId()]);
    $order = $stmt->fetch();
    
    // If order not found or doesn't belong to the current user, redirect
    if (!$order) {
        redirect(SITE_URL . '/pages/account.php');
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.slug, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    // Calculate order totals
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($order_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }
    
    // Extract shipping address components
    $address_parts = explode(',', $order['shipping_address']);
    $shipping_address = trim($address_parts[0] ?? '');
    $shipping_city = trim($address_parts[1] ?? '');
    $state_zip = explode(' ', trim($address_parts[2] ?? ''));
    $shipping_state = trim($state_zip[0] ?? '');
    $shipping_zip = trim($state_zip[1] ?? '');
    $shipping_country = trim($address_parts[3] ?? '');
    
} catch (PDOException $e) {
    redirect(SITE_URL . '/pages/account.php');
}

// Format order date
$order_date = new DateTime($order['created_at']);
$formatted_date = $order_date->format('F j, Y');

// Estimated delivery date (5-7 business days)
$delivery_date = clone $order_date;
$delivery_date->modify('+7 days');
$formatted_delivery_date = $delivery_date->format('F j, Y');
?>

<!-- Order Confirmation Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
            </div>
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-4">Thank You for Your Order!</h1>
                <p class="text-gray-600">
                    Your order #<?php echo $order_id; ?> has been placed successfully. 
                    A confirmation email has been sent to <?php echo $order['email']; ?>.
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold">Order #<?php echo $order_id; ?></h2>
                            <p class="text-gray-500">Placed on <?php echo $formatted_date; ?></p>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                <?php 
                                $status_color = 'bg-gray-100 text-gray-800';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_color = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'processing':
                                        $status_color = 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'shipped':
                                        $status_color = 'bg-purple-100 text-purple-800';
                                        break;
                                    case 'delivered':
                                        $status_color = 'bg-green-100 text-green-800';
                                        break;
                                    case 'cancelled':
                                        $status_color = 'bg-red-100 text-red-800';
                                        break;
                                }
                                echo $status_color;
                                ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="border-t border-b py-4 mb-6">
                        <h3 class="font-semibold mb-3">Order Items</h3>
                        <div class="space-y-4">
                            <?php foreach ($order_items as $item): ?>
                            <div class="flex items-start">
                                <div class="w-16 h-16 flex-shrink-0 mr-3">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                         alt="<?php echo $item['name']; ?>" 
                                         class="w-full h-full object-cover rounded">
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-medium">
                                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $item['slug']; ?>" class="hover:text-green-600 transition-colors">
                                            <?php echo $item['name']; ?>
                                        </a>
                                    </h4>
                                    <div class="flex justify-between mt-1">
                                        <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="font-medium">
                                            <?php echo '$' . number_format($item['price'] * $item['quantity'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold mb-3">Shipping Information</h3>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="font-medium"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                                <p><?php echo $shipping_address; ?></p>
                                <p><?php echo $shipping_city . ', ' . $shipping_state . ' ' . $shipping_zip; ?></p>
                                <p><?php echo $shipping_country; ?></p>
                                <p class="mt-2"><?php echo $order['shipping_phone']; ?></p>
                                <p><?php echo $order['shipping_email']; ?></p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="font-semibold mb-3">Payment Information</h3>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="font-medium">Payment Method:</p>
                                <p class="mb-3">
                                    <?php 
                                    switch ($order['payment_method']) {
                                        case 'credit_card':
                                            echo 'Credit Card';
                                            break;
                                        case 'paypal':
                                            echo 'PayPal';
                                            break;
                                        case 'bank_transfer':
                                            echo 'Bank Transfer';
                                            break;
                                        default:
                                            echo ucfirst($order['payment_method']);
                                    }
                                    ?>
                                </p>
                                
                                <p class="font-medium">Payment Status:</p>
                                <p class="
                                    <?php 
                                    $payment_color = 'text-gray-800';
                                    switch ($order['payment_status']) {
                                        case 'pending':
                                            $payment_color = 'text-yellow-600';
                                            break;
                                        case 'paid':
                                            $payment_color = 'text-green-600';
                                            break;
                                        case 'failed':
                                            $payment_color = 'text-red-600';
                                            break;
                                    }
                                    echo $payment_color;
                                    ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="font-semibold mb-3">Order Summary</h3>
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal (<?php echo $total_items; ?> items)</span>
                                    <span><?php echo '$' . number_format($subtotal, 2); ?></span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Shipping</span>
                                    <?php 
                                    $shipping_cost = $order['total_amount'] - $subtotal - ($subtotal * 0.08);
                                    if ($shipping_cost <= 0): 
                                    ?>
                                    <span class="text-green-600">Free</span>
                                    <?php else: ?>
                                    <span><?php echo '$' . number_format($shipping_cost, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax (8%)</span>
                                    <span><?php echo '$' . number_format($subtotal * 0.08, 2); ?></span>
                                </div>
                                
                                <div class="flex justify-between pt-2 border-t text-base font-bold">
                                    <span>Total</span>
                                    <span><?php echo '$' . number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Delivery Information</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="flex items-start">
                                <i class="fas fa-truck-moving mt-1 mr-3 text-green-600 text-lg"></i>
                                <div>
                                    <h4 class="font-medium">Estimated Delivery Date</h4>
                                    <p class="text-gray-600"><?php echo $formatted_delivery_date; ?></p>
                                    <p class="text-sm text-gray-500 mt-1">5-7 business days from order date</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="flex items-start">
                                <i class="fas fa-box mt-1 mr-3 text-green-600 text-lg"></i>
                                <div>
                                    <h4 class="font-medium">Shipping Method</h4>
                                    <p class="text-gray-600">Standard Shipping</p>
                                    <?php if ($shipping_cost <= 0): ?>
                                    <p class="text-sm text-green-600 mt-1">Free shipping applied</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center space-y-4">
                <p class="text-gray-600">
                    If you have any questions about your order, please contact our customer service.
                </p>
                
                <div class="space-x-4">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-secondary inline-block">
                        Continue Shopping
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/account.php?tab=orders" class="btn-primary inline-block">
                        View My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?> 