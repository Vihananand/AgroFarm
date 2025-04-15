<?php
$page_title = "Checkout";
$page_description = "Complete your purchase from AgroFarm.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Redirect to cart if cart is empty
if (empty($_SESSION['cart'])) {
    setFlashMessage('error', 'Your cart is empty. Please add products to your cart before checkout.');
    redirect(SITE_URL . '/pages/cart.php');
}

// Sample products (same as in shop.php)
$all_products = [
    [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'description' => 'Premium organic fertilizer for all types of plants',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category_id' => 2,
        'category_name' => 'Fertilizers',
        'category_slug' => 'fertilizers',
        'featured' => 1,
        'created_at' => '2023-06-15'
    ],
    [
        'id' => 2,
        'name' => 'Premium Garden Hoe',
        'slug' => 'premium-garden-hoe',
        'description' => 'Durable garden hoe with comfortable grip',
        'image' => 'https://picsum.photos/id/150/600/400',
        'price' => 49.99,
        'sale_price' => null,
        'stock' => 8,
        'category_id' => 3,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'featured' => 0,
        'created_at' => '2023-07-20'
    ],
    [
        'id' => 3,
        'name' => 'Organic Tomato Seeds',
        'slug' => 'organic-tomato-seeds',
        'description' => 'Heirloom tomato seeds for your garden',
        'image' => 'https://picsum.photos/id/145/600/400',
        'price' => 5.99,
        'sale_price' => 4.99,
        'stock' => 50,
        'category_id' => 5,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'featured' => 1,
        'created_at' => '2023-08-05'
    ],
    [
        'id' => 4,
        'name' => 'Mini Tractor',
        'slug' => 'mini-tractor',
        'description' => 'Compact tractor for small farms and gardens',
        'image' => 'https://picsum.photos/id/167/600/400',
        'price' => 2999.99,
        'sale_price' => 2799.99,
        'stock' => 0,
        'category_id' => 1,
        'category_name' => 'Farm Machinery',
        'category_slug' => 'machinery',
        'featured' => 1,
        'created_at' => '2023-05-10'
    ],
    [
        'id' => 5,
        'name' => 'Fresh Apples (5kg)',
        'slug' => 'fresh-apples',
        'description' => 'Organic farm-fresh apples',
        'image' => 'https://picsum.photos/id/102/600/400',
        'price' => 12.99,
        'sale_price' => null,
        'stock' => 20,
        'category_id' => 4,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'produce',
        'featured' => 0,
        'created_at' => '2023-09-01'
    ],
    [
        'id' => 6,
        'name' => 'Gardening Gloves',
        'slug' => 'gardening-gloves',
        'description' => 'Durable and comfortable gardening gloves',
        'image' => 'https://picsum.photos/id/160/600/400',
        'price' => 15.99,
        'sale_price' => 12.99,
        'stock' => 30,
        'category_id' => 3,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'featured' => 0,
        'created_at' => '2023-06-25'
    ],
    [
        'id' => 7,
        'name' => 'Carrot Seeds',
        'slug' => 'carrot-seeds',
        'description' => 'Premium carrot seeds for your vegetable garden',
        'image' => 'https://picsum.photos/id/292/600/400',
        'price' => 3.99,
        'sale_price' => null,
        'stock' => 45,
        'category_id' => 5,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'featured' => 0,
        'created_at' => '2023-07-10'
    ],
    [
        'id' => 8,
        'name' => 'Irrigation System',
        'slug' => 'irrigation-system',
        'description' => 'Automated drip irrigation system for efficient watering',
        'image' => 'https://picsum.photos/id/117/600/400',
        'price' => 199.99,
        'sale_price' => 179.99,
        'stock' => 10,
        'category_id' => 3,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'featured' => 1,
        'created_at' => '2023-05-20'
    ],
    [
        'id' => 9,
        'name' => 'Potato Harvester',
        'slug' => 'potato-harvester',
        'description' => 'Efficient potato harvesting machine for medium-sized farms',
        'image' => 'https://picsum.photos/id/239/600/400',
        'price' => 1499.99,
        'sale_price' => null,
        'stock' => 5,
        'category_id' => 1,
        'category_name' => 'Farm Machinery',
        'category_slug' => 'machinery',
        'featured' => 0,
        'created_at' => '2023-08-10'
    ],
    [
        'id' => 10,
        'name' => 'Organic Strawberries (1kg)',
        'slug' => 'organic-strawberries',
        'description' => 'Sweet and juicy organic strawberries',
        'image' => 'https://picsum.photos/id/1080/600/400',
        'price' => 8.99,
        'sale_price' => 7.99,
        'stock' => 15,
        'category_id' => 4,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'produce',
        'featured' => 1,
        'created_at' => '2023-09-05'
    ]
];

// Convert products array to associative array by ID for easy lookup
$products_by_id = [];
foreach ($all_products as $product) {
    $products_by_id[$product['id']] = $product;
}

// Prepare cart items with product data
$cart_items = [];
$subtotal = 0;
$total_items = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products_by_id[$product_id])) {
        $product = $products_by_id[$product_id];
        $price = $product['sale_price'] ?? $product['price'];
        $item_total = $price * $quantity;
        
        $cart_items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'name' => $product['name'],
            'slug' => $product['slug'],
            'image' => $product['image'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'item_total' => $item_total
        ];
        
        $subtotal += $item_total;
        $total_items += $quantity;
    }
}

