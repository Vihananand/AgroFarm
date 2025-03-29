<?php
$page_title = "Product Details";
include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// In a real application, you would fetch product data from database
// For demo purposes, we'll use sample data based on the slug parameter
$slug = $_GET['slug'] ?? '';

// Define sample products
$products = [
    'organic-fertilizer' => [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category_name' => 'Fertilizers',
        'category_slug' => 'fertilizers',
        'description' => 'Our premium organic fertilizer is made from 100% natural ingredients. It provides essential nutrients to your plants without harmful chemicals. Perfect for organic gardening and environmentally conscious farmers.',
        'specifications' => [
            'Weight' => '5 kg',
            'Composition' => 'Compost, Bone Meal, Seaweed',
            'NPK Ratio' => '4-3-3',
            'Suitable for' => 'All plants and vegetables'
        ]
    ],
    'premium-garden-hoe' => [
        'id' => 2,
        'name' => 'Premium Garden Hoe',
        'slug' => 'premium-garden-hoe',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 49.99,
        'sale_price' => null,
        'stock' => 8,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'description' => 'Our premium garden hoe features a durable stainless steel blade and comfortable ergonomic handle. Perfect for weeding, cultivating soil, and creating furrows for planting.',
        'specifications' => [
            'Handle Length' => '120 cm',
            'Blade Width' => '15 cm',
            'Material' => 'Stainless Steel, Hardwood Handle',
            'Weight' => '1.2 kg'
        ]
    ],
    'organic-tomato-seeds' => [
        'id' => 3,
        'name' => 'Organic Tomato Seeds',
        'slug' => 'organic-tomato-seeds',
        'image' => 'https://picsum.photos/id/145/600/400',
        'price' => 5.99,
        'sale_price' => 4.99,
        'stock' => 50,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'description' => 'Our organic tomato seeds are sourced from heirloom varieties, grown without pesticides or genetic modification. These seeds produce juicy, flavorful tomatoes that are perfect for home gardens.',
        'specifications' => [
            'Variety' => 'Beefsteak',
            'Quantity' => '50 seeds per packet',
            'Growing Season' => 'Spring to Summer',
            'Days to Germination' => '5-10 days',
            'Days to Maturity' => '70-80 days'
        ]
    ],
    'mini-tractor' => [
        'id' => 4,
        'name' => 'Mini Tractor',
        'slug' => 'mini-tractor',
        'image' => 'https://picsum.photos/id/167/600/400',
        'price' => 2999.99,
        'sale_price' => 2799.99,
        'stock' => 0,
        'category_name' => 'Machinery',
        'category_slug' => 'machinery',
        'description' => 'This compact mini tractor is perfect for small farms and large gardens. With its powerful engine and versatile attachments, it can handle plowing, tilling, mowing, and more with ease.',
        'specifications' => [
            'Engine' => '15 HP Diesel',
            'Transmission' => '8-speed gear shift',
            'Dimensions' => '2.3m x 1.2m x 1.5m',
            'Weight' => '580 kg',
            'Fuel Capacity' => '20 liters'
        ]
    ]
];

// Get product data or redirect if not found
if (isset($products[$slug])) {
    $product = $products[$slug];
} else {
    // Redirect to shop if product not found
    header('Location: ' . SITE_URL . '/pages/shop.php');
    exit();
}
?>

<!-- Breadcrumbs -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <div class="flex items-center text-sm">
            <a href="<?php echo SITE_URL; ?>" class="text-gray-600 hover:text-green-600">Home</a>
            <span class="mx-2">/</span>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-gray-600 hover:text-green-600">Shop</a>
            <span class="mx-2">/</span>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $product['category_slug']; ?>" class="text-gray-600 hover:text-green-600"><?php echo $product['category_name']; ?></a>
            <span class="mx-2">/</span>
            <span class="text-green-600"><?php echo $product['name']; ?></span>
        </div>
    </div>
</div>

