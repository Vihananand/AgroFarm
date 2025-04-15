<?php
$page_title = "Request Item";
$page_description = "Request a custom product or item not available in our current inventory.";

include_once '../includes/config.php';

$form_submitted = false;
$form_error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $item_name = sanitize($_POST['item_name'] ?? '');
    $item_description = sanitize($_POST['item_description'] ?? '');
    $quantity = sanitize($_POST['quantity'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $urgency = sanitize($_POST['urgency'] ?? '');
    
    if (empty($name) || empty($email) || empty($item_name) || empty($item_description)) {
        $form_error = true;
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = true;
        $error_message = 'Please provide a valid email address.';
    } elseif (empty($quantity)) {
        $form_error = true;
        $error_message = 'Please select a quantity.';
    } else {
        $form_submitted = true;
        
        try {
            $stmt = $conn->prepare("INSERT INTO item_requests (name, email, phone, item_name, item_description, quantity, category, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $item_name, $item_description, $quantity, $category, $urgency]);
        } catch (PDOException $e) {
            
        }
    }
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<!-- Hero Section -->
<section class="relative bg-green-900 text-white py-20" style="background-image: url('https://picsum.photos/id/117/1920/600'); background-size: cover; background-position: center;">
    <div class="container mx-auto px-4 z-10 relative">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-gsap="fade-up">Request an Item</h1>
            <p class="text-xl md:text-2xl mb-8 text-green-100" data-gsap="fade-up" data-delay="0.2">
                Can't find what you're looking for? Let us know and we'll help source it for you.
            </p>
            <div class="flex justify-center gap-4" data-gsap="fade-up" data-delay="0.3">
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full flex items-center transition-all duration-300">
                    <i class="fas fa-store mr-2 text-green-300"></i>
                    <span>Browse Shop</span>
                </a>
                <a href="#request-form" class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-full flex items-center transition-all duration-300">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    <span>Start Request</span>
                </a>
            </div>
        </div>
    </div>
    <div class="absolute inset-0 bg-black opacity-60"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
</section>

<!-- How It Works -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12" data-gsap="fade-up">How It Works</h2>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-sm text-center" data-gsap="fade-up" data-delay="0.1">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4 relative">
                    <span class="absolute -top-2 -right-2 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg">1</span>
                    <i class="fas fa-pencil-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Submit Request</h3>
                <p class="text-gray-600">
                    Fill out the form below with details about the item you're looking for.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm text-center" data-gsap="fade-up" data-delay="0.2">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4 relative">
                    <span class="absolute -top-2 -right-2 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg">2</span>
                    <i class="fas fa-search text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">We Source It</h3>
                <p class="text-gray-600">
                    Our team will search for the best products that match your requirements.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm text-center" data-gsap="fade-up" data-delay="0.3">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4 relative">
                    <span class="absolute -top-2 -right-2 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg">3</span>
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Get a Quote</h3>
                <p class="text-gray-600">
                    We'll provide you with pricing and availability information within 24-48 hours.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Request Form -->
<section id="request-form" class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden" data-gsap="fade-up">
                <div class="md:flex">
                    <!-- Left side - Form -->
                    <div class="md:w-2/3 p-8">
                        <h2 class="text-3xl font-bold mb-6">Request Form</h2>
                        <p class="text-gray-600 mb-8">
                            Please provide as much detail as possible to help us find exactly what you need.
                        </p>
                        
                        <?php if ($form_submitted): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm mb-6 flex items-start" role="alert">
                            <div class="flex-shrink-0 mr-3">
                                <i class="fas fa-check-circle text-green-500 text-xl mt-0.5"></i>
                            </div>
                            <div>
                                <strong class="font-bold block mb-1">Thank you for your request!</strong>
                                <span>We've received your item request and will get back to you within 24-48 hours.</span>
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
                        
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#request-form" method="POST">
                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="name" class="form-label flex items-center">
                                        <i class="fas fa-user text-green-600 mr-2"></i>
                                        Your Name*
                                    </label>
                                    <input type="text" id="name" name="name" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="John Doe" required value="<?php echo $_POST['name'] ?? ''; ?>">
                                </div>
                                <div>
                                    <label for="email" class="form-label flex items-center">
                                        <i class="fas fa-envelope text-green-600 mr-2"></i>
                                        Email Address*
                                    </label>
                                    <input type="email" id="email" name="email" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="john@example.com" required value="<?php echo $_POST['email'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label for="phone" class="form-label flex items-center">
                                    <i class="fas fa-phone text-green-600 mr-2"></i>
                                    Phone Number (Optional)
                                </label>
                                <input type="tel" id="phone" name="phone" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="+1 (555) 123-4567" value="<?php echo $_POST['phone'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-6">
                                <label for="item_name" class="form-label flex items-center">
                                    <i class="fas fa-box text-green-600 mr-2"></i>
                                    Item Name*
                                </label>
                                <input type="text" id="item_name" name="item_name" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="e.g., Organic Fertilizer, Mini Tractor, etc." required value="<?php echo $_POST['item_name'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-6">
                                <label for="item_description" class="form-label flex items-center">
                                    <i class="fas fa-align-left text-green-600 mr-2"></i>
                                    Item Description*
                                </label>
                                <textarea id="item_description" name="item_description" rows="4" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm resize-none" placeholder="Please provide detailed specifications, brand preferences, and any other relevant information. If you selected 'Custom' quantity, please specify the exact amount needed here." required><?php echo $_POST['item_description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="grid md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="quantity" class="form-label flex items-center">
                                        <i class="fas fa-sort-amount-up text-green-600 mr-2"></i>
                                        Quantity*
                                    </label>
                                    <select id="quantity" name="quantity" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" required>
                                        <option value="1" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '1') ? 'selected' : ''; ?>>1 unit</option>
                                        <option value="2-5" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '2-5') ? 'selected' : ''; ?>>2-5 units</option>
                                        <option value="6-10" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '6-10') ? 'selected' : ''; ?>>6-10 units</option>
                                        <option value="11-20" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '11-20') ? 'selected' : ''; ?>>11-20 units</option>
                                        <option value="21-50" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '21-50') ? 'selected' : ''; ?>>21-50 units</option>
                                        <option value="51-100" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '51-100') ? 'selected' : ''; ?>>51-100 units</option>
                                        <option value="100+" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == '100+') ? 'selected' : ''; ?>>100+ units</option>
                                        <option value="custom" <?php echo (isset($_POST['quantity']) && $_POST['quantity'] == 'custom') ? 'selected' : ''; ?>>Custom (specify in description)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="category" class="form-label flex items-center">
                                        <i class="fas fa-folder text-green-600 mr-2"></i>
                                        Category
                                    </label>
                                    <select id="category" name="category" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm">
                                        <option value="">Select a category</option>
                                        <option value="machinery" <?php echo (isset($_POST['category']) && $_POST['category'] === 'machinery') ? 'selected' : ''; ?>>Farm Machinery</option>
                                        <option value="fertilizers" <?php echo (isset($_POST['category']) && $_POST['category'] === 'fertilizers') ? 'selected' : ''; ?>>Fertilizers</option>
                                        <option value="equipment" <?php echo (isset($_POST['category']) && $_POST['category'] === 'equipment') ? 'selected' : ''; ?>>Equipment</option>
                                        <option value="produce" <?php echo (isset($_POST['category']) && $_POST['category'] === 'produce') ? 'selected' : ''; ?>>Fresh Produce</option>
                                        <option value="seeds" <?php echo (isset($_POST['category']) && $_POST['category'] === 'seeds') ? 'selected' : ''; ?>>Seeds</option>
                                        <option value="other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="urgency" class="form-label flex items-center">
                                        <i class="fas fa-clock text-green-600 mr-2"></i>
                                        Urgency
                                    </label>
                                    <select id="urgency" name="urgency" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm">
                                        <option value="standard" <?php echo (!isset($_POST['urgency']) || $_POST['urgency'] === 'standard') ? 'selected' : ''; ?>>Standard (1-2 weeks)</option>
                                        <option value="urgent" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] === 'urgent') ? 'selected' : ''; ?>>Urgent (3-5 days)</option>
                                        <option value="critical" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] === 'critical') ? 'selected' : ''; ?>>Critical (24-48 hours)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex items-start mb-6">
                                <div class="flex items-center h-5">
                                    <input id="terms" name="terms" type="checkbox" class="h-5 w-5 accent-green-500 rounded bg-gray-50 cursor-pointer" required>
                                </div>
                                <label for="terms" class="ml-3 text-sm text-gray-600">
                                    I understand that submitting this form does not guarantee item availability and that pricing may vary.
                                </label>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn-primary py-3 px-8 w-full md:w-auto flex items-center justify-center space-x-2 rounded-md shadow-md hover:shadow-lg transition-all duration-300">
                                    <span>Submit Request</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Right side - Info -->
                    <div class="md:w-1/3 bg-green-800 text-white p-8">
                        <h3 class="text-xl font-bold mb-6">Request Guidelines</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="fas fa-info-circle text-green-300 mr-3"></i>
                                </div>
                                <div>
                                    <p class="text-green-100 text-sm">
                                        The more details you provide, the better we can match your requirements.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="fas fa-info-circle text-green-300 mr-3"></i>
                                </div>
                                <div>
                                    <p class="text-green-100 text-sm">
                                        Be specific about brands, models, and specifications if you have preferences.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="fas fa-info-circle text-green-300 mr-3"></i>
                                </div>
                                <div>
                                    <p class="text-green-100 text-sm">
                                        For large quantities or specialized equipment, we may need additional information.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-green-700 my-6">
                        
                        <h4 class="font-semibold mb-4">Need immediate assistance?</h4>
                        <p class="text-green-100 text-sm mb-4">
                            Contact our sourcing specialists directly:
                        </p>
                        
                        <div class="flex items-center mb-3">
                            <i class="fas fa-phone-alt mr-3 text-green-300"></i>
                            <a href="tel:8001234567" class="text-white hover:text-green-200 transition-colors">(800) 123-4567</a>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-green-300"></i>
                            <a href="mailto:sourcing@agrofarm.com" class="text-white hover:text-green-200 transition-colors">sourcing@agrofarm.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12" data-gsap="fade-up">Frequently Asked Questions</h2>
            
            <div class="space-y-4" data-gsap="fade-up" data-delay="0.2">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">How long does it take to receive a quote?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            For standard requests, we typically provide a quote within 24-48 hours. For more complex or specialized items, it may take up to 3-5 business days to source and provide accurate pricing.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">Is there a minimum order quantity?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            There is no minimum order quantity for most items. However, for very specialized or bulk items, suppliers may have minimum order requirements. We'll inform you of any such requirements in our quote.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">What if I need an item urgently?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            For urgent requests, select "Critical" or "Urgent" in the urgency dropdown. We'll prioritize your request and explore expedited shipping options. Additional fees may apply for rush orders and expedited shipping.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">Can I request international products?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Yes, we can source products internationally. However, please note that international sourcing may involve longer lead times, customs duties, and additional shipping costs. We'll provide all relevant information in your quote.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FAQ Accordion functionality
        const faqToggles = document.querySelectorAll('.faq-toggle');
        
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle content visibility
                content.classList.toggle('hidden');
                
                // Toggle icon
                if (content.classList.contains('hidden')) {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                    icon.style.transform = 'rotate(180deg)';
                }
                
                // Close other FAQ items
                faqToggles.forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        const otherContent = otherToggle.nextElementSibling;
                        const otherIcon = otherToggle.querySelector('i');
                        
                        otherContent.classList.add('hidden');
                        otherIcon.classList.remove('fa-minus');
                        otherIcon.classList.add('fa-plus');
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                });
            });
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?> 