<?php
$page_title = "Request Custom Item - AgroFarm";
$page_description = "Submit a custom product request to AgroFarm";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

$form_submitted = false;
$form_error = false;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $request_details = sanitize($_POST['request_details'] ?? '');
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    if (empty($name) || empty($email) || empty($request_details)) {
        $form_error = true;
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = true;
        $error_message = 'Please provide a valid email address.';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO custom_requests (user_id, name, email, phone, request_details, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $name, $email, $phone, $request_details]);
            
            $form_submitted = true;
            $success_message = 'Thank you for your request. We will review it and get back to you soon.';
            
            // Log success
            error_log("Custom request submitted successfully from: " . $email);

            // Set success message
            $_SESSION['success'] = 'Your custom request has been submitted successfully. We will review it and get back to you soon.';
            header('Location: ' . SITE_URL . '/user/custom-requests.php');
            exit;
        } catch (PDOException $e) {
            error_log("Error submitting custom request: " . $e->getMessage());
            $form_error = true;
            $error_message = 'An error occurred while submitting your request. Please try again later.';
        }
    }
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Hero Section with Parallax Effect -->
<section class="relative bg-green-900 text-white py-24 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); opacity: 0.3;"></div>
    <div class="container mx-auto px-4 z-10 relative">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6" data-aos="fade-up">Request a Custom Item</h1>
            <p class="text-xl md:text-2xl mb-8 text-green-100" data-aos="fade-up" data-aos-delay="100">
                Can't find what you're looking for? Let us know and we'll help source it for you.
            </p>
        </div>
    </div>
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black opacity-60"></div>
</section>

<!-- Process Steps Section -->
<section class="py-16 bg-green-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
        
        <div class="grid md:grid-cols-4 gap-8 max-w-5xl mx-auto">
            <div class="bg-white p-6 rounded-xl shadow-sm text-center relative" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-lg shadow-md">1</div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-600 mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Submit Your Request</h3>
                <p class="text-gray-600 text-sm">Fill out our custom request form with your needs and specifications.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm text-center relative" data-aos="fade-up" data-aos-delay="200">
                <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-lg shadow-md">2</div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-600 mx-auto mb-4">
                    <i class="fas fa-search text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">We Research Options</h3>
                <p class="text-gray-600 text-sm">Our team will research and find the best options for your requirements.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm text-center relative" data-aos="fade-up" data-aos-delay="300">
                <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-lg shadow-md">3</div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-600 mx-auto mb-4">
                    <i class="fas fa-comments text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">We Contact You</h3>
                <p class="text-gray-600 text-sm">We'll reach out to discuss the options and get your approval.</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm text-center relative" data-aos="fade-up" data-aos-delay="400">
                <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-lg shadow-md">4</div>
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-green-600 mx-auto mb-4">
                    <i class="fas fa-truck text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Order Fulfillment</h3>
                <p class="text-gray-600 text-sm">We source the items and deliver them to your location.</p>
            </div>
        </div>
    </div>
</section>

