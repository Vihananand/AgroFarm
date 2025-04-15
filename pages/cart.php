<?php
$page_title = "Shopping Cart";
$page_description = "View and manage your AgroFarm shopping cart.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
            setFlashMessage('success', 'Cart updated successfully.');
        } else {
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                setFlashMessage('success', 'Item removed from cart.');
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $product_id = (int)$_POST['product_id'];
        
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            setFlashMessage('success', 'Item removed from cart.');
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'clear') {
        
        $_SESSION['cart'] = [];
        setFlashMessage('success', 'Cart cleared successfully.');
    }
    
    redirect(SITE_URL . '/pages/cart.php');
}

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

$products_by_id = [];
foreach ($all_products as $product) {
    $products_by_id[$product['id']] = $product;
}

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

$shipping_cost = $subtotal > 100 ? 0 : 15;

$tax_rate = 0.08; // 8%
$tax = $subtotal * $tax_rate;

$total = $subtotal + $shipping_cost + $tax;
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Your Shopping Cart</h1>
        
        <?php if (count($cart_items) > 0): ?>
        
        <div class="grid lg:grid-cols-3 gap-8">
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
                                <img src="<?php echo $item['image']; ?>" 
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
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <label for="quantity-<?php echo $item['product_id']; ?>" class="sr-only">Quantity</label>
                                        <div class="flex items-center border rounded">
                                            <button type="button" class="px-3 py-1 bg-gray-100 border-r" onclick="decrementQuantity(<?php echo $item['product_id']; ?>)">-</button>
                                            <input type="number" id="quantity-<?php echo $item['product_id']; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="w-12 text-center p-1 focus:outline-none">
                                            <button type="button" class="px-3 py-1 bg-gray-100 border-l" onclick="incrementQuantity(<?php echo $item['product_id']; ?>)">+</button>
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
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
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
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-6">Order Summary</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span><?php echo '$' . number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <?php if ($shipping_cost > 0): ?>
                            <span><?php echo '$' . number_format($shipping_cost, 2); ?></span>
                            <?php else: ?>
                            <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (8%)</span>
                            <span><?php echo '$' . number_format($tax, 2); ?></span>
                        </div>
                        
                        <div class="border-t pt-4 mt-4">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total</span>
                                <span class="text-green-600"><?php echo '$' . number_format($total, 2); ?></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Free shipping on orders over $100
                            </p>
                        </div>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="btn-primary w-full text-center">
                        Proceed to Checkout
                    </a>
                    
                    <div class="mt-6">
                        <h3 class="font-medium mb-2">We Accept</h3>
                        <div class="flex space-x-2">
                            <div class="p-2 border rounded">
                                <i class="fab fa-cc-visa text-blue-700 text-2xl"></i>
                            </div>
                            <div class="p-2 border rounded">
                                <i class="fab fa-cc-mastercard text-red-600 text-2xl"></i>
                            </div>
                            <div class="p-2 border rounded">
                                <i class="fab fa-cc-paypal text-blue-500 text-2xl"></i>
                            </div>
                            <div class="p-2 border rounded">
                                <i class="fab fa-cc-amex text-blue-400 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 class="text-2xl font-semibold mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Looks like you haven't added any products to your cart yet.</p>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-primary inline-block">
                Start Shopping
            </a>
        </div>
        
        <?php endif; ?>
        
    </div>
</section>

<script>
    function incrementQuantity(productId) {
        const inputElement = document.getElementById('quantity-' + productId);
        let currentValue = parseInt(inputElement.value, 10);
        if (currentValue < 99) {
            inputElement.value = currentValue + 1;
        }
    }
    
    function decrementQuantity(productId) {
        const inputElement = document.getElementById('quantity-' + productId);
        let currentValue = parseInt(inputElement.value, 10);
        if (currentValue > 1) {
            inputElement.value = currentValue - 1;
        }
    }
</script>

<?php include_once '../includes/footer.php'; ?> 