<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Admin Dashboard - AgroFarm";
$page_description = "Admin dashboard for managing AgroFarm";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/header.php';

// Initialize variables to prevent undefined warnings
$total_products = 0;
$total_orders = 0;
$total_customers = 0;
$total_revenue = 0;
$recent_orders = [];
$low_stock_products = [];
$error_message = '';

try {
    // Check if the necessary tables exist
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tablesMissing = [];
    
    // Check for required tables
    if (!in_array('products', $tables)) {
        $tablesMissing[] = 'products';
    }
    if (!in_array('orders', $tables)) {
        $tablesMissing[] = 'orders';
    }
    if (!in_array('users', $tables)) {
        $tablesMissing[] = 'users';
    }
    
    if (!empty($tablesMissing)) {
        throw new Exception("Required tables missing: " . implode(", ", $tablesMissing));
    }
    
    // Total products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Total customers - try with different column names for role
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE user_role = 'customer'");
        $total_customers = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Try with 'role' column instead
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $total_customers = $stmt->fetchColumn();
    }

    // Total revenue - try with different column names for status
    try {
        $stmt = $conn->query("SELECT SUM(total_amount) FROM orders");
        $total_revenue = $stmt->fetchColumn();
        // Handle null or false return from fetchColumn
        if ($total_revenue === null || $total_revenue === false) {
            $total_revenue = 0;
        }
        error_log("Total revenue: " . $total_revenue);
    } catch (PDOException $e) {
        error_log("Revenue calculation error: " . $e->getMessage());
        $total_revenue = 0;
    }

    // Recent orders
    $stmt = $conn->query("
        SELECT o.*, u.email, u.first_name, u.last_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll();

    // Low stock products
    $stmt = $conn->query("
        SELECT * FROM products 
        WHERE stock < 10 
        ORDER BY stock ASC 
        LIMIT 5
    ");
    $low_stock_products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard database error: " . $e->getMessage());
    $error_message = "A database error occurred while loading the dashboard. Please check your database connection and structure.";
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "An error occurred while loading the dashboard: " . $e->getMessage();
}
?>

<div class="min-h-screen bg-gray-100">
    <!-- Admin Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div id="error-message" class="<?php echo empty($error_message) ? 'hidden' : ''; ?> bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $error_message; ?></span>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Products -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                                <dd id="total-products" class="text-3xl font-semibold text-gray-900"><?php echo $total_products; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="products.php" class="text-sm font-medium text-green-600 hover:text-green-500">Manage products</a>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                <dd id="total-orders" class="text-3xl font-semibold text-gray-900"><?php echo $total_orders; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="orders.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">View orders</a>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Customers</dt>
                                <dd id="total-customers" class="text-3xl font-semibold text-gray-900"><?php echo $total_customers; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="customers.php" class="text-sm font-medium text-purple-600 hover:text-purple-500">View customers</a>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                <dd id="total-revenue" class="text-3xl font-semibold text-gray-900">$<?php echo number_format($total_revenue, 2); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="reports.php" class="text-sm font-medium text-yellow-600 hover:text-yellow-500">View reports</a>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Orders</h3>
                    <a href="orders.php" class="text-sm text-blue-600 hover:text-blue-500">View all</a>
                </div>
                <div class="border-t border-gray-200">
                    <div id="recent-orders-loading" class="px-4 py-5 sm:p-6 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-2 text-gray-500">Loading recent orders...</p>
                    </div>
                    <div id="recent-orders-empty" class="px-4 py-5 sm:p-6 text-center text-gray-500 hidden">
                        No orders yet.
                    </div>
                    <div id="recent-orders-table" class="overflow-x-auto hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders-body" class="bg-white divide-y divide-gray-200">
                                <!-- Orders will be loaded here via JavaScript -->
                                
                                <!-- Server-side fallback for recent orders -->
                                <?php if (!empty($recent_orders)) { ?>
                                    <?php foreach ($recent_orders as $order) { ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $order['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php 
                                                $customer_name = '';
                                                if (!empty($order['first_name'])) {
                                                    $customer_name .= $order['first_name'];
                                                }
                                                if (!empty($order['last_name'])) {
                                                    $customer_name .= ' ' . $order['last_name'];
                                                }
                                                echo !empty($customer_name) ? htmlspecialchars($customer_name) : 'Unknown';
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                $<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php 
                                                $status = $order['status'] ?? $order['payment_status'] ?? 'unknown';
                                                $statusClass = '';
                                                switch(strtolower($status)) {
                                                    case 'pending':
                                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'cancelled':
                                                    case 'canceled':
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                }
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Low Stock Products</h3>
                    <a href="products.php" class="text-sm text-green-600 hover:text-green-500">View all products</a>
                </div>
                <div class="border-t border-gray-200">
                    <div id="low-stock-loading" class="px-4 py-5 sm:p-6 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto"></div>
                        <p class="mt-2 text-gray-500">Loading low stock products...</p>
                    </div>
                    <div id="low-stock-empty" class="px-4 py-5 sm:p-6 text-center text-gray-500 hidden">
                        No products with low stock.
                    </div>
                    <div id="low-stock-table" class="overflow-x-auto hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="low-stock-body" class="bg-white divide-y divide-gray-200">
                                <!-- Low stock products will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Helper function to correctly format image URLs
function getImageUrl(image) {
    if (!image) return '<?= SITE_URL ?>/assets/images/products/default-product.jpg';
    
    if (image.startsWith('http://') || image.startsWith('https://')) {
        return image;
    } else if (image.startsWith('/')) {
        return '<?= SITE_URL ?>' + image;
    } else {
        return '<?= SITE_URL ?>/assets/images/products/' + image;
    }
}

// Load recent orders
async function loadRecentOrders() {
    try {
        console.log("Fetching recent orders...");
        document.getElementById('recent-orders-loading').classList.remove('hidden');
        document.getElementById('recent-orders-empty').classList.add('hidden');
        document.getElementById('recent-orders-table').classList.add('hidden');
        
        // Make sure we're using the admin orders API
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/orders.php?limit=5');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Response is not JSON. Check server logs for PHP errors.");
        }
        
        const data = await response.json();
        console.log("Orders API response:", data);
        
        // Debug output - check what data we're getting
        if (data.debug) {
            console.log("API Debug Info:", data.debug);
            
            // Show detailed info about the orders array
            if (data.orders) {
                console.log("Orders array:", data.orders);
                console.log("Orders array type:", typeof data.orders);
                console.log("Orders array length:", data.orders.length);
            } else {
                console.log("No orders array in response");
            }
        }
        
        const loadingElement = document.getElementById('recent-orders-loading');
        const emptyElement = document.getElementById('recent-orders-empty');
        const tableElement = document.getElementById('recent-orders-table');
        const tableBody = document.getElementById('recent-orders-body');
        
        loadingElement.classList.add('hidden');
        
        if (!data.success || !data.orders || data.orders.length === 0) {
            emptyElement.classList.remove('hidden');
            emptyElement.textContent = data.message || 'No orders found.';
            return;
        }
        
        tableElement.classList.remove('hidden');
        tableBody.innerHTML = '';
        
        data.orders.forEach(order => {
            let statusClass = '';
            const status = order.status || 'pending';
            
            switch(status.toLowerCase()) {
                case 'pending':
                    statusClass = 'bg-yellow-100 text-yellow-800';
                    break;
                case 'processing':
                    statusClass = 'bg-blue-100 text-blue-800';
                    break;
                case 'completed':
                    statusClass = 'bg-green-100 text-green-800';
                    break;
                case 'cancelled':
                case 'canceled':
                    statusClass = 'bg-red-100 text-red-800';
                    break;
                default:
                    statusClass = 'bg-gray-100 text-gray-800';
            }
            
            // Format created_at date
            let formattedDate = 'N/A';
            if (order.created_at) {
                const date = new Date(order.created_at);
                if (!isNaN(date)) {
                    formattedDate = date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            }
            
            // Format total amount
            const amount = parseFloat(order.total_amount);
            const formattedAmount = !isNaN(amount) ? amount.toFixed(2) : '0.00';
            
            // Create the table row
            tableBody.innerHTML += `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#${order.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${order.customer_name || 'Unknown'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${formattedAmount}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${status.charAt(0).toUpperCase() + status.slice(1).toLowerCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formattedDate}</td>
                </tr>
            `;
        });
    } catch (error) {
        console.error('Error loading recent orders:', error);
        const errorMsg = document.getElementById('recent-orders-empty');
        document.getElementById('recent-orders-loading').classList.add('hidden');
        document.getElementById('recent-orders-table').classList.add('hidden');
        errorMsg.classList.remove('hidden');
        errorMsg.innerHTML = `<div class="text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>Error loading orders: ${error.message}</div>
                             <div class="mt-2"><button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="loadRecentOrders()">Try Again</button></div>`;
    }
}

// Load low stock products
async function loadLowStockProducts() {
    try {
        console.log("Fetching low stock products...");
        document.getElementById('low-stock-loading').classList.remove('hidden');
        document.getElementById('low-stock-empty').classList.add('hidden');
        document.getElementById('low-stock-table').classList.add('hidden');
        
        const response = await fetch('<?php echo SITE_URL; ?>/api/dashboard_stats.php?action=low_stock_products');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Response is not JSON. Check server logs for PHP errors.");
        }
        
        const data = await response.json();
        console.log("Low stock products response:", data);
        
        const loadingElement = document.getElementById('low-stock-loading');
        const emptyElement = document.getElementById('low-stock-empty');
        const tableElement = document.getElementById('low-stock-table');
        
        loadingElement.classList.add('hidden');
        
        if (!data.success || !data.products || data.products.length === 0) {
            emptyElement.classList.remove('hidden');
            emptyElement.textContent = data.message || 'No products with low stock.';
            return;
        }
        
        tableElement.classList.remove('hidden');
        const tableBody = document.getElementById('low-stock-body');
        tableBody.innerHTML = '';
        
        data.products.forEach(product => {
            const row = document.createElement('tr');
            
            const productNameCell = document.createElement('td');
            productNameCell.className = 'px-6 py-4 whitespace-nowrap';
            
            // Create a flex container for product image and name
            const productContainer = document.createElement('div');
            productContainer.className = 'flex items-center';
            
            // Add product image
            const imgWrapper = document.createElement('div');
            imgWrapper.className = 'flex-shrink-0 h-10 w-10';
            
            const img = document.createElement('img');
            img.className = 'h-10 w-10 rounded-full object-cover product-image';
            img.setAttribute('data-image', product.image || '');
            img.alt = product.name || 'Product';
            img.onerror = function() {
                this.src = '<?= SITE_URL ?>/assets/images/products/default-product.jpg';
                this.onerror = null;
            };
            
            imgWrapper.appendChild(img);
            productContainer.appendChild(imgWrapper);
            
            // Add product name
            const nameSpan = document.createElement('span');
            nameSpan.className = 'ml-4 text-sm font-medium text-gray-900';
            nameSpan.textContent = product.name || 'Unnamed Product';
            productContainer.appendChild(nameSpan);
            
            productNameCell.appendChild(productContainer);
            row.appendChild(productNameCell);
            
            // Stock level
            const stockCell = document.createElement('td');
            stockCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
            stockCell.textContent = product.stock || '0';
            row.appendChild(stockCell);
            
            // Price
            const priceCell = document.createElement('td');
            priceCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
            priceCell.textContent = `$${parseFloat(product.price || 0).toFixed(2)}`;
            row.appendChild(priceCell);
            
            // Action button
            const actionCell = document.createElement('td');
            actionCell.className = 'px-6 py-4 whitespace-nowrap text-sm font-medium';
            
            const actionLink = document.createElement('a');
            actionLink.href = `<?php echo SITE_URL; ?>/admin/product-edit.php?id=${product.id}`;
            actionLink.className = 'text-blue-600 hover:text-blue-900';
            actionLink.textContent = 'Update';
            
            actionCell.appendChild(actionLink);
            row.appendChild(actionCell);
            
            tableBody.appendChild(row);
        });

        // After adding all products to the table, initialize the images
        const productImages = document.querySelectorAll('.product-image');
        productImages.forEach(function(img) {
            const imagePath = img.getAttribute('data-image');
            if (imagePath) {
                img.src = getImageUrl(imagePath);
                
                // Make sure we have error handling
                if (!img.onerror) {
                    img.onerror = function() {
                        this.src = '<?= SITE_URL ?>/assets/images/products/default-product.jpg';
                        this.onerror = null;
                    };
                }
            }
        });
    } catch (error) {
        console.error('Error fetching low stock products:', error);
        const errorMsg = document.getElementById('low-stock-empty');
        document.getElementById('low-stock-loading').classList.add('hidden');
        document.getElementById('low-stock-table').classList.add('hidden');
        errorMsg.classList.remove('hidden');
        errorMsg.innerHTML = `<div class="text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>Error loading low stock products: ${error.message}</div>
                             <div class="mt-2"><button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" onclick="loadLowStockProducts()">Try Again</button></div>`;
    }
}

// Load dashboard stats
async function loadDashboardStats() {
    try {
        console.log("Fetching dashboard statistics...");
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/dashboard-stats.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("Dashboard stats API response:", data);
        
        if (data.success) {
            // Update total products
            if (data.total_products !== undefined) {
                document.getElementById('total-products').textContent = data.total_products;
            }
            
            // Update total orders
            if (data.total_orders !== undefined) {
                document.getElementById('total-orders').textContent = data.total_orders;
            }
            
            // Update total customers
            if (data.total_customers !== undefined) {
                document.getElementById('total-customers').textContent = data.total_customers;
            }
            
            // Update total revenue with more detailed logging
            if (data.total_revenue !== undefined) {
                console.log("Revenue data received:", data.total_revenue, "Type:", typeof data.total_revenue);
                let revenue = 0;
                
                // Handle different formats that could come from API
                if (typeof data.total_revenue === 'string') {
                    revenue = parseFloat(data.total_revenue);
                } else if (typeof data.total_revenue === 'number') {
                    revenue = data.total_revenue;
                }
                
                console.log("Parsed revenue:", revenue);
                document.getElementById('total-revenue').textContent = '$' + (isNaN(revenue) ? '0.00' : revenue.toFixed(2));
            } else {
                console.log("Total revenue not received from API");
                // Use the server-side rendered value which is already on the page
            }
            
            // If there's debug info, log it
            if (data.debug) {
                console.log("API Debug Info:", data.debug);
            }
        } else {
            console.error('API error:', data.message);
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Load all product images
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(function(img) {
        const imagePath = img.getAttribute('data-image');
        img.src = getImageUrl(imagePath);
        
        img.onerror = function() {
            this.src = '<?= SITE_URL ?>/assets/images/products/default-product.jpg';
            this.onerror = null;
        }
    });

    // Check if orders table already has content (server-side rendered)
    const ordersTableBody = document.getElementById('recent-orders-body');
    if (ordersTableBody.children.length > 0) {
        console.log("Found server-rendered order rows, displaying them");
        document.getElementById('recent-orders-loading').classList.add('hidden');
        document.getElementById('recent-orders-table').classList.remove('hidden');
    } else {
        loadRecentOrders();
    }
    
    loadLowStockProducts();
    loadDashboardStats();
});
</script>

<?php include_once '../includes/footer.php'; ?> 