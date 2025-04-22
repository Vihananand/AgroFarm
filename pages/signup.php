<?php
$page_title = "Sign Up";
$page_description = "Create an AgroFarm account to start shopping for agricultural products and more.";

include_once '../includes/config.php';
include_once '../includes/db_connect.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'This email is already registered. Please use a different email or login.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$first_name, $last_name, $email, $phone, $hashed_password]);
                
                if ($result) {
                    $user_id = $conn->lastInsertId();
                    
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $first_name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'customer';
                    
                    setFlashMessage('success', 'Registration successful! Welcome to AgroFarm.');
                    
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL;
                    redirect($redirect);
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

include_once '../includes/header.php';
include_once '../includes/navbar.php';
?>

<section class="min-h-screen bg-gray-50 flex items-center py-12">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-xl overflow-hidden max-w-5xl mx-auto" style="min-height: 700px;">
            <!-- Left side - Form -->
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col" data-gsap="fade-right">
                <div class="mb-6 text-center md:text-left">
                    <div class="inline-block p-4 bg-green-100 rounded-full mb-4" data-gsap="fade-down" data-delay="0.2">
                        <i class="fas fa-user-plus text-green-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800" data-gsap="fade-up" data-delay="0.3">Join AgroFarm</h1>
                    <p class="text-gray-600 mt-2" data-gsap="fade-up" data-delay="0.4">Create an account to start shopping</p>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert" data-gsap="fade-in" data-delay="0.5">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . (isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '')); ?>" method="POST" data-gsap="fade-up" data-delay="0.6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="first_name" name="first_name" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="John" required value="<?php echo $_POST['first_name'] ?? ''; ?>">
                            </div>
                        </div>
                        <div>
                            <label for="last_name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="last_name" name="last_name" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="Doe" required value="<?php echo $_POST['last_name'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="tel" id="phone" name="phone" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="+1 (555) 000-0000" value="<?php echo $_POST['phone'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="your@email.com" required value="<?php echo $_POST['email'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="••••••••" required minlength="8">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" id="toggle-password">
                                <i class="fas fa-eye-slash text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-2">Must be at least 8 characters</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="••••••••" required minlength="8">
                        </div>
                    </div>
                    
                    <div class="flex items-start mb-6">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" class="h-5 w-5 accent-green-600 border-gray-300 rounded cursor-pointer" required>
                        </div>
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the <a href="<?php echo SITE_URL; ?>/pages/terms.php" class="text-green-600 hover:text-green-800 font-medium">Terms of Service</a> and <a href="<?php echo SITE_URL; ?>/pages/privacy-policy.php" class="text-green-600 hover:text-green-800 font-medium">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <div class="mb-6">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <span>Create Account</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    
                    <div class="text-center" data-gsap="fade-up" data-delay="0.7">
                        <p class="text-gray-600">Already have an account?</p>
                        <a href="<?php echo SITE_URL; ?>/pages/login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="inline-block mt-2 text-green-600 hover:text-green-800 font-medium transition-colors duration-200">
                            Sign in <i class="fas fa-long-arrow-alt-right ml-1"></i>
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Right side - Benefits -->
            <div class="md:w-1/2 relative hidden md:block" data-gsap="fade-left">
                <div class="absolute inset-0 bg-green-800/50 z-10 flex flex-col justify-between p-8 text-white">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">Benefits of Joining</h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-2">Order Tracking</h3>
                                    <p class="text-white/80">Track your orders and view order history at any time. Get real-time updates on your deliveries.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-heart text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-2">Save Favorites</h3>
                                    <p class="text-white/80">Create a wishlist of your favorite products for future purchases. Never forget what you loved.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-tag text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-2">Exclusive Offers</h3>
                                    <p class="text-white/80">Get access to member-only deals and promotions. Save on your favorite agricultural products.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-user-shield text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-xl mb-2">Secure Checkout</h3>
                                    <p class="text-white/80">Enjoy faster checkout with your saved information. All your data is securely encrypted.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-6">
                        <p class="text-white/60 text-sm">Join thousands of satisfied customers</p>
                        <div class="flex space-x-3">
                            <span class="h-2 w-2 rounded-full bg-white/60"></span>
                            <span class="h-2 w-2 rounded-full bg-white"></span>
                            <span class="h-2 w-2 rounded-full bg-white/60"></span>
                        </div>
                    </div>
                </div>
                <img src="https://picsum.photos/id/1084/800/1200" alt="Farm landscape" class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?>