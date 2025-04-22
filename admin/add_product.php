<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Add New Product - AgroFarm";
$page_description = "Add a new product to the AgroFarm store";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /AgroFarm/pages/login.php');
    exit;
}

include_once '../includes/config.php';
include_once '../includes/db_connect.php';
include_once '../includes/header.php';

// Get all categories for the dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching categories: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $stock = intval($_POST['stock']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
            // Use the original filename or generate a new one
            $new_filename = $_FILES['image']['name'];
            // Or uncomment below to generate a unique filename
            // $new_filename = uniqid() . '.' . $file_extension;
            
            $upload_path = $upload_dir . $new_filename;

            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Store only the filename in the database
                $image = $new_filename;
            } else {
                $error_message = "Error uploading image";
            }
        } else {
            $error_message = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        }
    }

    if (!isset($error_message)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (name, slug, description, price, sale_price, stock, image, category_id, featured, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([
                $name,
                $slug,
                $description,
                $price,
                $sale_price,
                $stock,
                $image,
                $category_id,
                $featured
            ]);

            $success_message = "Product added successfully";
            
            // Clear form data
            $name = $description = $price = $sale_price = $stock = '';
            $category_id = null;
            $featured = 0;
        } catch (PDOException $e) {
            $error_message = "Error adding product: " . $e->getMessage();
        }
    }
}
?>

<div class="min-h-screen bg-gray-100">
    <!-- Admin Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
                <a href="products.php" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    Back to Products
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" name="name" id="name" required
                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                        <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required
                               value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Sale Price -->
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700">Sale Price ($)</label>
                        <input type="number" name="sale_price" id="sale_price" step="0.01" min="0"
                               value="<?php echo isset($sale_price) ? htmlspecialchars($sale_price) : ''; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Stock -->
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" id="stock" min="0" required
                               value="<?php echo isset($stock) ? htmlspecialchars($stock) : ''; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <!-- Featured -->
                    <div class="flex items-center">
                        <input type="checkbox" name="featured" id="featured"
                               <?php echo (isset($featured) && $featured) ? 'checked' : ''; ?>
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="featured" class="ml-2 block text-sm text-gray-900">
                            Featured Product
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*" required
                           class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-green-50 file:text-green-700
                                  hover:file:bg-green-100">
                    <p class="mt-1 text-sm text-gray-500">PNG, JPG, JPEG or WEBP (MAX. 2MB)</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?> 