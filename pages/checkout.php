<?php
$page_title = "Checkout";
$page_description = "Complete your purchase at AgroFarm.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Save current URL to redirect back after login
    $redirect_url = SITE_URL . '/pages/checkout.php';
    redirect(SITE_URL . '/pages/login.php?redirect=' . urlencode($redirect_url));
}

// Get cart items and verify there are items to checkout
try {
    $stmt = $conn->prepare("
        SELECT c.id as cart_id, c.quantity, p.*, c.quantity * COALESCE(p.sale_price, p.price) as item_total 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([getUserId()]);
    $cart_items = $stmt->fetchAll();
    
    // If cart is empty, redirect to cart page
    if (empty($cart_items)) {
        setFlashMessage('error', 'Your cart is empty. Please add items before checkout.');
        redirect(SITE_URL . '/pages/cart.php');
    }
    
    // Calculate cart totals
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
    }
    
    // Calculate shipping cost (simplified for example)
    $shipping_cost = $subtotal > 100 ? 0 : 15;
    
    // Calculate tax (simplified for example)
    $tax_rate = 0.08; // 8%
    $tax = $subtotal * $tax_rate;
    
    // Calculate total
    $total = $subtotal + $shipping_cost + $tax;
    
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to retrieve cart items.');
    redirect(SITE_URL . '/pages/cart.php');
}

// Get user information
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([getUserId()]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = [];
}

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_first_name = $_POST['shipping_first_name'] ?? '';
    $shipping_last_name = $_POST['shipping_last_name'] ?? '';
    $shipping_email = $_POST['shipping_email'] ?? '';
    $shipping_phone = $_POST['shipping_phone'] ?? '';
    $shipping_address = $_POST['shipping_address'] ?? '';
    $shipping_city = $_POST['shipping_city'] ?? '';
    $shipping_state = $_POST['shipping_state'] ?? '';
    $shipping_zip = $_POST['shipping_zip'] ?? '';
    $shipping_country = $_POST['shipping_country'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Basic validation
    $errors = [];
    
    if (empty($shipping_first_name)) $errors[] = 'First name is required.';
    if (empty($shipping_last_name)) $errors[] = 'Last name is required.';
    if (empty($shipping_email)) $errors[] = 'Email is required.';
    if (empty($shipping_phone)) $errors[] = 'Phone number is required.';
    if (empty($shipping_address)) $errors[] = 'Address is required.';
    if (empty($shipping_city)) $errors[] = 'City is required.';
    if (empty($shipping_state)) $errors[] = 'State is required.';
    if (empty($shipping_zip)) $errors[] = 'ZIP code is required.';
    if (empty($shipping_country)) $errors[] = 'Country is required.';
    if (empty($payment_method)) $errors[] = 'Payment method is required.';
    
    // If no errors, process the order
    if (empty($errors)) {
        try {
            // Format full shipping address
            $full_shipping_address = $shipping_address . ', ' . $shipping_city . ', ' . $shipping_state . ' ' . $shipping_zip . ', ' . $shipping_country;
            
            // Begin transaction
            $conn->beginTransaction();
            
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    user_id, total_amount, shipping_address, shipping_phone, shipping_email,
                    status, payment_method, payment_status
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, 'pending')
            ");
            
            $stmt->execute([
                getUserId(),
                $total,
                $full_shipping_address,
                $shipping_phone,
                $shipping_email,
                $payment_method
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $item) {
                $item_price = $item['sale_price'] ?? $item['price'];
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item_price
                ]);
                
                // Update product stock
                $new_stock = $item['stock'] - $item['quantity'];
                $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update_stock->execute([$new_stock, $item['id']]);
            }
            
            // Clear the user's cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([getUserId()]);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order confirmation page
            redirect(SITE_URL . '/pages/order-confirmation.php?order_id=' . $order_id);
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $errors[] = 'An error occurred while processing your order. Please try again.';
        }
    }
}
?>

