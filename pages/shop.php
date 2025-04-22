<?php
$page_title = "Shop - AgroFarm";
$page_description = "Browse our selection of fresh farm products";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Get filter parameters
$category_slug = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search_term = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Pagination settings
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

try {
    // Fetch categories from database
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo "<!-- Debug: Found " . count($categories) . " categories -->";

    // Build the product query
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p
              LEFT JOIN categories c ON p.category_id = c.id";
$params = [];
    $where_conditions = [];

    // Add category filter
if (!empty($category_slug)) {
        $where_conditions[] = "c.slug = ?";
    $params[] = $category_slug;
}

    // Add search filter
if (!empty($search_term)) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

    // Add WHERE clause if we have conditions
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
            $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
            $query .= " ORDER BY p.price DESC";
        break;
    case 'popular':
            $query .= " ORDER BY p.featured DESC, p.id DESC";
        break;
    case 'newest':
    default:
            $query .= " ORDER BY p.id DESC";
    }

    echo "<!-- Debug: Query before pagination: " . htmlspecialchars($query) . " -->";
    echo "<!-- Debug: Params: " . htmlspecialchars(print_r($params, true)) . " -->";

    // Get total products count for pagination
    $count_query = "SELECT COUNT(*) as total FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id";
    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    echo "<!-- Debug: Count query: " . htmlspecialchars($count_query) . " -->";
    
    $stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $total_products = $stmt->fetch()['total'];
    
    echo "<!-- Debug: Total products found: " . $total_products . " -->";

    // Add pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $offset;
    
    // Get products
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    echo "<!-- Debug: Products fetched: " . count($products) . " -->";
    
} catch (PDOException $e) {
    error_log("Shop page error: " . $e->getMessage());
    echo "<!-- Debug: Database error: " . htmlspecialchars($e->getMessage()) . " -->";
    $error_message = "An error occurred while fetching products. Please try again later.";
    $products = [];
    $categories = [];
    $total_products = 0;
    $total_pages = 0;
}
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
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Categories</h2>
                    <div id="categories-list" class="space-y-2">
                        <!-- Categories will be loaded here -->
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="w-full md:w-3/4">
                <div id="loading" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Loading products...</p>
                </div>
                <div id="error-message" class="hidden text-center py-8 text-red-600"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="products-grid">
                    <!-- Products will be loaded here -->
                </div>
                
                <!-- Server-side rendered products for fallback -->
                <div class="server-products-grid hidden">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden" data-category="<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>">
                        <div class="relative h-48">
                            <img src="<?php echo !empty($product['image']) ? '/AgroFarm/assets/images/products/' . $product['image'] : '/AgroFarm/assets/images/products/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-cover"
                                 onerror="this.src='/AgroFarm/assets/images/products/default-product.jpg'">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm">
                                Sale
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <div class="text-sm text-green-600 mb-1"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <div>
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                    <span class="text-lg font-bold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                    <span class="text-sm text-gray-500 line-through ml-2">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                    <span class="text-lg font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?id=<?php echo $product['id']; ?>" 
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-start mb-4">
            <h2 id="modal-title" class="text-2xl font-semibold"></h2>
            <button onclick="closeProductModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-content" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Product details will be loaded here -->
        </div>
    </div>
</div>

<script>
let products = [];
let categories = [];
let currentCategory = null;

// Show error message
function showError(message) {
    const errorElement = document.getElementById('error-message');
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
    document.getElementById('loading').classList.add('hidden');
}

// Get image URL
function getImageUrl(image) {
    if (!image) return '/AgroFarm/assets/images/products/default-product.jpg';
    return `/AgroFarm/assets/images/products/${image}`;
}

