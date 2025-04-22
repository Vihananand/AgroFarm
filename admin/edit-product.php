<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$page_title = "Edit Product - Admin Dashboard";

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    setFlashMessage('error', 'Invalid product ID');
    header('Location: ' . SITE_URL . '/admin/products.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['name', 'price', 'stock', 'category_id'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Sanitize and validate input
        $name = sanitize($_POST['name']);
        $slug = createSlug($name);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stock = (int)$_POST['stock'];
        $category_id = (int)$_POST['category_id'];
        $featured = isset($_POST['featured']) ? 1 : 0;

        // Handle image upload
        $image = $_POST['current_image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/products/';
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            // Use original filename or create a new filename
            $new_filename = $_FILES['image']['name'];
            // Or uncomment this to create a unique filename
            // $new_filename = $slug . '-' . uniqid() . '.' . $file_extension;
            
            $target_path = $upload_dir . $new_filename;

            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
            }

            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if exists and different from new one
                if ($_POST['current_image'] && $_POST['current_image'] != $new_filename && file_exists('../assets/images/products/' . $_POST['current_image'])) {
                    unlink('../assets/images/products/' . $_POST['current_image']);
                }
                // Store only the filename in the database
                $image = $new_filename;
            } else {
                throw new Exception('Failed to upload image');
            }
        }

        // Update product in database
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, 
                slug = ?, 
                description = ?, 
                price = ?, 
                sale_price = ?, 
                stock = ?, 
                category_id = ?, 
                image = ?, 
                featured = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $slug,
            $description,
            $price,
            $sale_price,
            $stock,
            $category_id,
            $image,
            $featured,
            $product_id
        ]);

        setFlashMessage('success', 'Product updated successfully');
        header('Location: ' . SITE_URL . '/admin/products.php');
        exit;

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

// Fetch product data
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlashMessage('error', 'Product not found');
        header('Location: ' . SITE_URL . '/admin/products.php');
        exit;
    }

    // Fetch categories for dropdown
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    setFlashMessage('error', 'Error fetching product details');
    header('Location: ' . SITE_URL . '/admin/products.php');
    exit;
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Main Content -->
<main class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Edit Product</h1>
                <a href="<?php echo SITE_URL; ?>/admin/products.php" class="text-gray-600 hover:text-gray-900">
                    Back to Products
                </a>
            </div>

            <?php if ($flash = getFlashMessage()): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <!-- Price and Sale Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   value="<?php echo number_format($product['price'], 2, '.', ''); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label for="sale_price" class="block text-sm font-medium text-gray-700">Sale Price ($)</label>
                            <input type="number" 
                                   id="sale_price" 
                                   name="sale_price" 
                                   value="<?php echo $product['sale_price'] ? number_format($product['sale_price'], 2, '.', '') : ''; ?>" 
                                   step="0.01" 
                                   min="0" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                    </div>

                    <!-- Stock and Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" 
                                   id="stock" 
                                   name="stock" 
                                   value="<?php echo $product['stock']; ?>" 
                                   min="0" 
                                   required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="category_id" 
                                    name="category_id" 
                                    required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] === $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Featured Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="featured" 
                                   value="1" 
                                   <?php echo $product['featured'] ? 'checked' : ''; ?> 
                                   class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                        </label>
                    </div>

                    <!-- Current Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Image</label>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="h-32 w-32 object-cover rounded-lg">
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                        </div>
                    </div>

                    <!-- New Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Upload New Image</label>
                        <input type="file" 
                               id="image" 
                               name="image" 
                               accept="image/jpeg,image/png,image/webp" 
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="mt-1 text-sm text-gray-500">Leave empty to keep current image</p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                            Update Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>