<!-- Product Details -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div class="product-image" data-gsap="fade-right">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full rounded-lg shadow-md">
            </div>
            
            <!-- Product Info -->
            <div class="product-info" data-gsap="fade-left">
                <h1 class="text-3xl md:text-4xl font-bold mb-2"><?php echo $product['name']; ?></h1>
                
                <div class="text-sm text-green-600 mb-4">
                    Category: <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a>
                </div>
                
                <div class="price-wrapper mb-6">
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="text-3xl font-bold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                    <span class="text-xl text-gray-500 line-through ml-2">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php else: ?>
                    <span class="text-3xl font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="stock mb-6">
                    <?php if ($product['stock'] > 0): ?>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="description mb-6">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <p class="text-gray-700"><?php echo $product['description']; ?></p>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                <div class="quantity-selector mb-6">
                    <h3 class="text-lg font-semibold mb-2">Quantity</h3>
                    <div class="flex items-center">
                        <button id="decrease-qty" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-l-md">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-16 text-center border-y border-gray-200 py-1">
                        <button id="increase-qty" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-r-md">+</button>
                    </div>
                </div>
                
                <div class="actions flex flex-col sm:flex-row gap-4 mb-8">
                    <button id="add-to-cart" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                    <button id="add-to-wishlist" class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                        <i class="far fa-heart mr-2"></i> Add to Wishlist
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-8">
                    <button class="w-full bg-gray-300 text-gray-600 cursor-not-allowed py-3 px-6 rounded-md flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i> Out of Stock
                    </button>
                    <p class="text-sm text-gray-600 mt-2">This product is currently out of stock. Please check back later or browse similar products.</p>
                </div>
                <?php endif; ?>
                
                <!-- Product Specifications -->
                <div class="specifications">
                    <h2 class="text-xl font-semibold mb-4">Specifications</h2>
                    <div class="border rounded-lg overflow-hidden">
                        <?php $i = 0; foreach ($product['specifications'] as $label => $value): $i++; ?>
                        <div class="grid grid-cols-2 gap-4 p-3 <?php echo $i % 2 === 0 ? 'bg-gray-50' : 'bg-white'; ?>">
                            <div class="font-medium"><?php echo $label; ?></div>
                            <div><?php echo $value; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">You May Also Like</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php 
            // Get related products (excluding current product)
            $related_products = array_filter($products, function($p) use ($product) {
                return $p['slug'] !== $product['slug'];
            });
            
            // Display up to 4 related products
            $count = 0;
            foreach ($related_products as $related): 
                if ($count >= 4) break;
                $count++;
            ?>
            <div class="product-card" data-gsap="fade-up">
                <div class="relative overflow-hidden group">
                    <img src="<?php echo $related['image']; ?>" 
                         alt="<?php echo $related['name']; ?>" 
                         class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                    
                    <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                        Sale
                    </div>
                    <?php endif; ?>
                    
                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $related['slug']; ?>" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors block text-center">
                            View Details
                        </a>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="text-sm text-green-600 mb-1">
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $related['category_slug']; ?>">
                            <?php echo $related['category_name']; ?>
                        </a>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $related['slug']; ?>" class="hover:text-green-600 transition-colors">
                            <?php echo $related['name']; ?>
                        </a>
                    </h3>
                    <div class="flex justify-between items-center">
                        <div class="price-wrapper">
                            <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($related['sale_price'], 2); ?></span>
                            <span class="text-sm text-gray-500 line-through ml-2">$<?php echo number_format($related['price'], 2); ?></span>
                            <?php else: ?>
                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($related['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity selector
        const quantityInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        
        if (quantityInput && decreaseBtn && increaseBtn) {
            // Min and max values
            const minQty = 1;
            const maxQty = <?php echo $product['stock'] > 0 ? $product['stock'] : 1; ?>;
            
            // Update quantity display
            function updateQuantityInput(qty) {
                quantityInput.value = qty;
            }
            
            // Decrease button
            decreaseBtn.addEventListener('click', function() {
                let currentQty = parseInt(quantityInput.value);
                if (currentQty > minQty) {
                    updateQuantityInput(currentQty - 1);
                }
            });
            
            // Increase button
            increaseBtn.addEventListener('click', function() {
                let currentQty = parseInt(quantityInput.value);
                if (currentQty < maxQty) {
                    updateQuantityInput(currentQty + 1);
                }
            });
            
            // Manual input validation
            quantityInput.addEventListener('change', function() {
                let qty = parseInt(this.value);
                
                if (isNaN(qty) || qty < minQty) {
                    updateQuantityInput(minQty);
                } else if (qty > maxQty) {
                    updateQuantityInput(maxQty);
                }
            });
        }
        
        // Add to cart button
        const addToCartBtn = document.getElementById('add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = <?php echo $product['id']; ?>;
                const quantity = parseInt(document.getElementById('quantity').value);
                
                // AJAX request to add item to cart
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
                        // Update cart count in the navbar
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = data.cart_count;
                        }
                        
                        // Show success message
                        alert(data.message || 'Product added to cart successfully');
                    } else {
                        // Show error message
                        alert(data.message || 'Failed to add product to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the product to cart');
                });
            });
        }
        
        // Add to wishlist button
        const addToWishlistBtn = document.getElementById('add-to-wishlist');
        if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener('click', function() {
                const productId = <?php echo $product['id']; ?>;
                
                // AJAX request to add item to wishlist
                fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update wishlist count in the navbar
                        const wishlistCountElement = document.getElementById('wishlist-count');
                        if (wishlistCountElement) {
                            wishlistCountElement.textContent = data.wishlist_count;
                        }
                        
                        // Show success message
                        alert(data.message || 'Product added to wishlist successfully');
                    } else {
                        // Show error message
                        alert(data.message || 'Failed to add product to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the product to wishlist');
                });
            });
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?> 