// Fetch products and categories
async function fetchData() {
    try {
        console.log("Fetching products from API...");
        // Show loading indicator
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('error-message').classList.add('hidden');
        
        const response = await fetch('/AgroFarm/api/products.php');
        console.log("API response status:", response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("API data received:", data);
        
        if (data.success) {
            products = data.data.products;
            categories = data.data.categories;
            
            console.log(`Loaded ${products.length} products and ${categories.length} categories`);
            
            // Check URL for category parameter
            const urlParams = new URLSearchParams(window.location.search);
            const categoryParam = urlParams.get('category');
            if (categoryParam) {
                currentCategory = categoryParam;
                console.log("Setting initial category from URL:", currentCategory);
            }
            
            renderCategories();
            renderProducts();
            document.getElementById('loading').classList.add('hidden');
        } else {
            throw new Error(data.message || 'Failed to fetch data');
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        document.getElementById('loading').classList.add('hidden');
        showError('Failed to load products. Please try again later. Error: ' + error.message);
        
        // Try loading the server-side rendered products if available
        const serverProductsGrid = document.querySelector('.server-products-grid');
        if (serverProductsGrid) {
            console.log("Falling back to server-rendered products");
            document.getElementById('products-grid').innerHTML = serverProductsGrid.innerHTML;
        }
    }
}

// Render categories
function renderCategories() {
    const categoriesList = document.getElementById('categories-list');
    categoriesList.innerHTML = `
        <button onclick="filterByCategory(null)" 
                class="w-full text-left px-4 py-2 rounded ${!currentCategory ? 'bg-green-100 text-green-800' : 'hover:bg-gray-100'}">
            All Categories
        </button>
    `;
    
    categories.forEach(category => {
        categoriesList.innerHTML += `
            <button onclick="filterByCategory('${category.slug}')" 
                    class="w-full text-left px-4 py-2 rounded ${currentCategory === category.slug ? 'bg-green-100 text-green-800' : 'hover:bg-gray-100'}">
                ${category.name}
            </button>
        `;
    });
}

// Render products
function renderProducts() {
    const productsGrid = document.getElementById('products-grid');
    let filteredProducts = products;
    
    // Debug product categories
    console.log("All products:", products.map(p => ({ id: p.id, name: p.name, category: p.category_slug })));
    
    if (currentCategory) {
        console.log("Filtering by category:", currentCategory);
        filteredProducts = products.filter(p => p.category_slug === currentCategory);
        console.log("Filtered products:", filteredProducts.length);
    }
    
    if (filteredProducts.length === 0) {
        productsGrid.innerHTML = `
            <div class="col-span-full text-center py-8">
                <p class="text-gray-600">No products found in this category.</p>
            </div>
        `;
        return;
    }
    
    productsGrid.innerHTML = filteredProducts.map(product => `
        <div class="bg-white rounded-lg shadow-md overflow-hidden" data-category="${product.category_slug || ''}">
            <div class="relative h-48">
                <img src="${getImageUrl(product.image)}" 
                     alt="${product.name}" 
                     class="w-full h-full object-cover"
                     onerror="this.src='/AgroFarm/assets/images/products/default-product.jpg'">
                ${product.sale_price ? `
                    <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm">
                        Sale
                    </div>
                ` : ''}
            </div>
            <div class="p-4">
                <div class="text-sm text-green-600 mb-1">${product.category_name || 'Uncategorized'}</div>
                <h3 class="text-lg font-semibold mb-2">${product.name}</h3>
                <p class="text-gray-600 mb-2">${product.description}</p>
                <div class="flex justify-between items-center">
                    <div>
                        ${product.sale_price && product.sale_price < product.price ? `
                            <span class="text-lg font-bold text-green-600">$${product.sale_price}</span>
                            <span class="text-sm text-gray-500 line-through ml-2">$${product.price}</span>
                        ` : `
                            <span class="text-lg font-bold text-green-600">$${product.price}</span>
                        `}
                    </div>
                    <button onclick="viewProduct(${product.id})" 
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Filter products by category
function filterByCategory(categorySlug) {
    currentCategory = categorySlug;
    
    // Update URL with category parameter
    if (categorySlug) {
        const url = new URL(window.location);
        url.searchParams.set('category', categorySlug);
        window.history.pushState({}, '', url);
    } else {
        const url = new URL(window.location);
        url.searchParams.delete('category');
        window.history.pushState({}, '', url);
    }
    
    renderCategories();
    renderProducts();
}

// View product details
function viewProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const modal = document.getElementById('product-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');

    modalTitle.textContent = product.name;
    modalContent.innerHTML = `
        <div>
            <img src="${getImageUrl(product.image)}" 
                 alt="${product.name}" 
                 class="w-full h-64 object-cover rounded"
                 onerror="this.src='/AgroFarm/assets/images/products/default-product.jpg'">
            ${product.sale_price ? `
                <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm">
                    Sale
                </div>
            ` : ''}
            <button onclick="toggleWishlist(${product.id})" 
                    class="absolute top-2 left-2 bg-white p-2 rounded-full hover:bg-red-50 transition-colors"
                    id="wishlist-btn-${product.id}">
                <i class="far fa-heart text-red-500"></i>
            </button>
        </div>
        <div>
            <p class="text-gray-600 mb-4">${product.description}</p>
            <div class="mb-4">
                ${product.sale_price && product.sale_price < product.price ? `
                    <span class="text-2xl font-bold text-green-600">$${product.sale_price}</span>
                    <span class="text-lg text-gray-500 line-through ml-2">$${product.price}</span>
                ` : `
                    <span class="text-2xl font-bold text-green-600">$${product.price}</span>
                `}
            </div>
            <div class="mb-4">
                <span class="font-semibold">Category:</span> ${product.category_name || 'Uncategorized'}
            </div>
            <div class="mb-4">
                <span class="font-semibold">Stock:</span> ${product.stock} units
            </div>
            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <div class="flex items-center">
                    <button onclick="updateQuantity(${product.id}, 'decrease')" 
                            class="bg-gray-200 text-gray-600 px-3 py-1 rounded-l hover:bg-gray-300 focus:outline-none">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                           id="quantity-${product.id}" 
                           value="1" 
                           min="1" 
                           max="${product.stock}" 
                           class="w-20 text-center border-t border-b border-gray-300 py-1 px-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           onchange="validateQuantity(${product.id}, ${product.stock})">
                    <button onclick="updateQuantity(${product.id}, 'increase')" 
                            class="bg-gray-200 text-gray-600 px-3 py-1 rounded-r hover:bg-gray-300 focus:outline-none">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <p id="quantity-error-${product.id}" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
            <div class="flex gap-4">
                <button onclick="addToCart(${product.id})" 
                        class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    Add to Cart
                </button>
                <button onclick="toggleWishlist(${product.id})" 
                        class="bg-white border border-red-500 text-red-500 px-6 py-3 rounded-lg hover:bg-red-50"
                        id="wishlist-btn-large-${product.id}">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Check if product is in wishlist
    checkWishlistStatus(productId);
}

// Check wishlist status
async function checkWishlistStatus(productId) {
    try {
        const response = await fetch('/AgroFarm/api/wishlist.php');
        const data = await response.json();
        
        if (data.success) {
            const isInWishlist = data.data.some(item => item.product_id === productId);
            updateWishlistButton(productId, isInWishlist);
        }
    } catch (error) {
        console.error('Error checking wishlist status:', error);
    }
}

// Update wishlist button appearance
function updateWishlistButton(productId, isInWishlist) {
    const buttons = [
        document.getElementById(`wishlist-btn-${productId}`),
        document.getElementById(`wishlist-btn-large-${productId}`)
    ];
    
    buttons.forEach(button => {
        if (button) {
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = isInWishlist ? 'fas fa-heart text-red-500' : 'far fa-heart text-red-500';
            }
        }
    });
}

// Toggle wishlist
async function toggleWishlist(productId) {
    try {
        const response = await fetch('/AgroFarm/api/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId
            })
        });

        const data = await response.json();
        
            if (data.success) {
                // Show success message
            const modal = document.getElementById('product-modal');
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            successMessage.textContent = data.message;
            document.body.appendChild(successMessage);
            
            // Remove success message after 3 seconds
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
            
            // Update button appearance
            checkWishlistStatus(productId);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
        alert('An error occurred while updating wishlist. Please try again.');
    }
}

