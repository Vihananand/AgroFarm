<?php
$page_title = "Product Details";
include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

$slug = $_GET['slug'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$products = [
    'organic-fertilizer' => [
        'id' => 1,
        'name' => 'Organic Fertilizer',
        'slug' => 'organic-fertilizer',
        'image' => 'https://images.unsplash.com/photo-1605000797499-95a51c5269ae?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&h=400&q=80',
        'price' => 29.99,
        'sale_price' => 24.99,
        'stock' => 15,
        'category_name' => 'Fertilizers',
        'category_slug' => 'fertilizers',
        'description' => 'Our premium organic fertilizer is made from 100% natural ingredients, providing essential nutrients for your plants without harmful chemicals. Ideal for all types of gardens, it improves soil health while promoting vigorous plant growth. Each bag covers approximately 500 square feet of garden space.',
        'specifications' => [
            'Weight' => '5 kg',
            'Composition' => 'Compost, Bone Meal, Seaweed',
            'NPK Ratio' => '4-3-3',
            'Suitable for' => 'All plants and vegetables'
        ]
    ],
    'premium-garden-hoe' => [
        'id' => 2,
        'name' => 'Premium Garden Hoe',
        'slug' => 'premium-garden-hoe',
        'image' => 'https://images.unsplash.com/photo-1585483266669-f9ff856159ad?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 49.99,
        'sale_price' => null,
        'stock' => 8,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'description' => 'This premium garden hoe features an ergonomic design and durable construction for comfortable, long-lasting use. The sharp blade easily cuts through soil and weeds, while the hardwood handle provides excellent grip. Perfect for maintaining garden beds, creating furrows, and general garden maintenance.',
        'specifications' => [
            'Handle Length' => '120 cm',
            'Blade Width' => '15 cm',
            'Material' => 'Stainless Steel, Hardwood Handle',
            'Weight' => '1.2 kg'
        ]
    ],
    'organic-tomato-seeds' => [
        'id' => 3,
        'name' => 'Organic Tomato Seeds',
        'slug' => 'organic-tomato-seeds',
        'image' => 'https://plus.unsplash.com/premium_photo-1724256185670-ab944897f4bc?q=80&w=2574&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 5.99,
        'sale_price' => 4.99,
        'stock' => 50,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'description' => 'Grow delicious, juicy tomatoes with our certified organic seeds. Non-GMO and naturally grown, these seeds offer excellent germination rates and produce disease-resistant plants. Each packet contains approximately 50 seeds, enough to grow a bountiful harvest of flavorful tomatoes for your home garden.',
        'specifications' => [
            'Variety' => 'Beefsteak',
            'Quantity' => '50 seeds per packet',
            'Growing Season' => 'Spring to Summer',
            'Days to Germination' => '5-10 days',
            'Days to Maturity' => '70-80 days'
        ]
    ],
    'mini-tractor' => [
        'id' => 4,
        'name' => 'Mini Tractor',
        'slug' => 'mini-tractor',
        'image' => 'https://images.unsplash.com/photo-1614977645540-7abd88ba8e56?q=80&w=2573&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 2999.99,
        'sale_price' => 2799.99,
        'stock' => 0,
        'category_name' => 'Machinery',
        'category_slug' => 'machinery',
        'description' => 'This compact mini tractor is perfect for small to medium-sized farms and gardens. With 25 horsepower and a versatile attachment system, it handles plowing, tilling, mowing, and more with ease. Fuel-efficient engine and comfortable operator station make long working hours more productive and less fatiguing.',
        'specifications' => [
            'Engine' => '25 HP Diesel',
            'Transmission' => '8-speed gear shift',
            'Dimensions' => '2.3m x 1.2m x 1.5m',
            'Weight' => '580 kg',
            'Fuel Capacity' => '20 liters'
        ]
    ],
    'fresh-apples' => [
        'id' => 5,
        'name' => 'Fresh Apples (5kg)',
        'slug' => 'fresh-apples',
        'image' => 'https://images.unsplash.com/photo-1570913149827-d2ac84ab3f9a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&h=400&q=80',
        'price' => 12.99,
        'sale_price' => null,
        'stock' => 20,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'produce',
        'description' => 'Crisp, juicy apples freshly harvested from organic orchards. These apples are perfect for eating fresh, baking, or making homemade applesauce. No pesticides or chemical fertilizers used.',
        'specifications' => [
            'Weight' => '5 kg',
            'Variety' => 'Mixed (Gala, Honeycrisp, Fuji)',
            'Growing Method' => 'Organic',
            'Storage' => 'Keep refrigerated for maximum freshness',
            'Origin' => 'Local orchards'
        ]
    ],
    'gardening-gloves' => [
        'id' => 6,
        'name' => 'Gardening Gloves',
        'slug' => 'gardening-gloves',
        'image' => 'https://images.unsplash.com/photo-1582586131076-6c308a437385?q=80&w=2680&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 15.99,
        'sale_price' => 12.99,
        'stock' => 30,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'description' => 'Durable and comfortable gardening gloves made from high-quality materials. These gloves provide protection while working in the garden without sacrificing dexterity or comfort.',
        'specifications' => [
            'Material' => 'Synthetic leather, breathable fabric',
            'Sizes Available' => 'S, M, L, XL',
            'Features' => 'Reinforced fingertips, adjustable wrist closure',
            'Care Instructions' => 'Hand wash, air dry',
            'Usage' => 'Gardening, landscaping, light farm work'
        ]
    ],
    'carrot-seeds' => [
        'id' => 7,
        'name' => 'Carrot Seeds',
        'slug' => 'carrot-seeds',
        'image' => 'https://images.unsplash.com/photo-1625243436520-1372a94c84ab?q=80&w=2612&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 3.99,
        'sale_price' => null,
        'stock' => 45,
        'category_name' => 'Seeds',
        'category_slug' => 'seeds',
        'description' => 'High-yielding carrot seeds that produce sweet, crunchy carrots. These easy-to-grow seeds are perfect for beginners and experienced gardeners alike.',
        'specifications' => [
            'Variety' => 'Nantes',
            'Quantity' => '200 seeds per packet',
            'Growing Season' => 'Spring, Fall',
            'Days to Germination' => '7-14 days',
            'Days to Maturity' => '65-75 days',
            'Planting Depth' => '1/4 inch'
        ]
    ],
    'irrigation-system' => [
        'id' => 8,
        'name' => 'Irrigation System',
        'slug' => 'irrigation-system',
        'image' => 'https://plus.unsplash.com/premium_photo-1661825536186-19606cd9a0f1?q=80&w=2164&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 199.99,
        'sale_price' => 179.99,
        'stock' => 10,
        'category_name' => 'Equipment',
        'category_slug' => 'equipment',
        'description' => 'Complete drip irrigation system kit that waters your garden efficiently, saving water and time. Easy to install and customize for gardens of all sizes.',
        'specifications' => [
            'Coverage Area' => 'Up to 50 square meters',
            'Components' => 'Timer, pressure regulator, 50m tubing, 50 drippers, connectors',
            'Water Saving' => 'Up to 70% compared to traditional watering',
            'Installation' => 'No tools required',
            'Timer Settings' => 'Adjustable frequency and duration'
        ]
    ],
    'potato-harvester' => [
        'id' => 9,
        'name' => 'Potato Harvester',
        'slug' => 'potato-harvester',
        'image' => 'https://images.unsplash.com/photo-1698505949250-51f8b2c9c8c6?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        'price' => 1499.99,
        'sale_price' => null,
        'stock' => 5,
        'category_name' => 'Machinery',
        'category_slug' => 'machinery',
        'description' => 'Efficient potato harvesting machine for medium-sized farms. This harvester digs, separates, and collects potatoes with minimal damage to the crop.',
        'specifications' => [
            'Working Width' => '60 cm',
            'Capacity' => 'Up to 3 tons per hour',
            'Power Requirement' => '35-45 HP tractor',
            'Working Depth' => 'Adjustable, up to 30 cm',
            'Weight' => '350 kg',
            'Features' => 'Adjustable vibrating sieve, collection basket'
        ]
    ],
    'organic-strawberries' => [
        'id' => 10,
        'name' => 'Organic Strawberries (1kg)',
        'slug' => 'organic-strawberries',
        'image' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&h=400&q=80',
        'price' => 8.99,
        'sale_price' => 7.99,
        'stock' => 15,
        'category_name' => 'Fresh Produce',
        'category_slug' => 'produce',
        'description' => 'Sweet and juicy organic strawberries freshly harvested at peak ripeness. These berries are perfect for snacking, desserts, or preserving.',
        'specifications' => [
            'Weight' => '1 kg',
            'Growing Method' => 'Organic, no pesticides',
            'Variety' => 'Mixed (predominantly Sweet Charlie)',
            'Nutritional Benefits' => 'Rich in vitamin C, antioxidants, and fiber',
            'Storage' => 'Refrigerate immediately, consume within 5 days',
            'Origin' => 'Local berry farms'
        ]
    ]
];

$product = null;

if (!empty($slug) && isset($products[$slug])) {
    $product = $products[$slug];
} elseif ($id > 0) {
    foreach ($products as $prod) {
        if ($prod['id'] == $id) {
            $product = $prod;
            break;
        }
    }
}

if ($product === null) {
    redirect(SITE_URL . '/pages/shop.php');
}
?>

<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <div class="flex items-center text-sm">
            <a href="<?php echo SITE_URL; ?>" class="text-gray-600 hover:text-green-600">Home</a>
            <span class="mx-2">/</span>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-gray-600 hover:text-green-600">Shop</a>
            <span class="mx-2">/</span>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $product['category_slug']; ?>" class="text-gray-600 hover:text-green-600"><?php echo $product['category_name']; ?></a>
            <span class="mx-2">/</span>
            <span class="text-green-600"><?php echo $product['name']; ?></span>
        </div>
    </div>
</div>

<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-12 gap-8 mb-10">
            <div class="md:col-span-5 product-image-container">
                <img data-image="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full rounded-lg shadow-md product-image">
            </div>
            
            <div class="product-info md:col-span-7">
                <h1 class="text-3xl md:text-4xl font-bold mb-2"><?php echo $product['name']; ?></h1>
                
                <div class="text-sm text-green-600 mb-4">
                    Category: <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a>
                </div>
                
                <div class="price-wrapper mb-6">
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="text-3xl font-bold text-green-600">$<?php echo number_format($product['sale_price'], 2); ?></span>
                    <span class="text-xl text-gray-500 line-through ml-2">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php else: ?>
                    <span class="text-3xl font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="stock mb-6">
                    <?php if ($product['stock'] > 0): ?>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="description mb-6">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <p class="text-gray-700"><?php echo $product['description']; ?></p>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                <div class="quantity-selector mb-6">
                    <h3 class="text-lg font-semibold mb-2">Quantity</h3>
                    <div class="flex items-center">
                        <button id="decrease-qty" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-l-md">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-16 text-center border-y border-gray-200 py-1">
                        <button id="increase-qty" class="bg-gray-200 text-gray-700 px-3 py-1 rounded-r-md">+</button>
                    </div>
                </div>
                
                <div class="actions flex flex-col sm:flex-row gap-4 mb-8">
                    <button id="add-to-cart" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                    <button id="add-to-wishlist" class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 py-3 px-6 rounded-md transition-colors flex items-center justify-center">
                        <i class="far fa-heart mr-2"></i> Add to Wishlist
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-8">
                    <button class="w-full bg-gray-300 text-gray-600 cursor-not-allowed py-3 px-6 rounded-md flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i> Out of Stock
                    </button>
                    <p class="text-sm text-gray-600 mt-2">This product is currently out of stock. Please check back later or browse similar products.</p>
                </div>
                <?php endif; ?>
                
                <div class="specifications">
                    <h2 class="text-xl font-semibold mb-4">Specifications</h2>
                    <div class="border rounded-lg overflow-hidden">
                        <?php $i = 0; foreach ($product['specifications'] as $label => $value): $i++; ?>
                        <div class="grid grid-cols-2 gap-4 p-3 <?php echo $i % 2 === 0 ? 'bg-gray-50' : 'bg-white'; ?>">
                            <div class="font-medium"><?php echo $label; ?></div>
                            <div><?php echo $value; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">You May Also Like</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php 
            $related_products = array_filter($products, function($p) use ($product) {
                return $p['slug'] !== $product['slug'];
            });
            
            $count = 0;
            foreach ($related_products as $related): 
                if ($count >= 4) break;
                $count++;
            ?>
            <div class="product-card">
                <div class="relative overflow-hidden group">
                    <img data-image="<?php echo $related['image']; ?>" 
                         alt="<?php echo $related['name']; ?>" 
                         class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110 product-image">
                    
                    <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                        Sale
                    </div>
                    <?php endif; ?>
                    
                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $related['slug']; ?>" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors block text-center">
                            View Details
                        </a>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="text-sm text-green-600 mb-1">
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php?category=<?php echo $related['category_slug']; ?>">
                            <?php echo $related['category_name']; ?>
                        </a>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">
                        <a href="<?php echo SITE_URL; ?>/pages/product.php?slug=<?php echo $related['slug']; ?>" class="hover:text-green-600 transition-colors">
                            <?php echo $related['name']; ?>
                        </a>
                    </h3>
                    <div class="flex justify-between items-center">
                        <div class="price-wrapper">
                            <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($related['sale_price'], 2); ?></span>
                            <span class="text-sm text-gray-500 line-through ml-2">$<?php echo number_format($related['price'], 2); ?></span>
                            <?php else: ?>
                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($related['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    // Function to get the correct image URL
    function getImageUrl(image) {
        if (!image) return '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
        
        if (image.startsWith('http://') || image.startsWith('https://')) {
            return image;
        } else if (image.startsWith('/')) {
            return '<?php echo SITE_URL; ?>' + image;
        } else {
            return '<?php echo SITE_URL; ?>/assets/images/products/' + image;
        }
    }
    
    // Load images when the page is ready
    document.addEventListener('DOMContentLoaded', function() {
        const productImages = document.querySelectorAll('.product-image');
        
        productImages.forEach(function(img) {
            const imagePath = img.getAttribute('data-image');
            img.src = getImageUrl(imagePath);
            
            img.onerror = function() {
                this.src = '<?php echo SITE_URL; ?>/assets/images/products/default-product.jpg';
                this.onerror = null;
            }
        });
        
        const quantityInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        
        if (quantityInput && decreaseBtn && increaseBtn) {
            const minQty = 1;
            const maxQty = <?php echo $product['stock'] > 0 ? $product['stock'] : 1; ?>;
            
            function updateQuantityInput(qty) {
                quantityInput.value = qty;
            }
            
            decreaseBtn.addEventListener('click', function() {
                let currentQty = parseInt(quantityInput.value);
                if (currentQty > minQty) {
                    updateQuantityInput(currentQty - 1);
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let currentQty = parseInt(quantityInput.value);
                if (currentQty < maxQty) {
                    updateQuantityInput(currentQty + 1);
                }
            });
            
            quantityInput.addEventListener('change', function() {
                let qty = parseInt(this.value);
                
                if (isNaN(qty) || qty < minQty) {
                    updateQuantityInput(minQty);
                } else if (qty > maxQty) {
                    updateQuantityInput(maxQty);
                }
            });
        }
        
        const addToCartBtn = document.getElementById('add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = <?php echo $product['id']; ?>;
                const quantity = parseInt(document.getElementById('quantity').value);
                
                fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = data.cart_count;
                        }
                        
                        alert(data.message || 'Product added to cart successfully');
                    } else {
                        alert(data.message || 'Failed to add product to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the product to cart');
                });
            });
        }
        
        const addToWishlistBtn = document.getElementById('add-to-wishlist');
        if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener('click', function() {
                const productId = <?php echo $product['id']; ?>;
                
                fetch('<?php echo SITE_URL; ?>/includes/ajax/add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const wishlistCountElement = document.getElementById('wishlist-count');
                        if (wishlistCountElement) {
                            wishlistCountElement.textContent = data.wishlist_count;
                        }
                        
                        alert(data.message || 'Product added to wishlist successfully');
                    } else {
                        alert(data.message || 'Failed to add product to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the product to wishlist');
                });
            });
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?> 