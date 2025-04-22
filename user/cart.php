<?php
$page_title = "Shopping Cart - AgroFarm";
$page_description = "Your Shopping Cart";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your cart');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = null;
$success_message = null;

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        try {
            foreach ($_POST['quantity'] as $item_id => $quantity) {
                $quantity = (int) $quantity;
                if ($quantity <= 0) {
                    // Remove item if quantity is 0 or negative
                    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                    $stmt->execute([$item_id, $user_id]);
                } else {
                    // Update quantity
                    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$quantity, $item_id, $user_id]);
                }
            }
            $success_message = "Cart updated successfully";
        } catch (PDOException $e) {
            error_log("Cart update error: " . $e->getMessage());
            $error_message = "Failed to update cart";
        }
    } elseif (isset($_POST['remove_item']) && isset($_POST['item_id'])) {
        try {
            $item_id = $_POST['item_id'];
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$item_id, $user_id]);
            $success_message = "Item removed from cart";
        } catch (PDOException $e) {
            error_log("Cart remove error: " . $e->getMessage());
            $error_message = "Failed to remove item";
        }
    }
}

// Get cart items
try {
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, p.stock, p.id as product_id
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    // Calculate totals
    $subtotal = 0;
    $total_items = 0;
    
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
    
} catch (PDOException $e) {
    error_log("Cart error: " . $e->getMessage());
    $error_message = "An error occurred while loading your cart";
    $cart_items = [];
    $subtotal = $tax = $shipping = $total = 0;
    $total_items = 0;
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Cart Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">Shopping Cart</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Review your items and proceed to checkout
        </p>
    </div>
</section>

<!-- Cart Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="text-2xl font-semibold mb-4">Your cart is empty</h2>
                <p class="text-gray-600 mb-8">Looks like you haven't added any products to your cart yet.</p>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md transition duration-300">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-16 w-16">
                                                    <img class="product-image h-16 w-16 object-cover rounded" 
                                                         src="#" 
                                                         data-image="<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($item['name']); ?>
                                                    </div>
                                                    <?php if ($item['stock'] < 5): ?>
                                                    <div class="text-xs text-red-500">
                                                        Only <?php echo $item['stock']; ?> left in stock
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">$<?php echo number_format($item['price'], 2); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" 
                                                min="0" max="<?php echo $item['stock']; ?>" 
                                                class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="submit" name="remove_item" value="1" 
                                                    onclick="document.getElementById('item_id').value='<?php echo $item['id']; ?>'"
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <input type="hidden" id="item_id" name="item_id" value="">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="px-6 py-4 bg-gray-50">
                                <button type="submit" name="update_cart" value="1" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                    Update Cart
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                        <div class="border-t border-gray-200 pt-4">
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
                            <?php else: ?>
                            <div class="text-gray-500 text-sm mt-2 mb-4">
                                Add $<?php echo number_format(50 - $subtotal, 2); ?> more for free shipping
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between py-2 border-t border-gray-200 mt-2">
                                <span class="font-semibold">Total</span>
                                <span class="font-semibold">$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="<?php echo SITE_URL; ?>/user/checkout.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md text-center transition duration-300">
                                Proceed to Checkout
                            </a>
                            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="block w-full text-center mt-4 text-green-600 hover:text-green-800">
                                Continue Shopping
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
    
    // Show an alert when quantity exceeds stock
    const quantityInputs = document.querySelectorAll('input[name^="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);
            
            if (value > max) {
                alert('Sorry, only ' + max + ' items are available in stock.');
                this.value = max;
            }
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?> 