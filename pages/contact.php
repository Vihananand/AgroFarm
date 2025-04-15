<?php
$page_title = "Contact Us";
$page_description = "Get in touch with AgroFarm for any inquiries, questions, or support.";

include_once '../includes/config.php';
include_once '../includes/header.php';
include_once '../includes/navbar.php';

$form_submitted = false;
$form_error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $form_error = true;
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = true;
        $error_message = 'Please provide a valid email address.';
    } else {
        $form_submitted = true;
        
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
        } catch (PDOException $e) {
        }
    }
}
?>

<!-- Hero Section -->
<section class="relative bg-green-900 text-white py-20" style="background-image: url('https://picsum.photos/id/1072/1920/600'); background-size: cover; background-position: center;">
    <div class="container mx-auto px-4 z-10 relative">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-gsap="fade-up">Contact Us</h1>
            <p class="text-xl md:text-2xl mb-8 text-green-100" data-gsap="fade-up" data-delay="0.2">
                We'd love to hear from you! Reach out with any questions or inquiries.
            </p>
            <div class="flex justify-center gap-4" data-gsap="fade-up" data-delay="0.3">
                <a href="tel:8001234567" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full flex items-center transition-all duration-300">
                    <i class="fas fa-phone-alt mr-2 text-green-300"></i>
                    <span>Call Us</span>
                </a>
                <a href="mailto:info@agrofarm.com" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full flex items-center transition-all duration-300">
                    <i class="fas fa-envelope mr-2 text-green-300"></i>
                    <span>Email Us</span>
                </a>
            </div>
        </div>
    </div>
    <div class="absolute inset-0 bg-black opacity-60"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
</section>

