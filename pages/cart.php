<?php
$page_title = "Shopping Cart";
$page_description = "View and manage your AgroFarm shopping cart.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Save current URL to redirect back after login
    $redirect_url = SITE_URL . '/pages/cart.php';
    redirect(SITE_URL . '/pages/login.php?redirect=' . urlencode($redirect_url));
}

// Process cart update or removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Update cart item quantity
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0) {
            try {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, getUserId()]);
                setFlashMessage('success', 'Cart updated successfully.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to update cart.');
            }
        } else {
            // If quantity is 0 or negative, remove item
            try {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, getUserId()]);
                setFlashMessage('success', 'Item removed from cart.');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to remove item from cart.');
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
        // Remove cart item
        $cart_id = (int)$_POST['cart_id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, getUserId()]);
            setFlashMessage('success', 'Item removed from cart.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Failed to remove item from cart.');
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'clear') {
        // Clear entire cart
        try {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([getUserId()]);
            setFlashMessage('success', 'Cart cleared successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Failed to clear cart.');
        }
    }
    
    // Redirect to avoid form resubmission
    redirect(SITE_URL . '/pages/cart.php');
}

// Get cart items
try {
    $stmt = $conn->prepare("
        SELECT c.id as cart_id, c.quantity, p.*, c.quantity * COALESCE(p.sale_price, p.price) as item_total 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([getUserId()]);
    $cart_items = $stmt->fetchAll();
    
    // Calculate cart totals
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
    }
    
    // Calculate shipping cost (simplified for example)
    $shipping_cost = $subtotal > 100 ? 0 : 15;
    
    // Calculate tax (simplified for example)
    $tax_rate = 0.08; // 8%
    $tax = $subtotal * $tax_rate;
    
    // Calculate total
    $total = $subtotal + $shipping_cost + $tax;
    
} catch (PDOException $e) {
    $cart_items = [];
    $subtotal = 0;
    $shipping_cost = 0;
    $tax = 0;
    $total = 0;
    $total_items = 0;
    
    setFlashMessage('error', 'Failed to retrieve cart items.');
}
?>

