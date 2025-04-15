<?php
$page_title = "Order Confirmation";
$page_description = "Thank you for your order at AgroFarm.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';


if (!isset($_SESSION['order'])) {
    redirect(SITE_URL);
}

$order = $_SESSION['order'];
?>

<!-- Order Confirmation Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-10">
                <div class="inline-block mb-6 p-6 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-5xl text-green-600"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Thank You for Your Order!</h1>
                <p class="text-xl text-gray-600">Your order has been received and is being processed.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold">Order Information</h2>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Confirmed</span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="font-medium mb-2">Order Details</h3>
                            <ul class="text-gray-600 space-y-1">
                                <li><span class="font-medium">Order ID:</span> <?php echo $order['order_id']; ?></li>
                                <li><span class="font-medium">Date:</span> <?php echo date('F j, Y', strtotime($order['date'])); ?></li>
                                <li><span class="font-medium">Payment Method:</span> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="font-medium mb-2">Shipping Address</h3>
                            <p class="text-gray-600">
                                <?php echo $order['shipping_name']; ?><br>
                                <?php echo $order['shipping_address']; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-medium mb-4">Order Summary</h3>
                        
                        <div class="divide-y">
                            <?php foreach ($order['items'] as $item): ?>
                            <div class="flex py-3">
                                <div class="w-16 h-16 flex-shrink-0">
                                    <img src="<?php echo $item['image']; ?>" 
                                         alt="<?php echo $item['name']; ?>" 
                                         class="w-full h-full object-cover rounded">
                                </div>
                                <div class="ml-4 flex-grow">
                                    <h4 class="font-medium"><?php echo $item['name']; ?></h4>
                                    <div class="flex justify-between text-sm text-gray-600 mt-1">
                                        <span>Quantity: <?php echo $item['quantity']; ?></span>
                                        <span>
                                            <?php 
                                            $price = $item['sale_price'] ?? $item['price'];
                                            echo '$' . number_format($price, 2) . ' × ' . $item['quantity']; 
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4 font-medium">
                                    <?php echo '$' . number_format($item['item_total'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="border-t mt-6 pt-6 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span><?php echo '$' . number_format($order['subtotal'], 2); ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <?php if ($order['shipping'] > 0): ?>
                                <span><?php echo '$' . number_format($order['shipping'], 2); ?></span>
                                <?php else: ?>
                                <span class="text-green-600">Free</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span><?php echo '$' . number_format($order['tax'], 2); ?></span>
                            </div>
                            
                            <div class="flex justify-between text-lg font-bold pt-4 border-t">
                                <span>Total</span>
                                <span class="text-green-600"><?php echo '$' . number_format($order['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center space-y-4">
                <p class="text-gray-600">
                    A confirmation email has been sent to your email address.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="<?php echo SITE_URL; ?>" class="btn-secondary">
                        Return to Home
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-primary">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
unset($_SESSION['order']);

include_once '../includes/footer.php'; 
?> 