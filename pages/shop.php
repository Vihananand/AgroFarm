<?php
$page_title = "Shop";
$page_description = "Browse our wide selection of agricultural products, farming equipment, fresh produce, seeds, and more.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

$category_slug = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$search_term = isset($_GET['q']) ? sanitize($_GET['q']) : '';

$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

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

$all_products = [
    [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category_name' => 'Fertilizers',
        'category_slug' => 'fertilizers',
        'created_at' => '2023-06-15'
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
        'category_slug' => 'equipment',
        'created_at' => '2023-07-20'
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
        'category_slug' => 'seeds',
        'created_at' => '2023-08-05'
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
        'category_slug' => 'machinery',
        'created_at' => '2023-05-10'
    ],
    [
        'id' => 5,
        'name' => 'Fresh Apples (5kg)',
        'slug' => 'fresh-apples-5kg',
        'image' => 'https://picsum.photos/id/102/600/400',
        'price' => 12.99,
        'sale_price' => null,
        'stock' => 30,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'fresh-produce',
        'created_at' => '2023-09-01'
    ],
    [
        'id' => 6,
        'name' => 'Gardening Gloves',
        'slug' => 'gardening-gloves',
        'image' => 'https://picsum.photos/id/160/600/400',
        'price' => 9.99,
        'sale_price' => null,
        'stock' => 20,
        'category_name' => 'Accessories',
        'category_slug' => 'accessories',
        'created_at' => '2023-06-25'
    ],
    [
        'id' => 7,
        'name' => 'Carrot Seeds',
        'slug' => 'carrot-seeds',
        'image' => 'https://picsum.photos/id/292/600/400',
        'price' => 3.99,
        'sale_price' => null,
        'stock' => 45,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'created_at' => '2023-07-10'
    ],
    [
        'id' => 8,
        'name' => 'Irrigation System',
        'slug' => 'irrigation-system',
        'image' => 'https://picsum.photos/id/117/600/400',
        'price' => 199.99,
        'sale_price' => 169.99,
        'stock' => 5,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'created_at' => '2023-05-20'
    ],
    [
        'id' => 9,
        'name' => 'Potato Harvester',
        'slug' => 'potato-harvester',
        'image' => 'https://picsum.photos/id/239/600/400',
        'price' => 599.99,
        'sale_price' => null,
        'stock' => 3,
        'category_name' => 'Machinery',
        'category_slug' => 'machinery',
        'created_at' => '2023-08-10'
    ],
    [
        'id' => 10,
        'name' => 'Organic Strawberries (1kg)',
        'slug' => 'organic-strawberries-1kg',
        'image' => 'https://picsum.photos/id/1080/600/400',
        'price' => 14.99,
        'sale_price' => 11.99,
        'stock' => 25,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'fresh-produce',
        'created_at' => '2023-09-05'
    ]
];

$filtered_products = $all_products;
if (!empty($category_slug)) {
    $filtered_products = array_filter($all_products, function($product) use ($category_slug) {
        return $product['category_slug'] === $category_slug;
    });
}

if (!empty($search_term)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($search_term) {
        return (stripos($product['name'], $search_term) !== false || 
                stripos($product['description'], $search_term) !== false);
    });
}

usort($filtered_products, function($a, $b) use ($sort_by) {
    switch ($sort_by) {
        case 'price_low':
            return $a['price'] <=> $b['price'];
        case 'price_high':
            return $b['price'] <=> $a['price'];
        case 'popular':
            $a_featured = $a['featured'] ?? 0;
            $b_featured = $b['featured'] ?? 0;
            return $b_featured <=> $a_featured;
        case 'newest':
        default:
            $a_date = isset($a['created_at']) ? strtotime($a['created_at']) : time();
            $b_date = isset($b['created_at']) ? strtotime($b['created_at']) : time();
            return $b_date <=> $a_date;
    }
});

$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);
    
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
    function quickView(productId) {
        const modal = document.getElementById('quick-view-modal');
        const modalContent = document.getElementById('quick-view-content');
        const loadingSpinner = document.getElementById('quick-view-loading');
        
        modal.classList.remove('hidden');
        loadingSpinner.classList.remove('hidden');
        modalContent.classList.add('hidden');
        
        <?php foreach ($all_products as $product): ?>
        if (productId === <?php echo $product['id']; ?>) {
            loadingSpinner.classList.add('hidden');
            modalContent.classList.remove('hidden');
            
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
                            <p class="text-gray-700"><?php echo $product['description']; ?></p>
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
        
        loadingSpinner.classList.add('hidden');
        modalContent.classList.remove('hidden');
        modalContent.innerHTML = '<p class="text-center text-red-600">Product not found</p>';
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
    
    document.addEventListener('DOMContentLoaded', function() {
        const closeModal = document.getElementById('close-quick-view');
        const modal = document.getElementById('quick-view-modal');
        
        closeModal.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    });
</script>

<!-- Quick View Modal -->
<div id="quick-view-modal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-auto">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-xl font-bold">Quick View</h3>
            <button id="close-quick-view" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Loading Spinner -->
        <div id="quick-view-loading" class="py-12 flex justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
        </div>
        
        <!-- Modal Content (will be populated by JS) -->
        <div id="quick-view-content" class="p-6 hidden"></div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 