<!-- Cart Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Your Shopping Cart</h1>
        
        <?php if (count($cart_items) > 0): ?>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold">Cart Items (<?php echo $total_items; ?>)</h2>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="inline">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Are you sure you want to clear your cart?')">
                                    Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="divide-y">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="p-4 flex flex-col md:flex-row gap-4">
                            <div class="md:w-24 flex-shrink-0">
                                <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                    alt="<?php echo $item['name']; ?>" 
                                    class="w-full h-24 object-cover rounded">
                            </div>
                            
                            <div class="flex-grow">
                                <h3 class="font-semibold text-lg">
                                    <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $item['slug']; ?>" class="hover:text-green-600 transition-colors">
                                        <?php echo $item['name']; ?>
                                    </a>
                                </h3>
                                
                                <div class="text-gray-500 mb-2">
                                    <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                    <span class="text-green-600"><?php echo '$' . number_format($item['sale_price'], 2); ?></span>
                                    <span class="line-through ml-2"><?php echo '$' . number_format($item['price'], 2); ?></span>
                                    <?php else: ?>
                                    <span><?php echo '$' . number_format($item['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between items-center mt-2">
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="flex items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <label for="quantity-<?php echo $item['cart_id']; ?>" class="sr-only">Quantity</label>
                                        <div class="flex items-center border rounded">
                                            <button type="button" class="px-3 py-1 bg-gray-100 border-r" onclick="decrementQuantity(<?php echo $item['cart_id']; ?>)">-</button>
                                            <input type="number" id="quantity-<?php echo $item['cart_id']; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="w-12 text-center p-1 focus:outline-none">
                                            <button type="button" class="px-3 py-1 bg-gray-100 border-l" onclick="incrementQuantity(<?php echo $item['cart_id']; ?>)">+</button>
                                        </div>
                                        <button type="submit" class="text-green-600 hover:text-green-800 ml-2">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                    
                                    <div class="flex items-center">
                                        <span class="font-medium">
                                            <?php echo '$' . number_format($item['item_total'], 2); ?>
                                        </span>
                                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="ml-4">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="flex items-center text-green-600 hover:text-green-800">
                        <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b">
                        <h2 class="text-xl font-semibold">Order Summary</h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (<?php echo $total_items; ?> items)</span>
                            <span class="font-semibold"><?php echo '$' . number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <?php if ($shipping_cost > 0): ?>
                            <span class="font-semibold"><?php echo '$' . number_format($shipping_cost, 2); ?></span>
                            <?php else: ?>
                            <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (8%)</span>
                            <span class="font-semibold"><?php echo '$' . number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="border-t pt-4 mt-4">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span><?php echo '$' . number_format($total, 2); ?></span>
                            </div>
                            <?php if ($shipping_cost === 0): ?>
                            <div class="text-green-600 text-sm mt-2">
                                <i class="fas fa-check-circle mr-1"></i> Free shipping applied
                            </div>
                            <?php else: ?>
                            <div class="text-gray-500 text-sm mt-2">
                                Spend <?php echo '$' . number_format(100 - $subtotal, 2); ?> more for free shipping
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-6">
                            <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="btn-primary block text-center">
                                Proceed to Checkout
                            </a>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-gray-500 text-sm">Secure payment powered by Stripe</p>
                            <div class="flex justify-center gap-2 mt-2">
                                <i class="fab fa-cc-visa text-2xl text-gray-500"></i>
                                <i class="fab fa-cc-mastercard text-2xl text-gray-500"></i>
                                <i class="fab fa-cc-amex text-2xl text-gray-500"></i>
                                <i class="fab fa-cc-discover text-2xl text-gray-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Policy -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mt-6">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-semibold">Shipping & Returns</h2>
                    </div>
                    
                    <div class="p-4 space-y-3 text-sm text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-truck mt-1 mr-3 text-green-600"></i>
                            <p>Free shipping on orders over $100</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-calendar-alt mt-1 mr-3 text-green-600"></i>
                            <p>Estimated delivery: 3-5 business days</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-undo mt-1 mr-3 text-green-600"></i>
                            <p>30-day return policy on most items</p>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/pages/shipping.php" class="text-green-600 hover:text-green-800 text-sm block mt-2">
                            View shipping policy
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Empty Cart -->
        <div class="bg-white p-8 rounded-lg shadow-sm text-center">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-gray-400 text-3xl"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold mb-2">Your Cart is Empty</h2>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Looks like you haven't added anything to your cart yet. Explore our products and find something you like!
            </p>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-primary">
                Start Shopping
            </a>
        </div>
        
        <!-- Product Recommendations -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Recommended Products</h2>
            
            <div class="grid md:grid-cols-4 gap-6">
                <?php
                // Get featured products
                try {
                    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                                          FROM products p
                                          LEFT JOIN categories c ON p.category_id = c.id
                                          WHERE p.featured = 1
                                          ORDER BY RAND()
                                          LIMIT 4");
                    $stmt->execute();
                    $recommended_products = $stmt->fetchAll();
                    
                    foreach ($recommended_products as $product):
                ?>
                <div class="product-card" data-gsap="fade-up">
                    <div class="relative overflow-hidden group">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image'] ?? 'placeholder.jpg'; ?>" 
                             alt="<?php echo $product['name']; ?>" 
                             class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110">
                        
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                            Sale
                        </div>
                        <?php endif; ?>
                        
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md transition-colors">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2">
                            <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>" class="hover:text-green-600 transition-colors">
                                <?php echo $product['name']; ?>
                            </a>
                        </h3>
                        <div class="flex justify-between items-center">
                            <div class="price-wrapper">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <span class="text-sm text-gray-500 line-through ml-2">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php else: ?>
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach;
                } catch (PDOException $e) {
                    // Handle error
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Quantity increment/decrement functionality
    function incrementQuantity(cartId) {
        const input = document.getElementById('quantity-' + cartId);
        const currentValue = parseInt(input.value);
        input.value = currentValue + 1;
    }
    
    function decrementQuantity(cartId) {
        const input = document.getElementById('quantity-' + cartId);
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }
    
    // Add to cart functionality (for recommended products)
    function addToCart(productId) {
        // AJAX request to add item to cart
        fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated cart
                window.location.reload();
            } else {
                alert(data.message || 'Failed to add product to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again later.');
        });
    }
</script>

<?php include_once '../includes/footer.php'; ?> 