<?php
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
    
    function addToWishlist(productId) {
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
                const wishlistCountElement = document.getElementById('wishlist-count');
                if (wishlistCountElement) {
                    wishlistCountElement.textContent = data.wishlist_count;
                }
                
                alert(data.message || 'Product added to wishlist successfully');
            } else {
                alert(data.message || 'Failed to add product to wishlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the product to wishlist');
        });
    }
    
    function quickView(productId) {
        const modal = document.getElementById('quick-view-modal');
        const modalContent = document.getElementById('quick-view-content');
        const loadingSpinner = document.getElementById('quick-view-loading');
        
        if (!modal || !modalContent) {
            window.location.href = '<?php echo SITE_URL; ?>/pages/product.php?id=' + productId;
            return;
        }
        
        modal.classList.remove('hidden');
        if (loadingSpinner) {
            loadingSpinner.classList.remove('hidden');
            modalContent.classList.add('hidden');
        }
        
        <?php foreach ($featured_products as $product): ?>
        if (productId === <?php echo $product['id']; ?>) {
            if (loadingSpinner) {
                loadingSpinner.classList.add('hidden');
                modalContent.classList.remove('hidden');
            }

            const productHTML = `
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Product Image -->
                    <div class="product-image">
                        <img src="<?php echo $product['image']; ?>" 
                            alt="<?php echo $product['name']; ?>" 
                            class="w-full h-auto object-cover rounded-lg">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                            Sale
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="product-details">
                        <span class="text-sm text-green-600">
                            <?php echo $product['category_name']; ?>
                        </span>
                        <h2 class="text-2xl font-bold mb-2"><?php echo $product['name']; ?></h2>
                        
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
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                In Stock (<?php echo $product['stock']; ?> available)
                            </span>
                            <?php else: ?>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
                                Out of Stock
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="description mb-6">
                            <p class="text-gray-700">This is a sample product description. In a real application, this would contain detailed information about the product.</p>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                        <div class="actions flex gap-4 mb-6">
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                                <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                            </button>
                            <button onclick="addToWishlist(<?php echo $product['id']; ?>)" 
                                    class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                                <i class="far fa-heart mr-2"></i>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="mb-6">
                            <button class="w-full bg-gray-300 text-gray-600 cursor-not-allowed py-3 px-6 rounded-md flex items-center justify-center">
                                <i class="fas fa-ban mr-2"></i> Out of Stock
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>" 
                           class="text-green-600 hover:text-green-800 flex items-center">
                            <span>View Full Details</span>
                            <i class="fas fa-chevron-right ml-2 text-sm"></i>
                        </a>
                    </div>
                </div>
            `;
            
            modalContent.innerHTML = productHTML;
            return;
        }
        <?php endforeach; ?>
        
        if (loadingSpinner) {
            loadingSpinner.classList.add('hidden');
            modalContent.classList.remove('hidden');
        }
        modalContent.innerHTML = '<p class="text-center text-red-600">Product not found</p>';
    }
</script>
