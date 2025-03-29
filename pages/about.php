<?php
$page_title = "About Us";
$page_description = "Learn about AgroFarm's mission, values, and commitment to providing high-quality agricultural products.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="relative bg-green-900 text-white py-20" style="background-image: url('https://picsum.photos/id/1084/1920/600'); background-size: cover; background-position: center;">
    <div class="container mx-auto px-4 z-10 relative">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-gsap="fade-up">About AgroFarm</h1>
            <p class="text-xl md:text-2xl mb-8 text-green-100" data-gsap="fade-up" data-delay="0.2">
                Connecting Farmers with Quality Products Since 2005
            </p>
            <div class="w-24 h-1 bg-green-500 mx-auto"></div>
        </div>
    </div>
    <div class="absolute inset-0 bg-black opacity-60"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
</section>

<!-- Our Story -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="order-2 md:order-1" data-gsap="fade-right">
                <h2 class="text-3xl font-bold mb-6">Our Story</h2>
                <p class="text-gray-700 mb-4">
                    AgroFarm began with a simple vision: to provide farmers with high-quality products 
                    and equipment while making organic, fresh produce accessible to everyone.
                </p>
                <p class="text-gray-700 mb-4">
                    Founded in 2005 by a group of agricultural experts and farming enthusiasts, 
                    AgroFarm quickly grew from a small local supplier to a national leader in 
                    agricultural products and services.
                </p>
                <p class="text-gray-700 mb-4">
                    Our commitment to sustainability, innovation, and supporting local farmers 
                    has been at the heart of our business from day one. We believe in creating 
                    a healthier planet through responsible farming practices and providing the 
                    tools farmers need to succeed in an ever-changing world.
                </p>
                <div class="mt-8">
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn-primary">Get in Touch</a>
                </div>
            </div>
            <div class="order-1 md:order-2" data-gsap="fade-left">
                <img src="https://picsum.photos/id/1043/800/600" alt="AgroFarm Story" class="rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values -->
