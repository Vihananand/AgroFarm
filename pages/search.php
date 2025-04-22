<?php
$page_title = "Search Results - AgroFarm";
$page_description = "Search results for products at AgroFarm";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

// Get search query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch products if search query exists
$products = [];
if (!empty($search)) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE :search 
            OR p.description LIKE :search 
            OR c.name LIKE :search
            ORDER BY p.name ASC
        ");
        
        $search_term = "%{$search}%";
        $stmt->bindParam(':search', $search_term);
        $stmt->execute();
        $products = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error searching products: " . $e->getMessage());
        $error = "An error occurred while searching. Please try again.";
    }
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Search Results Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php if (!empty($search)): ?>
                        Search Results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Search Products
                    <?php endif; ?>
                </h1>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($search)): ?>
                <?php if (empty($products)): ?>
                    <div class="text-center py-12">
                        <div class="mb-4">
                            <i class="fas fa-search text-gray-400 text-5xl"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-2">No products found</h2>
                        <p class="text-gray-600 mb-6">
                            We couldn't find any products matching your search "<?php echo htmlspecialchars($search); ?>".
                        </p>
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="inline-flex items-center text-green-600 hover:text-green-700">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Browse all products
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                                <a href="<?php echo SITE_URL; ?>/pages/product.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-full h-48 object-cover">
                                </a>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-2">
                                        <a href="<?php echo SITE_URL; ?>/pages/product.php?id=<?php echo $product['id']; ?>" 
                                           class="text-gray-800 hover:text-green-600">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    <div class="text-sm text-gray-600 mb-2">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="text-green-600 font-semibold">
                                            $<?php echo number_format($product['price'], 2); ?>
                                        </div>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors duration-300">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="mb-4">
                        <i class="fas fa-search text-gray-400 text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-2">Start your search</h2>
                    <p class="text-gray-600 mb-6">
                        Use the search bar above to find products
                    </p>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="inline-flex items-center text-green-600 hover:text-green-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Browse all products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
async function addToCart(productId) {
    try {
        const response = await fetch(`${SITE_URL}/includes/ajax/add_to_cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            alert('Product added to cart successfully!');
            // Optionally update cart count in navbar
            if (document.querySelector('.cart-count')) {
                document.querySelector('.cart-count').textContent = data.cart_count;
            }
        } else {
            alert(data.message || 'Failed to add product to cart');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding the product to cart');
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>