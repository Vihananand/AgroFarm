<?php
$page_title = "My Orders";
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to view your orders";
    header("Location: " . SITE_URL . "/pages/login.php");
    exit();
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    header("Location: " . SITE_URL . "/admin/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get all orders for the current user with order items
    $stmt = $conn->prepare("
        SELECT o.*, 
               COUNT(oi.id) as total_items,
               SUM(oi.quantity * oi.price) as total_amount
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching your orders";
    $orders = [];
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-600">Track and manage your orders</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-8">
            <p class="text-gray-600 text-lg">You haven't placed any orders yet.</p>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                    <li>
                        <div class="px-4 py-5 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Order #<?php echo $order['id']; ?>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">
                                        $<?php echo number_format($order['total_amount'], 2); ?>
                                    </p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        <?php echo $order['total_items']; ?> items
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    <?php
                                    switch($order['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'processing':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'completed':
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
                            <div class="mt-4">
                                <a href="<?php echo SITE_URL; ?>/user/order-details.php?id=<?php echo $order['id']; ?>" 
                                   class="text-green-600 hover:text-green-700">
                                    View Order Details →
                                </a>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 