<section class="py-16 bg-green-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold mb-6" data-gsap="fade-up">Our Mission & Values</h2>
            <p class="text-xl text-gray-700" data-gsap="fade-up" data-delay="0.2">
                Guiding principles that drive everything we do at AgroFarm
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-lg shadow-sm" data-gsap="fade-up" data-delay="0.1">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-seedling text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4 text-center">Sustainability</h3>
                <p class="text-gray-700 text-center">
                    We're committed to promoting sustainable farming practices that protect our planet for future generations.
                </p>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-sm" data-gsap="fade-up" data-delay="0.2">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-hand-holding-heart text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4 text-center">Community Support</h3>
                <p class="text-gray-700 text-center">
                    We believe in empowering local farmers and supporting agricultural communities around the country.
                </p>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-sm" data-gsap="fade-up" data-delay="0.3">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                    <i class="fas fa-leaf text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4 text-center">Quality</h3>
                <p class="text-gray-700 text-center">
                    We never compromise on the quality of our products, ensuring the best for our customers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Meet Our Team -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold mb-6" data-gsap="fade-up">Meet Our Team</h2>
            <p class="text-xl text-gray-700" data-gsap="fade-up" data-delay="0.2">
                The passionate individuals behind AgroFarm's success
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center" data-gsap="fade-up" data-delay="0.1">
                <div class="relative inline-block mb-4">
                    <img src="https://picsum.photos/id/1025/300/300" alt="John Doe" class="w-40 h-40 rounded-full object-cover mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent rotate-animation"></div>
                </div>
                <h3 class="text-xl font-bold">John Doe</h3>
                <p class="text-green-600 mb-2">Founder & CEO</p>
                <p class="text-gray-600 text-sm mb-4">Agricultural Engineer with 20+ years of experience.</p>
                <div class="flex justify-center gap-4">
                    <a href="#" class="text-gray-400 hover:text-blue-500"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="text-center" data-gsap="fade-up" data-delay="0.2">
                <div class="relative inline-block mb-4">
                    <img src="https://picsum.photos/id/1000/300/300" alt="Jane Smith" class="w-40 h-40 rounded-full object-cover mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent rotate-animation"></div>
                </div>
                <h3 class="text-xl font-bold">Jane Smith</h3>
                <p class="text-green-600 mb-2">Operations Director</p>
                <p class="text-gray-600 text-sm mb-4">Supply chain expert specialized in agricultural logistics.</p>
                <div class="flex justify-center gap-4">
                    <a href="#" class="text-gray-400 hover:text-blue-500"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="text-center" data-gsap="fade-up" data-delay="0.3">
                <div class="relative inline-block mb-4">
                    <img src="https://picsum.photos/id/1074/300/300" alt="Michael Johnson" class="w-40 h-40 rounded-full object-cover mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent rotate-animation"></div>
                </div>
                <h3 class="text-xl font-bold">Michael Johnson</h3>
                <p class="text-green-600 mb-2">Head of Product Development</p>
                <p class="text-gray-600 text-sm mb-4">Agronomist with focus on sustainable farming innovations.</p>
                <div class="flex justify-center gap-4">
                    <a href="#" class="text-gray-400 hover:text-blue-500"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="text-center" data-gsap="fade-up" data-delay="0.4">
                <div class="relative inline-block mb-4">
                    <img src="https://picsum.photos/id/1062/300/300" alt="Emily Wilson" class="w-40 h-40 rounded-full object-cover mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent rotate-animation"></div>
                </div>
                <h3 class="text-xl font-bold">Emily Wilson</h3>
                <p class="text-green-600 mb-2">Customer Relations Manager</p>
                <p class="text-gray-600 text-sm mb-4">Former farmer with deep understanding of customer needs.</p>
                <div class="flex justify-center gap-4">
                    <a href="#" class="text-gray-400 hover:text-blue-500"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-green-900 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold mb-6" data-gsap="fade-up">Why Choose AgroFarm</h2>
            <p class="text-xl text-green-100" data-gsap="fade-up" data-delay="0.2">
                What sets us apart from the competition
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8">
            <div class="flex gap-4" data-gsap="fade-up" data-delay="0.1">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-white"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Quality Assurance</h3>
                    <p class="text-green-100">
                        All our products undergo rigorous quality checks before they reach our customers.
                        We partner with trusted suppliers and manufacturers to ensure the highest standards.
                    </p>
                </div>
            </div>
            
            <div class="flex gap-4" data-gsap="fade-up" data-delay="0.2">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-white"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Fast & Reliable Delivery</h3>
                    <p class="text-green-100">
                        Our efficient logistics network ensures your products reach you quickly and in perfect condition,
                        no matter where you're located.
                    </p>
                </div>
            </div>
            
            <div class="flex gap-4" data-gsap="fade-up" data-delay="0.3">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-headset text-white"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Expert Support</h3>
                    <p class="text-green-100">
                        Our team of agricultural experts is always available to provide guidance and answer your questions
                        about any product or farming practice.
                    </p>
                </div>
            </div>
            
            <div class="flex gap-4" data-gsap="fade-up" data-delay="0.4">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Satisfaction Guarantee</h3>
                    <p class="text-green-100">
                        We stand behind every product we sell. If you're not completely satisfied,
                        our hassle-free return policy has you covered.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Image Gallery -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold mb-6" data-gsap="fade-up">Our Farm Gallery</h2>
            <p class="text-xl text-gray-700" data-gsap="fade-up" data-delay="0.2">
                Take a visual journey through our farms and facilities
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.1">
                <img src="https://picsum.photos/id/164/600/400" alt="Farm fields" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.2">
                <img src="https://picsum.photos/id/1084/600/400" alt="Farmland" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.3">
                <img src="https://picsum.photos/id/10/600/400" alt="Countryside" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.4">
                <img src="https://picsum.photos/id/15/600/400" alt="Nature" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.5">
                <img src="https://picsum.photos/id/136/600/400" alt="Farm equipment" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.6">
                <img src="https://picsum.photos/id/137/600/400" alt="Green fields" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.7">
                <img src="https://picsum.photos/id/167/600/400" alt="Tractor" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
            <div class="gallery-item overflow-hidden rounded-lg shadow-md" data-gsap="fade-up" data-delay="0.8">
                <img src="https://picsum.photos/id/142/600/400" alt="Landscape" class="w-full h-64 object-cover transition-transform duration-500 hover:scale-110">
            </div>
        </div>
    </div>
