<?php
// Instead of querying the database, create random product data
$featured_products = [
    [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category_name' => 'Fertilizers',
        'category_slug' => 'fertilizers'
    ],
    [
        'id' => 2,
        'name' => 'Premium Garden Hoe',
        'slug' => 'premium-garden-hoe',
        'image' => 'https://picsum.photos/id/150/600/400',
        'price' => 49.99,
        'sale_price' => null,
        'stock' => 8,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment'
    ],
    [
        'id' => 3,
        'name' => 'Organic Tomato Seeds',
        'slug' => 'organic-tomato-seeds',
        'image' => 'https://picsum.photos/id/145/600/400',
        'price' => 5.99,
        'sale_price' => 4.99,
        'stock' => 50,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds'
    ],
    [
        'id' => 4,
        'name' => 'Mini Tractor',
        'slug' => 'mini-tractor',
        'image' => 'https://picsum.photos/id/167/600/400',
        'price' => 2999.99,
        'sale_price' => 2799.99,
        'stock' => 0,
        'category_name' => 'Machinery',
        'category_slug' => 'machinery'
    ]
];

// Display featured products
foreach ($featured_products as $product) {
?>
        <div class="product-card" data-gsap="fade-up">
            <div class="relative overflow-hidden group">
                <img src="<?php echo $product['image']; ?>" 
                     alt="<?php echo $product['name']; ?>" 
                     class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                
                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                    Sale
                </div>
                <?php endif; ?>
                
                <div class="absolute top-2 right-2 flex flex-col gap-2">
                    <button onclick="addToWishlist(<?php echo $product['id']; ?>)" 
                            class="bg-white p-2 rounded-full text-gray-600 hover:text-red-500 transition-colors">
                        <i class="far fa-heart"></i>
                    </button>
                    <button onclick="quickView(<?php echo $product['id']; ?>)" 
                            class="bg-white p-2 rounded-full text-gray-600 hover:text-blue-500 transition-colors">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md transition-colors">
                        Add to Cart
                    </button>
                </div>
            </div>
            
            <div class="p-4">
                <div class="text-sm text-green-600 mb-1">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $product['category_slug']; ?>">
                        <?php echo $product['category_name']; ?>
                    </a>
                </div>
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
                    <div class="stock">
                        <?php if ($product['stock'] > 0): ?>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">In Stock</span>
                        <?php else: ?>
                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
}
?>

<!-- Quick View Modal Placeholder (will be populated via JS) -->
<div id="quick-view-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-xl font-bold">Quick View</h3>
            <button id="close-quick-view" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="quick-view-content" class="p-4">
            <!-- Content will be loaded dynamically -->
            <div class="flex justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add to cart functionality
    function addToCart(productId) {
        // Default quantity 1 for featured products
        const quantity = 1;
        
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
    }
    
    // Add to wishlist functionality
    function addToWishlist(productId) {
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
    }
    
    // Quick view functionality
    function quickView(productId) {
        const modal = document.getElementById('quick-view-modal');
        const content = document.getElementById('quick-view-content');
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Sample product data for demo
        let productData = '';
        
        // Find the product based on ID
        <?php foreach ($featured_products as $product): ?>
        if (productId === <?php echo $product['id']; ?>) {
            productData = `
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="product-image">
                        <img src="<?php echo $product['image']; ?>" 
                             alt="<?php echo $product['name']; ?>"
                             class="w-full h-auto rounded-lg">
                    </div>
                    <div class="product-details">
                        <h2 class="text-2xl font-bold mb-2"><?php echo $product['name']; ?></h2>
                        <div class="text-sm text-green-600 mb-4">
                            Category: <?php echo $product['category_name']; ?>
                        </div>
                        <div class="price-wrapper mb-4">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <span class="text-2xl font-bold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                            <span class="text-lg text-gray-500 line-through ml-2">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php else: ?>
                            <span class="text-2xl font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="stock mb-4">
                            <?php if ($product['stock'] > 0): ?>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">In Stock (<?php echo $product['stock']; ?> available)</span>
                            <?php else: ?>
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="description mb-4">
                            <p>This is a sample product description. In a real application, this would contain detailed information about the product.</p>
                        </div>
                        <div class="actions flex gap-4">
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors">
                                Add to Cart
                            </button>
                            <button onclick="addToWishlist(<?php echo $product['id']; ?>)" 
                                    class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 py-2 px-4 rounded-md transition-colors">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        <?php endforeach; ?>
        
        content.innerHTML = productData || '<p class="text-center text-gray-500">Product details not available</p>';
    }
    
    // Close quick view modal
    document.getElementById('close-quick-view').addEventListener('click', function() {
        document.getElementById('quick-view-modal').classList.add('hidden');
    });
    
    // Close modal when clicking outside content
    document.getElementById('quick-view-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>
