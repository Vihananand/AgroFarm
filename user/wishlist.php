<?php
$page_title = "My Wishlist - AgroFarm";
$page_description = "Your saved products";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your wishlist');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = null;
$success_message = null;

// Handle remove from wishlist
if (isset($_POST['remove_item']) && isset($_POST['wishlist_id'])) {
    try {
        $wishlist_id = (int)$_POST['wishlist_id'];
        
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->execute([$wishlist_id, $user_id]);
        
        $success_message = "Item removed from wishlist";
    } catch (PDOException $e) {
        error_log("Wishlist error: " . $e->getMessage());
        $error_message = "Failed to remove item from wishlist";
    }
}

// Get wishlist items
try {
    $stmt = $conn->prepare("
        SELECT w.*, p.name, p.price, p.image, p.stock, p.description, p.id as product_id
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Wishlist error: " . $e->getMessage());
    $error_message = "An error occurred while loading your wishlist";
    $wishlist_items = [];
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Wishlist Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">My Wishlist</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Products you've saved for later
        </p>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($wishlist_items)): ?>
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h2 class="text-2xl font-semibold mb-4">Your wishlist is empty</h2>
                <p class="text-gray-600 mb-8">You haven't saved any products to your wishlist yet.</p>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md transition duration-300">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="relative">
                            <img src="#" 
                                 data-image="<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="product-image w-full h-64 object-cover">
                            <?php if ($item['stock'] <= 0): ?>
                                <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                    Out of Stock
                                </div>
                            <?php elseif ($item['stock'] < 5): ?>
                                <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                                    Only <?php echo $item['stock']; ?> left
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 100) . '...'); ?>
                            </p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-2xl font-bold text-green-600">$<?php echo number_format($item['price'], 2); ?></span>
                                
                                <?php if ($item['stock'] > 0): ?>
                                    <button onclick="addToCart(<?php echo $item['product_id']; ?>)" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button disabled class="bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-md cursor-not-allowed">
                                        Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <form method="post" class="text-right">
                                <input type="hidden" name="wishlist_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="remove_item" class="text-red-600 hover:text-red-800 font-medium">
                                    <i class="fas fa-trash mr-1"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Get image URL function, same as in shop.php
function getImageUrl(image) {
    if (!image) return '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
    
    // Check if image is a full URL
    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    }
    
    // Check if image is already a relative path
    if (image.startsWith('/')) {
        return '<?php echo SITE_URL; ?>' + image;
    }
    
    // Otherwise, build the path to images directory
    return '<?php echo SITE_URL; ?>/assets/images/products/' + image;
}

// Load images when page is ready
document.addEventListener('DOMContentLoaded', function() {
    const productImages = document.querySelectorAll('.product-image');
    
    productImages.forEach(img => {
        const imagePath = img.getAttribute('data-image');
        img.setAttribute('src', getImageUrl(imagePath));
        
        // Handle image load errors
        img.onerror = function() {
            this.src = '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
        };
    });
});

function addToCart(productId) {
    fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart successfully');
            // Optionally refresh the page or update a cart counter
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to cart');
    });
}
</script>

<?php include_once '../includes/footer.php'; ?> 