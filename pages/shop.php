<?php
$page_title = "Shop";
$page_description = "Browse our wide selection of agricultural products, farming equipment, fresh produce, seeds, and more.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Get category filter from URL
$category_slug = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Get search term if provided
$search_term = isset($_GET['q']) ? sanitize($_GET['q']) : '';

// Get sorting option
$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Pagination settings
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Define sample categories
$categories = [
    [
        'id' => 1,
        'name' => 'Farm Machinery',
        'slug' => 'machinery',
        'description' => 'High-quality machinery to increase farming efficiency and productivity.'
    ],
    [
        'id' => 2,
        'name' => 'Fertilizers',
        'slug' => 'fertilizers',
        'description' => 'Organic and synthetic fertilizers to boost plant growth and crop yields.'
    ],
    [
        'id' => 3,
        'name' => 'Equipment',
        'slug' => 'equipment',
        'description' => 'Essential tools and equipment for everyday farming tasks.'
    ],
    [
        'id' => 4,
        'name' => 'Fresh Produce',
        'slug' => 'produce',
        'description' => 'Farm-fresh fruits and vegetables grown with sustainable practices.'
    ],
    [
        'id' => 5,
        'name' => 'Seeds',
        'slug' => 'seeds',
        'description' => 'High-quality seeds for a variety of crops, vegetables, and flowers.'
    ]
];

// Define sample products
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

// Filter products based on category
$filtered_products = $all_products;
if (!empty($category_slug)) {
    $filtered_products = array_filter($all_products, function($product) use ($category_slug) {
        return $product['category_slug'] === $category_slug;
    });
}

// Filter products based on search term
if (!empty($search_term)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($search_term) {
        return (stripos($product['name'], $search_term) !== false || 
                stripos($product['description'], $search_term) !== false);
    });
}

// Sort products
usort($filtered_products, function($a, $b) use ($sort_by) {
    switch ($sort_by) {
        case 'price_low':
            return $a['price'] <=> $b['price'];
        case 'price_high':
            return $b['price'] <=> $a['price'];
        case 'popular':
            return $b['featured'] <=> $a['featured'];
        case 'newest':
        default:
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    }
});

// Pagination
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);

// Slice the array for pagination
$products = array_slice($filtered_products, $offset, $items_per_page);
?>

<!-- Shop Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">Shop</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            <?php 
            if (!empty($search_term)) {
                echo "Search results for: " . htmlspecialchars($search_term);
            } elseif (!empty($category_slug)) {
                foreach ($categories as $cat) {
                    if ($cat['slug'] === $category_slug) {
                        echo htmlspecialchars($cat['name']) . ": " . htmlspecialchars($cat['description']);
                        break;
                    }
                }
            } else {
                echo "Browse our wide selection of high-quality agricultural products.";
            }
            ?>
        </p>
    </div>
</section>

<!-- Shop Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4">
                <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                    <h3 class="text-xl font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="block py-2 <?php echo empty($category_slug) ? 'text-green-600 font-medium' : 'text-gray-700 hover:text-green-600'; ?>">
                                All Products
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $category['slug']; ?>" class="block py-2 <?php echo $category_slug === $category['slug'] ? 'text-green-600 font-medium' : 'text-gray-700 hover:text-green-600'; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4">Filter By</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Sort</h4>
                        <form action="<?php echo SITE_URL; ?>/pages/shop.php" method="GET" id="sort-form">
                            <?php if (!empty($category_slug)): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                            <?php endif; ?>
                            
                            <?php if (!empty($search_term)): ?>
                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_term); ?>">
                            <?php endif; ?>
                            
                            <select name="sort" id="sort-select" class="w-full border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-green-500" onchange="document.getElementById('sort-form').submit()">
                                <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Popular</option>
                            </select>
                        </form>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-2">Search</h4>
                        <form action="<?php echo SITE_URL; ?>/pages/shop.php" method="GET">
                            <?php if (!empty($category_slug)): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                            <?php endif; ?>
                            
                            <div class="flex">
                                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search products..." class="flex-grow border border-gray-300 rounded-l-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-r-md hover:bg-green-700 transition-colors">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Product Grid -->
            <div class="lg:w-3/4">
                <div class="mb-6 flex justify-between items-center">
                    <p class="text-gray-600">Showing <?php echo min(($offset + 1), $total_products); ?>-<?php echo min(($offset + $items_per_page), $total_products); ?> of <?php echo $total_products; ?> products</p>
                </div>
                
                <?php if (count($products) > 0): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
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
                                <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $product['slug']; ?>" class="block w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors text-center">
                                    View Details
                                </a>
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
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="flex items-center">
                        <?php if ($current_page > 1): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" class="px-3 py-2 border rounded-l-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php else: ?>
                        <span class="px-3 py-2 border rounded-l-md text-gray-400 bg-gray-100 cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="px-4 py-2 border-t border-b <?php echo $i === $current_page ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" class="px-3 py-2 border rounded-r-md text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php else: ?>
                        <span class="px-3 py-2 border rounded-r-md text-gray-400 bg-gray-100 cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="bg-gray-50 p-8 rounded-lg text-center">
                    <p class="text-xl text-gray-600 mb-4">No products found</p>
                    <p class="text-gray-500 mb-6">Try adjusting your search or filter to find what you're looking for.</p>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-secondary">View All Products</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Quick view functionality (modal would be implemented in a real application)
    function quickView(productId) {
        window.location.href = '<?php echo SITE_URL; ?>/pages/product.php?id=' + productId;
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
    
    // Add to cart functionality
    function addToCart(productId) {
        // Default quantity 1 for shop page quick add
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
</script>

<?php include_once '../includes/footer.php'; ?> 