</section>

<!-- Our Growth -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold mb-6" data-gsap="fade-up">Our Growth Journey</h2>
            <p class="text-xl text-gray-700" data-gsap="fade-up" data-delay="0.2">
                From a small local supplier to a national leader in agricultural products
            </p>
        </div>
        
        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-green-200"></div>
            
            <!-- Timeline items -->
            <div class="grid grid-cols-1 gap-12">
                <div class="relative flex justify-between items-center" data-gsap="fade-up" data-delay="0.1">
                    <div class="w-5/12 text-right pr-8">
                        <h3 class="text-xl font-bold text-green-700">2005</h3>
                        <h4 class="font-semibold mb-2">AgroFarm Founded</h4>
                        <p class="text-gray-600">Started as a small local supplier of farming equipment and seeds.</p>
                    </div>
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-green-600 z-10"></div>
                    <div class="w-5/12 pl-8">
                        <img src="https://picsum.photos/id/164/600/400" alt="2005 - AgroFarm Founded" class="rounded-lg shadow-sm">
                    </div>
                </div>
                
                <div class="relative flex justify-between items-center" data-gsap="fade-up" data-delay="0.2">
                    <div class="w-5/12 text-right pr-8">
                        <img src="https://picsum.photos/id/1029/600/400" alt="2010 - Regional Expansion" class="rounded-lg shadow-sm">
                    </div>
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-green-600 z-10"></div>
                    <div class="w-5/12 pl-8">
                        <h3 class="text-xl font-bold text-green-700">2010</h3>
                        <h4 class="font-semibold mb-2">Regional Expansion</h4>
                        <p class="text-gray-600">Expanded operations to cover three states with two distribution centers.</p>
                    </div>
                </div>
                
                <div class="relative flex justify-between items-center" data-gsap="fade-up" data-delay="0.3">
                    <div class="w-5/12 text-right pr-8">
                        <h3 class="text-xl font-bold text-green-700">2015</h3>
                        <h4 class="font-semibold mb-2">Product Line Expansion</h4>
                        <p class="text-gray-600">Added organic produce and fresh foods to our product offerings.</p>
                    </div>
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-green-600 z-10"></div>
                    <div class="w-5/12 pl-8">
                        <img src="https://picsum.photos/id/292/600/400" alt="2015 - Product Line Expansion" class="rounded-lg shadow-sm">
                    </div>
                </div>
                
                <div class="relative flex justify-between items-center" data-gsap="fade-up" data-delay="0.4">
                    <div class="w-5/12 text-right pr-8">
                        <img src="https://picsum.photos/id/180/600/400" alt="2018 - Online Platform Launch" class="rounded-lg shadow-sm">
                    </div>
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-green-600 z-10"></div>
                    <div class="w-5/12 pl-8">
                        <h3 class="text-xl font-bold text-green-700">2018</h3>
                        <h4 class="font-semibold mb-2">Online Platform Launch</h4>
                        <p class="text-gray-600">Launched our e-commerce platform to reach customers nationwide.</p>
                    </div>
                </div>
                
                <div class="relative flex justify-between items-center" data-gsap="fade-up" data-delay="0.5">
                    <div class="w-5/12 text-right pr-8">
                        <h3 class="text-xl font-bold text-green-700">Today</h3>
                        <h4 class="font-semibold mb-2">National Leader</h4>
                        <p class="text-gray-600">Serving thousands of farmers and households across the country with premium agricultural products.</p>
                    </div>
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-green-600 z-10"></div>
                    <div class="w-5/12 pl-8">
                        <img src="https://picsum.photos/id/1068/600/400" alt="Today - National Leader" class="rounded-lg shadow-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-green-100">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-gsap="fade-up">Ready to Experience AgroFarm?</h2>
        <p class="text-xl text-gray-700 mb-8 max-w-3xl mx-auto" data-gsap="fade-up" data-delay="0.2">
            Whether you're a commercial farmer or a home gardening enthusiast, we have everything you need to succeed.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center" data-gsap="fade-up" data-delay="0.3">
            <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-primary">Shop Now</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<style>
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .rotate-animation {
        animation: rotate 4s linear infinite;
    }
</style>

<?php include_once '../includes/footer.php'; ?> 