<!-- Contact Information -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border-t-4 border-green-500 group" data-gsap="fade-up" data-delay="0.1">
                <div class="w-16 h-16 mx-auto bg-green-100 group-hover:bg-green-200 rounded-full flex items-center justify-center mb-6 transition-all duration-300">
                    <i class="fas fa-map-marker-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-center">Visit Us</h3>
                <p class="text-gray-600 text-center">
                    123 Farm Road<br>
                    Green Valley, CA 94523<br>
                    United States
                </p>
                <div class="mt-6 text-center">
                    <a href="https://maps.google.com" target="_blank" class="text-green-600 hover:text-green-700 flex items-center justify-center font-medium">
                        <span>Get Directions</span>
                        <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </a>
                </div>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border-t-4 border-green-500 group" data-gsap="fade-up" data-delay="0.2">
                <div class="w-16 h-16 mx-auto bg-green-100 group-hover:bg-green-200 rounded-full flex items-center justify-center mb-6 transition-all duration-300">
                    <i class="fas fa-phone-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-center">Call Us</h3>
                <p class="text-gray-600 text-center">
                    Customer Service: <br>
                    <a href="tel:8001234567" class="hover:text-green-600">(800) 123-4567</a><br>
                    <span class="text-sm text-gray-500">Mon-Fri: 8:00 AM - 6:00 PM</span>
                </p>
                <div class="mt-6 text-center">
                    <a href="tel:8001234567" class="text-green-600 hover:text-green-700 flex items-center justify-center font-medium">
                        <span>Call Now</span>
                        <i class="fas fa-phone ml-2 text-sm"></i>
                    </a>
                </div>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border-t-4 border-green-500 group" data-gsap="fade-up" data-delay="0.3">
                <div class="w-16 h-16 mx-auto bg-green-100 group-hover:bg-green-200 rounded-full flex items-center justify-center mb-6 transition-all duration-300">
                    <i class="fas fa-envelope text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-center">Email Us</h3>
                <p class="text-gray-600 text-center">
                    <span class="block mb-1">General Inquiries:</span>
                    <a href="mailto:info@agrofarm.com" class="hover:text-green-600">info@agrofarm.com</a><br>
                    <span class="text-sm text-gray-500">We respond within 24 hours</span>
                </p>
                <div class="mt-6 text-center">
                    <a href="mailto:info@agrofarm.com" class="text-green-600 hover:text-green-700 flex items-center justify-center font-medium">
                        <span>Send Email</span>
                        <i class="fas fa-envelope ml-2 text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Map -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            <div data-gsap="fade-right">
                <h2 class="text-3xl font-bold mb-6">Send Us a Message</h2>
                <p class="text-gray-600 mb-8">
                    Have questions about our products or services? Fill out the form below and our team will get back to you as soon as possible.
                </p>
                
                <?php if ($form_submitted): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm mb-6 flex items-start" role="alert">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas fa-check-circle text-green-500 text-xl mt-0.5"></i>
                    </div>
                    <div>
                        <strong class="font-bold block mb-1">Thank you for reaching out!</strong>
                        <span>Your message has been sent successfully. We'll get back to you shortly.</span>
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
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="name" class="form-label flex items-center">
                                <i class="fas fa-user text-green-600 mr-2"></i>
                                Your Name
                            </label>
                            <input type="text" id="name" name="name" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="John Doe" required>
                        </div>
                        <div>
                            <label for="email" class="form-label flex items-center">
                                <i class="fas fa-envelope text-green-600 mr-2"></i>
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="john@example.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="subject" class="form-label flex items-center">
                            <i class="fas fa-tag text-green-600 mr-2"></i>
                            Subject
                        </label>
                        <input type="text" id="subject" name="subject" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm" placeholder="How can we help you?" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="message" class="form-label flex items-center">
                            <i class="fas fa-comment-alt text-green-600 mr-2"></i>
                            Message
                        </label>
                        <textarea id="message" name="message" rows="5" class="form-input border-0 bg-gray-50 focus:bg-white shadow-sm resize-none" placeholder="Please describe your question or inquiry in detail..." required></textarea>
                    </div>
                    
                    <div class="flex items-start mb-6">
                        <div class="flex items-center h-5">
                            <input id="terms" type="checkbox" required class="w-5 h-5 accent-green-500 rounded bg-gray-50 cursor-pointer">
                        </div>
                        <label for="terms" class="ml-3 text-sm text-gray-600">
                            I agree to the <a href="#" class="text-green-600 hover:underline font-medium">Privacy Policy</a> and consent to be contacted regarding my inquiry.
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn-primary py-3 px-8 w-full md:w-auto flex items-center justify-center space-x-2 rounded-md shadow-md hover:shadow-lg transition-all duration-300">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <div data-gsap="fade-left">
                <h2 class="text-3xl font-bold mb-6">Our Location</h2>
                <p class="text-gray-600 mb-6">
                    Visit our headquarters and main distribution center. We're conveniently located just off Highway 101.
                </p>
                
                <div class="bg-white p-2 rounded-lg shadow-sm">
                    <!-- Replace with your actual Google Maps embed code -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.7362175896884!2d-122.4194!3d37.7749!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808580a6f93676a3%3A0xba9229f4ef0ac49a!2sSan%20Francisco%2C%20CA%2C%20USA!5e0!3m2!1sen!2s!4v1625788921242!5m2!1sen!2s" 
                            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" class="rounded"></iframe>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                        <h3 class="font-semibold mb-2 flex items-center text-green-700">
                            <i class="fas fa-clock mr-2"></i>
                            Business Hours
                        </h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li class="flex justify-between">
                                <span>Monday - Friday:</span>
                                <span>8:00 AM - 6:00 PM</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Saturday:</span>
                                <span>9:00 AM - 4:00 PM</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Sunday:</span>
                                <span>Closed</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                        <h3 class="font-semibold mb-2 flex items-center text-green-700">
                            <i class="fas fa-address-card mr-2"></i>
                            Contact Info
                        </h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-phone-alt w-4 mt-1 mr-2 text-green-600"></i>
                                <span>(800) 123-4567</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-envelope w-4 mt-1 mr-2 text-green-600"></i>
                                <span>info@agrofarm.com</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-globe w-4 mt-1 mr-2 text-green-600"></i>
                                <span>www.agrofarm.com</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold mb-6 text-center" data-gsap="fade-up">Frequently Asked Questions</h2>
            <p class="text-xl text-gray-600 mb-8 text-center" data-gsap="fade-up" data-delay="0.2">
                Find quick answers to common questions about our products and services.
            </p>
            
            <div class="space-y-4" data-gsap="fade-up" data-delay="0.3">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">What payment methods do you accept?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            We accept all major credit cards (Visa, MasterCard, American Express, Discover), PayPal, and bank transfers. For large orders, we also offer financing options.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">How long does shipping take?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Standard shipping typically takes 3-5 business days. For large equipment and machinery, delivery times may vary between 7-14 business days depending on your location. Express shipping options are available at checkout for most items.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">Do you offer international shipping?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Yes, we ship to select international destinations. International shipping costs and delivery times vary by location. Please note that customs duties and taxes may apply and are the responsibility of the customer.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">What is your return policy?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            We offer a 30-day return policy for most products. Items must be returned in their original condition and packaging. Fresh produce and perishable items cannot be returned unless damaged or defective. Contact our customer service team to initiate a return.
                        </p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button class="faq-toggle flex justify-between items-center w-full p-4 text-left bg-white hover:bg-gray-50 transition-colors">
                        <span class="font-medium">Do you offer bulk discounts?</span>
                        <i class="fas fa-plus text-green-600 transition-transform"></i>
                    </button>
                    <div class="faq-content bg-gray-50 p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Yes, we offer tiered discounts for bulk orders. The discount percentage increases with the order quantity. For very large orders, please contact our sales team directly for custom pricing.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center" data-gsap="fade-up" data-delay="0.4">
                <p class="text-gray-600 mb-4">
                    Still have questions? We're here to help!
                </p>
                <a href="tel:8001234567" class="inline-flex items-center text-green-600 hover:text-green-700">
                    <i class="fas fa-phone-alt mr-2"></i> Call us at (800) 123-4567
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-green-100">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" data-gsap="fade-up">Ready to Get Started?</h2>
        <p class="text-xl text-gray-700 mb-8 max-w-3xl mx-auto" data-gsap="fade-up" data-delay="0.2">
            Browse our extensive range of agricultural products and start transforming your farming experience today.
        </p>
        <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="border-2 border-green-600 text-green-600 hover:bg-green-600 hover:text-white py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 text-center font-medium text-lg flex items-center justify-center max-w-72 mx-auto" data-gsap="fade-up" data-delay="0.3">Shop Now</a>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqToggles = document.querySelectorAll('.faq-toggle');
        
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                content.classList.toggle('hidden');
                
                if (content.classList.contains('hidden')) {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                    icon.style.transform = 'rotate(180deg)';
                }
                
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