<?php
$page_title = "Login";
$page_description = "Sign in to your AgroFarm account to manage your orders, wishlist, and more.";

include_once '../includes/config.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                    
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                }
                
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL;
                redirect($redirect);
            } else {
                $error = 'Invalid email or password.';
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
        <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-xl overflow-hidden max-w-5xl mx-auto" style="min-height: 600px;">
            <!-- Left side - Farm image -->
            <div class="md:w-1/2 relative hidden md:block" data-gsap="fade-right">
                <div class="absolute inset-0 bg-green-800/40 z-10 flex flex-col justify-end p-8 text-white">
                    <h2 class="text-3xl font-bold mb-4">Welcome to AgroFarm</h2>
                    <p class="text-white/90 mb-6">Your one-stop shop for all agricultural needs</p>
                    <div class="flex space-x-3 mb-6">
                        <span class="h-2 w-2 rounded-full bg-white/60"></span>
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        <span class="h-2 w-2 rounded-full bg-white/60"></span>
                    </div>
                </div>
                <img src="https://picsum.photos/id/1043/800/1200" alt="Farm landscape" class="w-full h-full object-cover">
            </div>
            
            <!-- Right side - Login form -->
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center" data-gsap="fade-left">
                <div class="mb-8 text-center md:text-left">
                    <div class="inline-block p-4 bg-green-100 rounded-full mb-4" data-gsap="fade-down" data-delay="0.2">
                        <i class="fas fa-user-circle text-green-600 text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800" data-gsap="fade-up" data-delay="0.3">Welcome Back</h1>
                    <p class="text-gray-600 mt-2" data-gsap="fade-up" data-delay="0.4">Sign in to access your account</p>
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
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="your@email.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-gray-700 text-sm font-medium">Password</label>
                            <a href="<?php echo SITE_URL; ?>/pages/forgot-password.php" class="text-sm text-green-600 hover:text-green-800 transition-colors duration-200">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" class="pl-10 pr-4 py-3 w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 rounded-lg transition-all duration-200" placeholder="••••••••" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" id="toggle-password">
                                <i class="fas fa-eye-slash text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center mb-6">
                        <input id="remember" name="remember" type="checkbox" class="h-5 w-5 accent-green-600 border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me for 30 days
                        </label>
                    </div>
                    
                    <div class="mb-6">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    
                    <div class="text-center" data-gsap="fade-up" data-delay="0.7">
                        <p class="text-gray-600">Don't have an account?</p>
                        <a href="<?php echo SITE_URL; ?>/pages/signup.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="inline-block mt-2 text-green-600 hover:text-green-800 font-medium transition-colors duration-200">
                            Create an account <i class="fas fa-long-arrow-alt-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="flex items-center my-6" data-gsap="fade-up" data-delay="0.8">
                        <div class="flex-grow h-px bg-gray-200"></div>
                        <div class="px-4 text-sm text-gray-500">or continue with</div>
                        <div class="flex-grow h-px bg-gray-200"></div>
                    </div>
                    
                    <div class="flex justify-center space-x-4" data-gsap="fade-up" data-delay="0.9">
                        <a href="#" class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 hover:bg-red-600 hover:text-white transition-all duration-300">
                            <i class="fab fa-google"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-full bg-black/10 flex items-center justify-center text-black hover:bg-black hover:text-white transition-all duration-300">
                            <i class="fab fa-apple"></i>
                        </a>
                    </div>
                </form>
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