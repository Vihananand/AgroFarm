<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="bg-white shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-4">
        <nav class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2">
                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSI4IiBmaWxsPSIjMkQ3NzM4Ii8+PHBhdGggZD0iTTggMjRDMTIuNDE4MyAyNCAxNiAyMC40MTgzIDE2IDE2QzE2IDExLjU4MTcgMTIuNDE4MyA4IDggOCIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik04IDMyQzE2LjgzNjYgMzIgMjQgMjQuODM2NiAyNCAxNkMyNCA3LjE2MzQ0IDE2LjgzNjYgMCA4IDAiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMjAgMjhMMjQgMzJMMjggMjgiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PHBhdGggZD0iTTI0IDE4VjMyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik0zMiAxNEMzMiAxNCAzMiA4IDI2IDgiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjxwYXRoIGQ9Ik0yOCAxOEMyOCAxOCAyOCAxNCAyNCAxNCIgc3Ryb2tlPSIjOEJDMzRBIiBzdHJva2Utd2lkdGg9IjIuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PC9zdmc+" alt="AgroFarm Logo" class="h-10 w-auto">
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?php echo SITE_URL; ?>" class="<?php echo $current_page === 'index.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Home</a>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="<?php echo $current_page === 'shop.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Shop</a>
                <a href="<?php echo SITE_URL; ?>/pages/about.php" class="<?php echo $current_page === 'about.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">About Us</a>
                <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="<?php echo $current_page === 'contact.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Contact Us</a>
                <a href="<?php echo SITE_URL; ?>/pages/custom-request.php" class="nav-link">Request Item</a>
            </div>
            
            <!-- Icons Section -->
            <div class="flex items-center gap-4">
                <!-- Search Icon -->
                <button id="search-toggle" class="text-gray-600 hover:text-green-600 transition-colors duration-300">
                    <i class="fas fa-search text-xl"></i>
                </button>
                
                <!-- Wishlist Icon -->
                <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="text-gray-600 hover:text-green-600 transition-colors duration-300 relative">
                    <i class="fas fa-heart text-xl"></i>
                    <?php if (isLoggedIn() && getWishlistItemCount() > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        <?php echo getWishlistItemCount(); ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- Cart Icon -->
                <a href="<?php echo SITE_URL; ?>/user/cart.php" class="text-gray-600 hover:text-green-600 transition-colors duration-300 relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if (isLoggedIn() && getCartItemCount() > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        <?php echo getCartItemCount(); ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <div class="relative" id="user-menu-container">
                    <button class="text-gray-600 hover:text-green-600 transition-colors duration-300" id="user-menu-button">
                        <i class="fas fa-user-circle text-xl"></i>
                    </button>
                    <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md overflow-hidden z-50 hidden">
                        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</a>
                        <a href="<?php echo SITE_URL; ?>/user/orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
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
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSI4IiBmaWxsPSIjMkQ3NzM4Ii8+PHBhdGggZD0iTTggMjRDMTIuNDE4MyAyNCAxNiAyMC40MTgzIDE2IDE2QzE2IDExLjU4MTcgMTIuNDE4MyA4IDggOCIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik04IDMyQzE2LjgzNjYgMzIgMjQgMjQuODM2NiAyNCAxNkMyNCA3LjE2MzQ0IDE2LjgzNjYgMCA4IDAiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMjAgMjhMMjQgMzJMMjggMjgiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PHBhdGggZD0iTTI0IDE4VjMyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik0zMiAxNEMzMiAxNCAzMiA4IDI2IDgiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjxwYXRoIGQ9Ik0yOCAxOEMyOCAxOCAyOCAxNCAyNCAxNCIgc3Ryb2tlPSIjOEJDMzRBIiBzdHJva2Utd2lkdGg9IjIuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PC9zdmc+" alt="AgroFarm Logo" class="h-10 w-auto">
        </a>
        <button id="mobile-menu-close" class="text-gray-600 hover:text-green-600 transition-colors duration-300">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="p-4">
        <div class="flex flex-col gap-4">
            <a href="<?php echo SITE_URL; ?>" class="text-lg py-2 border-b border-gray-200 <?php echo $current_page === 'index.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-lg py-2 border-b border-gray-200 <?php echo $current_page === 'shop.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Shop</a>
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="text-lg py-2 border-b border-gray-200 <?php echo $current_page === 'about.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">About Us</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-lg py-2 border-b border-gray-200 <?php echo $current_page === 'contact.php' ? 'text-green-600' : 'text-gray-600 hover:text-green-600'; ?>">Contact Us</a>
            <a href="<?php echo SITE_URL; ?>/pages/custom-request.php" class="text-lg py-2 border-b border-gray-200">Request Item</a>
            <?php if (!isLoggedIn()): ?>
            <div class="flex gap-4 mt-4">
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn-primary flex-1 text-center">Login</a>
                <a href="<?php echo SITE_URL; ?>/pages/signup.php" class="btn-secondary flex-1 text-center">Sign Up</a>
            </div>
            <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="text-lg py-2 border-b border-gray-200">My Profile</a>
            <a href="<?php echo SITE_URL; ?>/user/orders.php" class="text-lg py-2 border-b border-gray-200">My Orders</a>
            <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="text-lg py-2 border-b border-gray-200 text-red-600">Log Out</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('search-toggle').addEventListener('click', function() {
        document.getElementById('search-bar').classList.toggle('hidden');
    });
    
    document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; 
    });
    
    document.getElementById('mobile-menu-close').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.add('hidden');
        document.body.style.overflow = ''; 
    });

    // User menu dropdown functionality
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');
    const userMenuContainer = document.getElementById('user-menu-container');

    if (userMenuButton && userMenuDropdown) {
        // Toggle menu on button click
        userMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuContainer.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });

        // Prevent menu from closing when clicking inside dropdown
        userMenuDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
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
