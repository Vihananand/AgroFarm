<header class="bg-white shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-4">
        <nav class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                    <rect width="40" height="40" rx="8" fill="#2D7738"/>
                    <path d="M8 24C12.4183 24 16 20.4183 16 16C16 11.5817 12.4183 8 8 8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M8 32C16.8366 32 24 24.8366 24 16C24 7.16344 16.8366 0 8 0" stroke="#8BC34A" stroke-width="3" stroke-linecap="round"/>
                    <path d="M20 28L24 32L28 28" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M24 18V32" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M32 14C32 14 32 8 26 8" stroke="#8BC34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M28 18C28 18 28 14 24 14" stroke="#8BC34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-2xl font-bold text-green-700">AgroFarm</span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?php echo SITE_URL; ?>" class="nav-link">Home</a>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="nav-link">Shop</a>
                <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link">About Us</a>
                <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="nav-link">Contact Us</a>
                <a href="<?php echo SITE_URL; ?>/pages/custom-request.php" class="nav-link">Request Item</a>
            </div>
            
            <!-- Icons Section -->
            <div class="flex items-center gap-4">
                <!-- Search Icon -->
                <button id="search-toggle" class="text-gray-600 hover:text-green-600 transition-colors duration-300">
                    <i class="fas fa-search text-xl"></i>
                </button>
                
                <!-- Wishlist Icon -->
                <a href="<?php echo SITE_URL; ?>/pages/wishlist.php" class="text-gray-600 hover:text-green-600 transition-colors duration-300 relative">
                    <i class="fas fa-heart text-xl"></i>
                    <?php if (isLoggedIn() && getWishlistItemCount() > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        <?php echo getWishlistItemCount(); ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- Cart Icon -->
                <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="text-gray-600 hover:text-green-600 transition-colors duration-300 relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if (isLoggedIn() && getCartItemCount() > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        <?php echo getCartItemCount(); ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <div class="relative group">
                    <button class="text-gray-600 hover:text-green-600 transition-colors duration-300">
                        <i class="fas fa-user-circle text-xl"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md overflow-hidden z-50 hidden group-hover:block">
                        <a href="<?php echo SITE_URL; ?>/pages/account/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</a>
                        <a href="<?php echo SITE_URL; ?>/pages/account/orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                        <a href="<?php echo SITE_URL; ?>/pages/account/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Log Out</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="text-gray-600 hover:text-green-600 transition-colors duration-300">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button id="mobile-menu-toggle" class="text-gray-600 hover:text-green-600 transition-colors duration-300 md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>
    </div>
    
    <!-- Search Bar (Hidden by default) -->
    <div id="search-bar" class="bg-gray-100 py-4 hidden">
        <div class="container mx-auto px-4">
            <form action="<?php echo SITE_URL; ?>/pages/search.php" method="GET" class="flex">
                <input type="text" name="q" placeholder="Search products..." class="flex-grow py-2 px-4 rounded-l-md focus:outline-none">
                <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-r-md hover:bg-green-700 transition-colors duration-300">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Mobile Menu (Hidden by default) -->
<div id="mobile-menu" class="mobile-menu hidden">
    <div class="p-4 flex justify-between items-center border-b">
        <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                <rect width="40" height="40" rx="8" fill="#2D7738"/>
                <path d="M8 24C12.4183 24 16 20.4183 16 16C16 11.5817 12.4183 8 8 8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M8 32C16.8366 32 24 24.8366 24 16C24 7.16344 16.8366 0 8 0" stroke="#8BC34A" stroke-width="3" stroke-linecap="round"/>
                <path d="M20 28L24 32L28 28" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M24 18V32" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <path d="M32 14C32 14 32 8 26 8" stroke="#8BC34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M28 18C28 18 28 14 24 14" stroke="#8BC34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-2xl font-bold text-green-700">AgroFarm</span>
        </a>
        <button id="mobile-menu-close" class="text-gray-600 hover:text-green-600 transition-colors duration-300">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="p-4">
        <div class="flex flex-col gap-4">
            <a href="<?php echo SITE_URL; ?>" class="text-lg py-2 border-b border-gray-200">Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-lg py-2 border-b border-gray-200">Shop</a>
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="text-lg py-2 border-b border-gray-200">About Us</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-lg py-2 border-b border-gray-200">Contact Us</a>
            <a href="<?php echo SITE_URL; ?>/pages/custom-request.php" class="text-lg py-2 border-b border-gray-200">Request Item</a>
            <?php if (!isLoggedIn()): ?>
            <div class="flex gap-4 mt-4">
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn-primary flex-1 text-center">Login</a>
                <a href="<?php echo SITE_URL; ?>/pages/signup.php" class="btn-secondary flex-1 text-center">Sign Up</a>
            </div>
            <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/pages/account/profile.php" class="text-lg py-2 border-b border-gray-200">My Profile</a>
            <a href="<?php echo SITE_URL; ?>/pages/account/orders.php" class="text-lg py-2 border-b border-gray-200">My Orders</a>
            <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="text-lg py-2 border-b border-gray-200 text-red-600">Log Out</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Toggle search bar
    document.getElementById('search-toggle').addEventListener('click', function() {
        document.getElementById('search-bar').classList.toggle('hidden');
    });
    
    // Mobile menu functionality
    document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    });
    
    document.getElementById('mobile-menu-close').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.add('hidden');
        document.body.style.overflow = ''; // Re-enable scrolling
    });
</script>

<style>
    .nav-link {
        @apply text-gray-700 hover:text-green-600 font-medium relative;
    }
    
    .nav-link::after {
        content: '';
        @apply absolute bottom-0 left-0 h-0.5 w-0 bg-green-600 transition-all duration-300;
    }
    
    .nav-link:hover::after {
        @apply w-full;
    }
</style>