<!-- Checkout Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b">
                        <h2 class="text-xl font-semibold">Shipping Information</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 text-red-700 p-4 mb-6 rounded-md">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="checkout-form">
                            <div class="grid md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                                <div>
                                    <label for="shipping_first_name" class="block text-gray-700 mb-1">First Name <span class="text-red-600">*</span></label>
                                    <input type="text" id="shipping_first_name" name="shipping_first_name" class="form-input w-full" value="<?php echo $user['first_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_last_name" class="block text-gray-700 mb-1">Last Name <span class="text-red-600">*</span></label>
                                    <input type="text" id="shipping_last_name" name="shipping_last_name" class="form-input w-full" value="<?php echo $user['last_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_email" class="block text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                                    <input type="email" id="shipping_email" name="shipping_email" class="form-input w-full" value="<?php echo $user['email'] ?? ''; ?>" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_phone" class="block text-gray-700 mb-1">Phone <span class="text-red-600">*</span></label>
                                    <input type="tel" id="shipping_phone" name="shipping_phone" class="form-input w-full" value="<?php echo $user['phone'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label for="shipping_address" class="block text-gray-700 mb-1">Address <span class="text-red-600">*</span></label>
                                <input type="text" id="shipping_address" name="shipping_address" class="form-input w-full" value="<?php echo $user['address'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                                <div>
                                    <label for="shipping_city" class="block text-gray-700 mb-1">City <span class="text-red-600">*</span></label>
                                    <input type="text" id="shipping_city" name="shipping_city" class="form-input w-full" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_state" class="block text-gray-700 mb-1">State <span class="text-red-600">*</span></label>
                                    <input type="text" id="shipping_state" name="shipping_state" class="form-input w-full" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_zip" class="block text-gray-700 mb-1">ZIP Code <span class="text-red-600">*</span></label>
                                    <input type="text" id="shipping_zip" name="shipping_zip" class="form-input w-full" required>
                                </div>
                                
                                <div>
                                    <label for="shipping_country" class="block text-gray-700 mb-1">Country <span class="text-red-600">*</span></label>
                                    <select id="shipping_country" name="shipping_country" class="form-select w-full" required>
                                        <option value="">Select Country</option>
                                        <option value="United States">United States</option>
                                        <option value="Canada">Canada</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="France">France</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="border-t pt-6 mt-8">
                                <h3 class="text-lg font-semibold mb-4">Payment Method</h3>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input type="radio" id="payment_credit_card" name="payment_method" value="credit_card" class="h-4 w-4 text-green-600" checked>
                                        <label for="payment_credit_card" class="ml-2">
                                            <span class="font-medium">Credit Card</span>
                                            <div class="flex items-center mt-1">
                                                <i class="fab fa-cc-visa text-blue-600 text-2xl mr-2"></i>
                                                <i class="fab fa-cc-mastercard text-red-500 text-2xl mr-2"></i>
                                                <i class="fab fa-cc-amex text-blue-500 text-2xl mr-2"></i>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="radio" id="payment_paypal" name="payment_method" value="paypal" class="h-4 w-4 text-green-600">
                                        <label for="payment_paypal" class="ml-2">
                                            <span class="font-medium">PayPal</span>
                                            <div class="mt-1">
                                                <i class="fab fa-paypal text-blue-700 text-2xl"></i>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input type="radio" id="payment_bank_transfer" name="payment_method" value="bank_transfer" class="h-4 w-4 text-green-600">
                                        <label for="payment_bank_transfer" class="ml-2">
                                            <span class="font-medium">Bank Transfer</span>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Make your payment directly into our bank account.
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit Card Payment Fields (show/hide based on selection) -->
                            <div id="credit-card-fields" class="mt-6 border-t pt-6">
                                <div class="grid md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                                    <div class="md:col-span-2">
                                        <label for="card_number" class="block text-gray-700 mb-1">Card Number</label>
                                        <input type="text" id="card_number" class="form-input w-full" placeholder="XXXX XXXX XXXX XXXX">
                                    </div>
                                    
                                    <div>
                                        <label for="expiry_date" class="block text-gray-700 mb-1">Expiry Date</label>
                                        <input type="text" id="expiry_date" class="form-input w-full" placeholder="MM/YY">
                                    </div>
                                    
                                    <div>
                                        <label for="cvv" class="block text-gray-700 mb-1">CVV</label>
                                        <input type="text" id="cvv" class="form-input w-full" placeholder="XXX">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label for="name_on_card" class="block text-gray-700 mb-1">Name on Card</label>
                                        <input type="text" id="name_on_card" class="form-input w-full">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8">
                                <button type="submit" class="btn-primary w-full py-3 text-lg">
                                    Complete Order
                                </button>
                                
                                <p class="text-center mt-4 text-sm text-gray-500">
                                    By placing your order, you agree to our <a href="#" class="text-green-600 hover:underline">Terms of Service</a> and <a href="#" class="text-green-600 hover:underline">Privacy Policy</a>.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-8">
                    <div class="p-4 border-b">
                        <h2 class="text-xl font-semibold">Order Summary</h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="max-h-64 overflow-y-auto mb-4">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="flex items-start pb-3 mb-3 border-b last:border-b-0 last:mb-0 last:pb-0">
                                <div class="w-16 h-16 flex-shrink-0 mr-3">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                         alt="<?php echo $item['name']; ?>" 
                                         class="w-full h-full object-cover rounded">
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-medium"><?php echo $item['name']; ?></h4>
                                    <div class="flex justify-between mt-1">
                                        <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="text-sm font-medium">
                                            <?php echo '$' . number_format($item['item_total'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="space-y-3 pt-3 border-t text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span><?php echo '$' . number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <?php if ($shipping_cost > 0): ?>
                                <span><?php echo '$' . number_format($shipping_cost, 2); ?></span>
                                <?php else: ?>
                                <span class="text-green-600">Free</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span><?php echo '$' . number_format($tax, 2); ?></span>
                            </div>
                            
                            <div class="flex justify-between pt-3 border-t text-base font-bold">
                                <span>Total</span>
                                <span><?php echo '$' . number_format($total, 2); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($shipping_cost === 0): ?>
                        <div class="mt-4 bg-green-50 text-green-700 px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-check-circle mr-1"></i> Free shipping applied
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center text-sm text-gray-500">
                            <p>Need help? <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-green-600 hover:underline">Contact us</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Show/hide payment method fields
    document.addEventListener('DOMContentLoaded', function() {
        const creditCardRadio = document.getElementById('payment_credit_card');
        const paypalRadio = document.getElementById('payment_paypal');
        const bankTransferRadio = document.getElementById('payment_bank_transfer');
        const creditCardFields = document.getElementById('credit-card-fields');
        
        function updatePaymentFields() {
            if (creditCardRadio.checked) {
                creditCardFields.style.display = 'block';
            } else {
                creditCardFields.style.display = 'none';
            }
        }
        
        // Set initial state
        updatePaymentFields();
        
        // Add event listeners
        creditCardRadio.addEventListener('change', updatePaymentFields);
        paypalRadio.addEventListener('change', updatePaymentFields);
        bankTransferRadio.addEventListener('change', updatePaymentFields);
    });
</script>

<?php include_once '../includes/footer.php'; ?> 