<?php
$page_title = "Dashboard - AgroFarm";
$page_description = "Your personal dashboard";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access your dashboard');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getCurrentUser();

try {
    // Get user's recent orders
    $stmt = $conn->prepare("
        SELECT o.*, 
               COUNT(oi.id) as total_items,
               SUM(oi.quantity * oi.price) as total_amount
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll();

    // Get user's wishlist items
    $stmt = $conn->prepare("
        SELECT w.*, p.name, p.price, p.image, p.stock
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();

    // Get user's cart items
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "An error occurred while loading your dashboard.";
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - AgroFarm</title>
    
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
    /* ... existing code ... */
    </style>
</head>

<!-- Dashboard Header -->
<section class="bg-green-50 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4">Welcome, <?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>!</h1>
        <p class="text-xl text-center text-gray-600 max-w-2xl mx-auto">
            Manage your account and track your orders
        </p>
    </div>
</section>

<!-- Dashboard Content -->
<section class="py-12">
    <div class="container mx-auto px-4">
        

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Recent Orders</h2>
                <?php if (empty($recent_orders)): ?>
                    <p class="text-gray-500">No orders yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium">Order #<?php echo $order['id']; ?></h3>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full
                                        <?php
                                        switch ($order['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'processing':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'shipped':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'delivered':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    <span><?php echo $order['total_items']; ?> items</span>
                                    <span class="mx-2">•</span>
                                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-green-600 hover:text-green-700 font-medium">
                            View All Orders →
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Wishlist -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Wishlist</h2>
                <?php if (empty($wishlist_items)): ?>
                    <p class="text-gray-500">Your wishlist is empty</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($wishlist_items as $item): ?>
                            <div class="product-card hover:shadow-lg transition-shadow">
                                <div class="relative h-32 mb-2">
                                    <img class="product-image h-full w-full object-cover" data-image="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-sm text-gray-600">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <button onclick="addToCart(<?php echo $item['product_id']; ?>)"
                                        class="text-green-600 hover:text-green-700">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="wishlist.php" class="text-green-600 hover:text-green-700 font-medium">
                            View Full Wishlist →
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Shopping Cart</h2>
                <?php if (empty($cart_items)): ?>
                    <p class="text-gray-500">Your cart is empty</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 h-16 w-16 overflow-hidden">
                                    <img class="product-image h-full w-full object-cover" data-image="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-sm text-gray-600">
                                        $<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="cart.php" class="text-green-600 hover:text-green-700 font-medium">
                            View Cart →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Function to get the correct image URL
    function getImageUrl(image) {
        if (!image) return '<?= SITE_URL ?>/assets/images/default-product.jpg';
        
        if (image.startsWith('http://') || image.startsWith('https://')) {
            return image;
        } else if (image.startsWith('/')) {
            return '<?= SITE_URL ?>' + image;
        } else {
            return '<?= SITE_URL ?>/assets/images/products/' + image;
        }
    }
    
    // Load images when the page is ready
    document.addEventListener('DOMContentLoaded', function() {
        const productImages = document.querySelectorAll('.product-image');
        
        productImages.forEach(function(img) {
            const imagePath = img.getAttribute('data-image');
            img.src = getImageUrl(imagePath);
            
            img.onerror = function() {
                this.src = '<?= SITE_URL ?>/assets/images/default-product.jpg';
                this.onerror = null;
            }
        });
    });
    
    // Function to add product to cart
    function addToCart(productId) {
        $.ajax({
            url: '<?= SITE_URL ?>/api/cart.php',
            type: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product added to cart',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error message with SweetAlert
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to add product to cart',
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    console.error('Error parsing response:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to connect to the server. Please try again later.',
                    icon: 'error'
                });
            }
        });
    }
</script>

<?php include_once '../includes/footer.php'; ?> 