// Calculate shipping cost (simplified for example)
$shipping_cost = $subtotal > 100 ? 0 : 15;

// Calculate tax (simplified for example)
$tax_rate = 0.08; // 8%
$tax = $subtotal * $tax_rate;

// Calculate total
$total = $subtotal + $shipping_cost + $tax;

// Handle form submission for checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data here if needed
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Store order in session for the confirmation page
    $_SESSION['order'] = [
        'order_id' => 'ORD-' . time(),
        'items' => $cart_items,
        'subtotal' => $subtotal,
        'shipping' => $shipping_cost,
        'tax' => $tax,
        'total' => $total,
        'shipping_address' => $_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ' ' . $_POST['zip'],
        'shipping_name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
        'payment_method' => $_POST['payment_method'],
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to order confirmation page
    redirect(SITE_URL . '/pages/order-confirmation.php');
}
?>

<!-- Checkout Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-8">
                    <!-- Customer Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-input" required>
                            </div>
                            <div>
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-input" required>
                        </div>
                        
                        <div class="mt-4">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-input" required>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
                        
                        <div class="mt-4">
                            <label for="address" class="form-label">Street Address *</label>
                            <input type="text" id="address" name="address" class="form-input" required>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="city" class="form-label">City *</label>
                                <input type="text" id="city" name="city" class="form-input" required>
                            </div>
                            <div>
                                <label for="state" class="form-label">State/Province *</label>
                                <input type="text" id="state" name="state" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="zip" class="form-label">Zip/Postal Code *</label>
                                <input type="text" id="zip" name="zip" class="form-input" required>
                            </div>
                            <div>
                                <label for="country" class="form-label">Country *</label>
                                <select id="country" name="country" class="form-input" required>
                                    <option value="USA">United States</option>
                                    <option value="CAN">Canada</option>
                                    <option value="MEX">Mexico</option>
                                    <option value="GBR">United Kingdom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="radio" id="payment_credit" name="payment_method" value="credit_card" class="mr-2" checked>
                                <label for="payment_credit">Credit Card</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="radio" id="payment_paypal" name="payment_method" value="paypal" class="mr-2">
                                <label for="payment_paypal">PayPal</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="radio" id="payment_bank" name="payment_method" value="bank_transfer" class="mr-2">
                                <label for="payment_bank">Bank Transfer</label>
                            </div>
                        </div>
                        
                        <!-- Credit Card Details (shown/hidden based on selected payment method) -->
                        <div id="credit_card_details" class="mt-4 p-4 border border-gray-200 rounded-md">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" class="form-input" placeholder="**** **** **** ****">
                                </div>
                                <div>
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" id="card_name" name="card_name" class="form-input">
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="expiry_date" class="form-label">Expiration Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" class="form-input" placeholder="MM/YY">
                                </div>
                                <div>
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" id="cvv" name="cvv" class="form-input" placeholder="***">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Additional Information</h2>
                        
                        <div>
                            <label for="order_notes" class="form-label">Order Notes (Optional)</label>
                            <textarea id="order_notes" name="order_notes" rows="3" class="form-input" placeholder="Special instructions for delivery or order"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full text-center py-3">
                        Place Order
                    </button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    
                    <div class="max-h-64 overflow-y-auto mb-4">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="flex py-3 border-b">
                            <div class="w-16 h-16 flex-shrink-0">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="w-full h-full object-cover rounded">
                            </div>
                            <div class="ml-4 flex-grow">
                                <h3 class="text-sm font-medium"><?php echo $item['name']; ?></h3>
                                <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                <p class="text-sm font-medium mt-1">
                                    <?php echo '$' . number_format($item['item_total'], 2); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-2 border-t pt-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span><?php echo '$' . number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <?php if ($shipping_cost > 0): ?>
                            <span><?php echo '$' . number_format($shipping_cost, 2); ?></span>
                            <?php else: ?>
                            <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (8%)</span>
                            <span><?php echo '$' . number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold mt-4 pt-4 border-t">
                            <span>Total</span>
                            <span class="text-green-600"><?php echo '$' . number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-lock text-green-600 mr-2"></i>
                            <span class="text-sm">Secure Payment</span>
                        </div>
                        <div class="flex space-x-2">
                            <div class="p-1 border rounded">
                                <i class="fab fa-cc-visa text-blue-700 text-2xl"></i>
                            </div>
                            <div class="p-1 border rounded">
                                <i class="fab fa-cc-mastercard text-red-600 text-2xl"></i>
                            </div>
                            <div class="p-1 border rounded">
                                <i class="fab fa-cc-paypal text-blue-500 text-2xl"></i>
                            </div>
                            <div class="p-1 border rounded">
                                <i class="fab fa-cc-amex text-blue-400 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Payment method toggle
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardDetails = document.getElementById('credit_card_details');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                if (this.value === 'credit_card') {
                    creditCardDetails.style.display = 'block';
                } else {
                    creditCardDetails.style.display = 'none';
                }
            });
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?> 