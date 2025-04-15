<?php
include_once 'includes/config.php';
include_once 'includes/header.php';
include_once 'includes/navbar.php';
?>

<!-- Hero Section -->
<section id="hero" class="relative h-screen flex items-center overflow-hidden">
    <div class="container mx-auto px-4 z-10">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div class="text-content" data-gsap="fade-right">
                <h1 class="text-5xl md:text-6xl font-bold text-green-950 mb-4">Fresh From <span class="text-green-600">Nature</span> to Your Door</h1>
                <p class="text-xl text-gray-700 mb-8">Discover premium agricultural products for farmers and organic foods for everyone.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="pages/shop.php" class="bg-green-600 hover:bg-green-700 text-white py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 text-center font-medium text-lg flex items-center justify-center">
                        <span>Shop Now</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <a href="pages/about.php" class="bg-white hover:bg-gray-50 text-green-700 border-2 border-green-600 py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 text-center font-medium text-lg flex items-center justify-center">
                        <span>Learn More</span>
                        <i class="fas fa-info-circle ml-2"></i>
                    </a>
                </div>
            </div>
            <div class="image-container hidden md:block" data-gsap="fade-left">
                <img src="https://picsum.photos/id/1068/1200/800" alt="Fresh produce" class="rounded-lg shadow-xl">
            </div>
        </div>
    </div>
    <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-r from-green-100/50 to-transparent -z-10"></div>
</section>

<!-- Featured Categories -->
<section id="categories" class="py-20 bg-green-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">What We Offer</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div class="category-card" data-gsap="fade-up" data-delay="0.1">
                <div class="icon-wrapper">
                    <i class="fas fa-tractor text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mt-4">Farm Machinery</h3>
                <a href="pages/shop.php?category=machinery" class="text-green-600 hover:text-green-700 mt-2 inline-block">Browse</a>
            </div>
            <div class="category-card" data-gsap="fade-up" data-delay="0.2">
                <div class="icon-wrapper">
                    <i class="fas fa-flask text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mt-4">Fertilizers</h3>
                <a href="pages/shop.php?category=fertilizers" class="text-green-600 hover:text-green-700 mt-2 inline-block">Browse</a>
            </div>
            <div class="category-card" data-gsap="fade-up" data-delay="0.3">
                <div class="icon-wrapper">
                    <i class="fas fa-tools text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mt-4">Equipment</h3>
                <a href="pages/shop.php?category=equipment" class="text-green-600 hover:text-green-700 mt-2 inline-block">Browse</a>
            </div>
            <div class="category-card" data-gsap="fade-up" data-delay="0.4">
                <div class="icon-wrapper">
                    <i class="fas fa-apple-alt text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mt-4">Fresh Produce</h3>
                <a href="pages/shop.php?category=produce" class="text-green-600 hover:text-green-700 mt-2 inline-block">Browse</a>
            </div>
            <div class="category-card" data-gsap="fade-up" data-delay="0.5">
                <div class="icon-wrapper">
                    <i class="fas fa-seedling text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mt-4">Seeds</h3>
                <a href="pages/shop.php?category=seeds" class="text-green-600 hover:text-green-700 mt-2 inline-block">Browse</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured-products" class="py-20">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Featured Products</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            include_once 'components/featured-products.php';
            ?>
        </div>
        <div class="text-center mt-12">
            <a href="pages/shop.php" class="btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="py-20 bg-green-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">What Our Customers Say</h2>
        <div class="testimonial-slider relative" data-gsap="fade-in">
            <!-- Testimonials will be loaded dynamically -->
            <?php include_once 'components/testimonials.php'; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section id="cta" class="py-20 bg-green-900 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Join Our Community of Farmers</h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto">Sign up today and get access to exclusive deals, farming tips, and more!</p>
        <a href="pages/signup.php" class="btn-white">Sign Up Now</a>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?> 