<!-- Custom Request Form Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12">
                <div data-aos="fade-right">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-3xl font-bold">Request an Item</h2>
                        <a href="<?php echo SITE_URL; ?>/pages/custom-request-history.php" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors duration-300 shadow-sm">
                            <i class="fas fa-history mr-2"></i>
                            <span>View History</span>
                        </a>
                    </div>
                    <p class="text-gray-600 mb-8">
                        Fill out the form with details about the product you're looking for. The more specific you are, the better we can help find what you need.
                    </p>
                    
                    <?php if ($form_submitted): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm mb-6 flex items-start" role="alert">
                        <div class="flex-shrink-0 mr-3">
                            <i class="fas fa-check-circle text-green-500 text-xl mt-0.5"></i>
                        </div>
                        <div>
                            <strong class="font-bold block mb-1">Request Submitted!</strong>
                            <span><?php echo $success_message; ?></span>
                        </div>
                    </div>
                    <?php elseif ($form_error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm mb-6 flex items-start" role="alert">
                        <div class="flex-shrink-0 mr-3">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
                        </div>
                        <div>
                            <strong class="font-bold block mb-1">There was a problem</strong>
                            <span><?php echo $error_message; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="name" class="form-label flex items-center">
                                    <i class="fas fa-user text-green-600 mr-2"></i>
                                    Your Name
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm w-full rounded-md" 
                                       placeholder="John Doe" 
                                       required 
                                       value="<?php echo $_POST['name'] ?? ''; ?>">
                            </div>
                            
                            <div>
                                <label for="email" class="form-label flex items-center">
                                    <i class="fas fa-envelope text-green-600 mr-2"></i>
                                    Email Address
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm w-full rounded-md" 
                                       placeholder="john@example.com" 
                                       required 
                                       value="<?php echo $_POST['email'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="phone" class="form-label flex items-center">
                                <i class="fas fa-phone text-green-600 mr-2"></i>
                                Phone Number (Optional)
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm w-full rounded-md" 
                                   placeholder="+1 (555) 000-0000" 
                                   value="<?php echo $_POST['phone'] ?? ''; ?>">
                        </div>

                        <div class="mb-6">
                            <label for="request_details" class="form-label flex items-center">
                                <i class="fas fa-clipboard-list text-green-600 mr-2"></i>
                                Request Details
                            </label>
                            <textarea id="request_details" 
                                      name="request_details" 
                                      rows="6" 
                                      class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm resize-none w-full rounded-md" 
                                      placeholder="Please describe the item you're looking for in detail. Include any specific requirements such as quantity, size, color, material, brand preferences, and intended use..." 
                                      required><?php echo $_POST['request_details'] ?? ''; ?></textarea>
                        </div>

                        <div class="flex items-start mb-6">
                            <div class="flex items-center h-5">
                                <input id="terms" 
                                       type="checkbox" 
                                       required 
                                       class="w-5 h-5 accent-green-500 rounded bg-gray-50 cursor-pointer">
                            </div>
                            <label for="terms" class="ml-3 text-sm text-gray-600">
                                I agree to the <a href="#" class="text-green-600 hover:underline font-medium">Privacy Policy</a> and consent to be contacted regarding my request.
                            </label>
                        </div>

                        <button type="submit" 
                                class="w-full py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md shadow transition-colors duration-300 flex items-center justify-center">
                            <span>Submit Request</span>
                            <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>
                </div>
                
                <div data-aos="fade-left">
                    <h2 class="text-3xl font-bold mb-6">Why Choose Our Custom Sourcing</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500 flex">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 flex-shrink-0">
                                <i class="fas fa-network-wired text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-lg mb-1">Extensive Network</h3>
                                <p class="text-gray-600">
                                    We have an extensive network of suppliers and manufacturers across the agricultural sector.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500 flex">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i class="fas fa-award text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-lg mb-1">Quality Guaranteed</h3>
                                <p class="text-gray-600">
                                    We only source high-quality products that meet our strict standards and requirements.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-500 flex">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 flex-shrink-0">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-lg mb-1">Competitive Pricing</h3>
                                <p class="text-gray-600">
                                    We leverage our relationships to get you the best prices on your custom requests.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-amber-500 flex">
                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 flex-shrink-0">
                                <i class="fas fa-headset text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-lg mb-1">Dedicated Support</h3>
                                <p class="text-gray-600">
                                    Our team works with you throughout the entire process, from request to delivery.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <h3 class="font-semibold text-lg mb-3 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Popular Custom Requests
                        </h3>
                        <ul class="list-disc pl-5 space-y-2 text-gray-700">
                            <li>Specialized farming equipment for unique crops</li>
                            <li>Organic and sustainable farming supplies</li>
                            <li>Rare or heritage seeds and plants</li>
                            <li>Custom irrigation or water management systems</li>
                            <li>Specialized livestock feed or supplements</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Testimonials Section -->
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-center mb-10">What Our Customers Say</h2>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "I was looking for a rare type of organic fertilizer for my specialty crops. The team found exactly what I needed within days. Excellent service!"
                        </p>
                        <div class="font-medium">- Michael D., Organic Farmer</div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "AgroFarm helped me source custom equipment parts that I couldn't find anywhere else. Saved me thousands on replacement costs."
                        </p>
                        <div class="font-medium">- Sarah L., Commercial Grower</div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "I needed specialized irrigation equipment for my greenhouse. Not only did they find it, but they also helped me set it up. Above and beyond service!"
                        </p>
                        <div class="font-medium">- James K., Greenhouse Owner</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?> 