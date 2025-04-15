<?php
$products = [
    [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'description' => 'Premium organic fertilizer for all types of plants. Enriched with natural ingredients to boost plant growth and yield. Safe for all garden plants and vegetables.',
        'image' => 'https://picsum.photos/id/134/600/400',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category' => 'Fertilizers',
        'features' => [
            'Organic and chemical-free',
            'Suitable for all plant types',
            'Improves soil fertility',
            'Enhances plant growth'
        ],
        'related_products' => [3, 5, 7]
    ],
    [
        'id' => 2,
        'name' => 'Premium Garden Hoe',
        'slug' => 'premium-garden-hoe',
        'description' => 'Durable garden hoe with comfortable grip. Made from high-quality stainless steel with a hardwood handle for longevity and comfort during extended use.',
        'image' => 'https://picsum.photos/id/150/600/400',
        'price' => 49.99,
        'sale_price' => null,
        'stock' => 8,
        'category' => 'Equipment',
        'features' => [
            'Stainless steel blade',
            'Comfortable wooden handle',
            'Durable construction',
            'Ideal for weeding and soil preparation'
        ],
        'related_products' => [6, 8]
    ],
    [
        'id' => 3,
        'name' => 'Organic Tomato Seeds',
        'slug' => 'organic-tomato-seeds',
        'description' => 'Heirloom tomato seeds for your garden. Non-GMO and organically grown to ensure the highest quality and taste. Each packet contains approximately 50 seeds.',
        'image' => 'https://picsum.photos/id/145/600/400',
        'price' => 5.99,
        'sale_price' => 4.99,
        'stock' => 50,
        'category' => 'Seeds',
        'features' => [
            'Non-GMO and heirloom variety',
            'High germination rate',
            'Organically grown',
            'Produces large, flavorful tomatoes'
        ],
        'related_products' => [7, 10]
    ],
    [
        'id' => 4,
        'name' => 'Mini Tractor',
        'slug' => 'mini-tractor',
        'description' => 'Compact tractor for small farms and gardens. This fuel-efficient model is perfect for small to medium-sized agricultural operations. Includes a standard hitch system compatible with most attachments.',
        'image' => 'https://picsum.photos/id/167/600/400',
        'price' => 2999.99,
        'sale_price' => 2799.99,
        'stock' => 0,
        'category' => 'Machinery',
        'features' => [
            '25 horsepower diesel engine',
            'Compact design for small spaces',
            'All-terrain capabilities',
            'Compatible with multiple attachments'
        ],
        'related_products' => [9]
    ]
]; 

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$product = null;
foreach ($products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: /');
    exit;
}

$related_products_data = [];
if (!empty($product['related_products'])) {
    foreach ($product['related_products'] as $related_id) {
        foreach ($products as $p) {
            if ($p['id'] == $related_id) {
                $related_products_data[] = $p;
                break;
            }
        }
    }
}

include_once '../includes/header.php';
?>

<main class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded">
        </div>
        
        <!-- Product Details -->
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-muted">Category: <?php echo htmlspecialchars($product['category']); ?></p>
            
            <div class="mb-3">
                <?php if ($product['sale_price']): ?>
                    <span class="text-decoration-line-through text-muted me-2">$<?php echo number_format($product['price'], 2); ?></span>
                    <span class="fs-4 fw-bold text-success">$<?php echo number_format($product['sale_price'], 2); ?></span>
                    <span class="badge bg-danger ms-2">SALE</span>
                <?php else: ?>
                    <span class="fs-4 fw-bold">$<?php echo number_format($product['price'], 2); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <p class="mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
            
            <?php if ($product['stock'] > 0): ?>
                <div class="d-flex mb-4">
                    <input type="number" class="form-control me-2" id="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" style="width: 70px;">
                    <button class="btn btn-success me-2" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('quantity').value)">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-secondary" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                        <i class="bi bi-heart"></i> Add to Wishlist
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product['features'])): ?>
                <div class="mb-4">
                    <h4>Features</h4>
                    <ul class="list-group">
                        <?php foreach ($product['features'] as $feature): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($related_products_data)): ?>
        <div class="mt-5">
            <h3 class="mb-4">Related Products</h3>
            <div class="row">
                <?php foreach ($related_products_data as $related): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($related['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                                <p class="card-text">
                                    <?php if ($related['sale_price']): ?>
                                        <span class="text-decoration-line-through text-muted me-2">$<?php echo number_format($related['price'], 2); ?></span>
                                        <span class="fw-bold text-success">$<?php echo number_format($related['sale_price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold">$<?php echo number_format($related['price'], 2); ?></span>
                                    <?php endif; ?>
                                </p>
                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    function addToCart(productId, quantity) {
        fetch('../includes/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('cart-count').textContent = data.cart_count;
                alert('Product added to cart!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding product to cart');
        });
    }

    function addToWishlist(productId) {
        fetch('../includes/ajax/add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('wishlist-count').textContent = data.wishlist_count;
                alert('Product added to wishlist!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding product to wishlist');
        });
    }
</script>

<?php
include_once '../includes/footer.php';
?> 