// Update quantity
function updateQuantity(productId, action) {
    const input = document.getElementById(`quantity-${productId}`);
    const product = products.find(p => p.id === productId);
    if (!product) return;

    let newValue = parseInt(input.value);
    if (action === 'increase') {
        newValue = Math.min(newValue + 1, product.stock);
            } else {
        newValue = Math.max(newValue - 1, 1);
    }
    
    input.value = newValue;
    validateQuantity(productId, product.stock);
}

// Validate quantity
function validateQuantity(productId, maxStock) {
    const input = document.getElementById(`quantity-${productId}`);
    const errorElement = document.getElementById(`quantity-error-${productId}`);
    const value = parseInt(input.value);
    
    if (isNaN(value) || value < 1) {
        input.value = 1;
        errorElement.textContent = 'Quantity must be at least 1';
        errorElement.classList.remove('hidden');
        return false;
    }
    
    if (value > maxStock) {
        input.value = maxStock;
        errorElement.textContent = `Only ${maxStock} items available in stock`;
        errorElement.classList.remove('hidden');
        return false;
    }
    
    errorElement.classList.add('hidden');
    return true;
}

// Add to cart functionality
async function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    const quantity = parseInt(document.getElementById(`quantity-${productId}`).value);
    if (!validateQuantity(productId, product.stock)) return;
    
    try {
        const response = await fetch('/AgroFarm/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Show success message
            const modal = document.getElementById('product-modal');
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            successMessage.textContent = data.message;
            document.body.appendChild(successMessage);
            
            // Remove success message after 3 seconds
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
            
            // Close the modal
            closeProductModal();
        } else {
            // Show error message
            const errorElement = document.getElementById(`quantity-error-${productId}`);
            errorElement.textContent = data.message;
            errorElement.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        const errorElement = document.getElementById(`quantity-error-${productId}`);
        errorElement.textContent = 'An error occurred while adding to cart. Please try again.';
        errorElement.classList.remove('hidden');
    }
}

// Close product modal
function closeProductModal() {
    const modal = document.getElementById('product-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Initialize
document.addEventListener('DOMContentLoaded', fetchData);

// Close modal when clicking outside
document.getElementById('product-modal').addEventListener('click', (e) => {
    if (e.target.id === 'product-modal') {
        closeProductModal();
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>