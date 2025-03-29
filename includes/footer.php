<footer class="bg-green-900 text-white mt-auto">
    <!-- Main Footer -->
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About Us -->
            <div>
                <h3 class="text-xl font-bold mb-4">About AgroFarm</h3>
                <p class="text-green-100 mb-4">AgroFarm is your one-stop shop for agricultural products, fresh organic produce, farm equipment, and all your farming needs.</p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="text-white hover:text-green-300 transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-white hover:text-green-300 transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-white hover:text-green-300 transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-white hover:text-green-300 transition-colors">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Useful Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Useful Links</h3>
                <ul class="space-y-2">
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php" class="text-green-100 hover:text-white transition-colors">Shop</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/about.php" class="text-green-100 hover:text-white transition-colors">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-green-100 hover:text-white transition-colors">Contact Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/custom-request.php" class="text-green-100 hover:text-white transition-colors">Request Item</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/faq.php" class="text-green-100 hover:text-white transition-colors">FAQs</a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div>
                <h3 class="text-xl font-bold mb-4">Categories</h3>
                <ul class="space-y-2">
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php?category=machinery" class="text-green-100 hover:text-white transition-colors">Farm Machinery</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php?category=fertilizers" class="text-green-100 hover:text-white transition-colors">Fertilizers</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php?category=equipment" class="text-green-100 hover:text-white transition-colors">Farming Equipment</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php?category=produce" class="text-green-100 hover:text-white transition-colors">Fresh Produce</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php?category=seeds" class="text-green-100 hover:text-white transition-colors">Seeds</a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div>
                <h3 class="text-xl font-bold mb-4">Newsletter</h3>
                <p class="text-green-100 mb-4">Subscribe to our newsletter for the latest updates and offers.</p>
                <form action="<?php echo SITE_URL; ?>/includes/process_newsletter.php" method="POST" class="flex flex-col gap-2">
                    <input type="email" name="email" placeholder="Your email address" required class="px-4 py-2 rounded-md bg-green-800 text-white border border-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition-colors">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bottom Footer -->
    <div class="bg-green-950 py-4">
        <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
            <div class="text-green-200 text-sm mb-4 md:mb-0">
                &copy; <?php echo date('Y'); ?> AgroFarm. All rights reserved.
            </div>
            <div class="flex gap-4 text-sm">
                <a href="<?php echo SITE_URL; ?>/pages/privacy-policy.php" class="text-green-200 hover:text-white transition-colors">Privacy Policy</a>
                <a href="<?php echo SITE_URL; ?>/pages/terms.php" class="text-green-200 hover:text-white transition-colors">Terms of Service</a>
                <a href="<?php echo SITE_URL; ?>/pages/shipping.php" class="text-green-200 hover:text-white transition-colors">Shipping Info</a>
            </div>
        </div>
    </div>
</footer>

<!-- GSAP Animation Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize GSAP ScrollTrigger
        gsap.registerPlugin(ScrollTrigger);
        
        // Animate elements with data-gsap attribute
        const fadeElements = document.querySelectorAll('[data-gsap="fade-in"]');
        fadeElements.forEach(element => {
            gsap.from(element, {
                opacity: 0,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none none"
                }
            });
        });
        
        // Animate from right
        const fadeRightElements = document.querySelectorAll('[data-gsap="fade-right"]');
        fadeRightElements.forEach(element => {
            gsap.from(element, {
                x: 100,
                opacity: 0,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none none"
                }
            });
        });
        
        // Animate from left
        const fadeLeftElements = document.querySelectorAll('[data-gsap="fade-left"]');
        fadeLeftElements.forEach(element => {
            gsap.from(element, {
                x: -100,
                opacity: 0,
                duration: 1,
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none none"
                }
            });
        });
        
        // Animate from bottom (fade up)
        const fadeUpElements = document.querySelectorAll('[data-gsap="fade-up"]');
        fadeUpElements.forEach(element => {
            const delay = element.getAttribute('data-delay') || 0;
            gsap.from(element, {
                y: 50,
                opacity: 0,
                duration: 0.8,
                delay: parseFloat(delay),
                scrollTrigger: {
                    trigger: element,
                    start: "top 85%",
                    end: "bottom 15%",
                    toggleActions: "play none none none"
                }
            });
        });
    });
</script>

</body>
</html> 