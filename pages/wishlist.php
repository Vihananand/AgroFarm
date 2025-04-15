<?php
$page_title = "My Wishlist";
$page_description = "View and manage your saved items.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Initialize wishlist array if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Handle remove from wishlist action
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $key = array_search($remove_id, $_SESSION['wishlist']);
    if ($key !== false) {
        unset($_SESSION['wishlist'][$key]);
        // Reindex array
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
        setFlashMessage('success', 'Item removed from wishlist.');
    }
    redirect(SITE_URL . '/pages/wishlist.php');
}

// Get all product data for wishlist items
$wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    // Define sample products - same as shop.php
    $all_products = [
        1 => [
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
            'category_slug' => 'fertilizers'
        ],
        2 => [
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
            'category_slug' => 'equipment'
        ],
        3 => [
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
            'category_slug' => 'seeds'
        ],
        4 => [
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
            'category_slug' => 'machinery'
        ],
        5 => [
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
            'category_slug' => 'produce'
        ],
        6 => [
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
            'category_slug' => 'equipment'
        ],
        7 => [
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
            'category_slug' => 'seeds'
        ],
        8 => [
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
            'category_slug' => 'equipment'
        ],
        9 => [
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
            'category_slug' => 'machinery'
        ],
        10 => [
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
            'category_slug' => 'produce'
        ]
    ];

    foreach ($_SESSION['wishlist'] as $product_id) {
        if (isset($all_products[$product_id])) {
            $wishlist_products[] = $all_products[$product_id];
        }
    }
}
?>

<!-- Wishlist Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">My Wishlist</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Items you've saved for later. Add them to your cart when you're ready to purchase.
        </p>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <?php if (empty($wishlist_products)): ?>
        <div class="text-center py-8">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="far fa-heart"></i>
            </div>
            <h2 class="text-2xl font-semibold mb-4">Your wishlist is empty</h2>
            <p class="text-gray-600 mb-8">Browse our shop to find products you like and add them to your wishlist.</p>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-primary">
                Continue Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 text-left">Product</th>
                        <th class="p-4 text-left">Price</th>
                        <th class="p-4 text-left">Stock Status</th>
                        <th class="p-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($wishlist_products as $product): ?>
                    <tr>
                        <td class="p-4">
                            <div class="flex items-center">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-16 h-16 object-cover rounded-md mr-4">
                                <div>
                                    <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>" class="font-semibold hover:text-green-600 transition-colors">
                                        <?php echo $product['name']; ?>
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        <?php echo $product['category_name']; ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <div class="flex flex-col">
                                <span class="font-semibold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <span class="text-sm text-gray-500 line-through">$<?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="font-semibold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <?php if ($product['stock'] > 0): ?>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                In Stock
                            </span>
                            <?php else: ?>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
                                Out of Stock
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <div class="flex space-x-2">
                                <?php if ($product['stock'] > 0): ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                    <i class="fas fa-shopping-cart mr-1"></i> Add to Cart
                                </button>
                                <?php endif; ?>
                                <a href="<?php echo SITE_URL; ?>/pages/wishlist.php?remove=<?php echo $product['id']; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Remove
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 flex justify-between items-center">
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-green-600 hover:text-green-800 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
            </a>
            <button onclick="clearWishlist()" class="text-red-600 hover:text-red-800 focus:outline-none">
                <i class="far fa-trash-alt mr-1"></i> Clear Wishlist
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function addToCart(productId) {
        const quantity = 1;
        
        fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cart_count;
                }
                
                alert(data.message || 'Product added to cart successfully');
            } else {
                alert(data.message || 'Failed to add product to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the product to cart');
        });
    }
    
    function clearWishlist() {
        if (confirm('Are you sure you want to clear your entire wishlist?')) {
            window.location.href = '<?php echo SITE_URL; ?>/pages/wishlist.php?clear=true';
        }
    }
    
    <?php if (isset($_GET['clear']) && $_GET['clear'] === 'true'): ?>
    <?php 
        $_SESSION['wishlist'] = [];
        echo "window.location.href = '" . SITE_URL . "/pages/wishlist.php?cleared=true';";
    ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['cleared']) && $_GET['cleared'] === 'true'): ?>
    alert('Your wishlist has been cleared.');
    <?php endif; ?>
</script>

<?php include_once '../includes/footer.php'; ?> 