<?php
$page_title = "Checkout - AgroFarm";
$page_description = "Complete your purchase";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/cart_functions.php';
include_once '../includes/order_functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to checkout');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getCurrentUser();
$error_message = null;
$success_message = null;
$order_id = null;

// Get cart items
$cart_data = getCartItems($user_id);
$cart_items = $cart_data['items'];
$subtotal = 0;
$total_items = 0;

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    setFlashMessage('error', 'Your cart is empty');
    header('Location: ' . SITE_URL . '/user/cart.php');
    exit;
}

// Calculate totals
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

// Calculate taxes and shipping
$tax_rate = 0.05; // 5% tax
$tax = $subtotal * $tax_rate;

// Free shipping over $50, otherwise $5
$shipping = ($subtotal >= 50) ? 0 : 5;

$total = $subtotal + $tax + $shipping;

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate form fields
    $required_fields = [
        'shipping_address', 'shipping_phone', 'shipping_email', 'payment_method'
    ];
    
    $is_valid = true;
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error_message = "All fields are required";
            $is_valid = false;
            break;
        }
    }
    
    if ($is_valid) {
        // Create order
        $order_data = [
            'shipping_address' => $_POST['shipping_address'],
            'shipping_phone' => $_POST['shipping_phone'],
            'shipping_email' => $_POST['shipping_email'],
            'payment_method' => $_POST['payment_method'],
            'notes' => $_POST['notes'] ?? ''
        ];
        
        $result = createOrder($user_id, $order_data);
        
        if ($result['success']) {
            $success_message = $result['message'];
            $order_id = $result['order_id'];
        } else {
            $error_message = $result['message'];
        }
    }
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Checkout Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">Checkout</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Complete your order
        </p>
    </div>
</section>

<!-- Checkout Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message) && $order_id): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
                <p class="mt-2">Your order #<?php echo $order_id; ?> has been placed. Thank you for shopping with us!</p>
                <div class="mt-4">
                    <a href="<?php echo SITE_URL; ?>/user/orders.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                        View Orders
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Checkout Form -->
                <div class="lg:w-2/3">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="checkout-form">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                            <div class="px-6 py-4 bg-gray-50">
                                <h2 class="text-lg font-semibold">Shipping Information</h2>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Complete Address</label>
                                    <textarea name="shipping_address" id="shipping_address" rows="3" 
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Please include street, city, state, zip code and country</p>
                                </div>
                                <div>
                                    <label for="shipping_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="shipping_phone" id="shipping_phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required>
                                </div>
                                <div>
                                    <label for="shipping_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" name="shipping_email" id="shipping_email" 
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                            <div class="px-6 py-4 bg-gray-50">
                                <h2 class="text-lg font-semibold">Payment Method</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <input type="radio" name="payment_method" id="payment_cod" value="cod" 
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300" checked>
                                        <label for="payment_cod" class="ml-3 block text-sm font-medium text-gray-700">
                                            Cash on Delivery (COD)
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="payment_method" id="payment_bank" value="bank_transfer" 
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label for="payment_bank" class="ml-3 block text-sm font-medium text-gray-700">
                                            Bank Transfer
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="payment_method" id="payment_credit" value="credit_card" 
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                        <label for="payment_credit" class="ml-3 block text-sm font-medium text-gray-700">
                                            Credit Card
                                            <span class="text-xs text-gray-500 ml-1">(Coming Soon)</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional)</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flow-root">
                                <ul class="divide-y divide-gray-200">
                                    <?php foreach ($cart_items as $item): ?>
                                    <li class="py-3 flex">
                                        <div class="flex-shrink-0 h-16 w-16 overflow-hidden rounded">
                                            <img class="product-image h-full w-full object-cover" data-image="<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between text-sm font-medium text-gray-900">
                                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                                <p class="ml-4">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">Qty <?php echo $item['quantity']; ?></p>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="border-t border-gray-200 mt-4 pt-4">
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Subtotal (<?php echo $total_items; ?> items)</span>
                                    <span class="font-medium">$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Tax (5%)</span>
                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Shipping</span>
                                    <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                                </div>
                                <?php if ($shipping === 0): ?>
                                <div class="text-green-600 text-sm mt-2 mb-4">
                                    <i class="fas fa-check-circle mr-1"></i> Free shipping on orders over $50
                                </div>
                                <?php endif; ?>
                                <div class="flex justify-between py-2 border-t border-gray-200 mt-2">
                                    <span class="font-semibold">Total</span>
                                    <span class="font-semibold">$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" form="checkout-form" name="place_order" value="1" 
                                    class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md text-center transition duration-300">
                                Place Order
                            </button>
                            <a href="<?php echo SITE_URL; ?>/user/cart.php" class="block w-full text-center mt-4 text-green-600 hover:text-green-800">
                                Return to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Get image URL function
function getImageUrl(image) {
    if (!image) return '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
    
    // Check if image is a full URL
    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }
    
    // Check if image is already a relative path
    if (image.startsWith('/')) {
        return '<?php echo SITE_URL; ?>' + image;
    }
    
    // Otherwise, build the path to images directory
    return '<?php echo SITE_URL; ?>/assets/images/products/' + image;
}

document.addEventListener('DOMContentLoaded', function() {
    // Load product images
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(img => {
        const imagePath = img.getAttribute('data-image');
        img.setAttribute('src', getImageUrl(imagePath));
        
        // Handle image load errors
        img.onerror = function() {
            this.src = '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
        };
    });
    
    // Disable credit card option for now
    const creditOption = document.getElementById('payment_credit');
    if (creditOption) {
        creditOption.disabled = true;
    }
});
</script>

<?php include_once '../includes/footer.php'; ?> 