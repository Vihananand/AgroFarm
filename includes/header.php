<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'AgroFarm - Your one-stop shop for agricultural products, fresh produce, farm equipment, and more.'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSI4IiBmaWxsPSIjMkQ3NzM4Ii8+PHBhdGggZD0iTTggMjRDMTIuNDE4MyAyNCAxNiAyMC40MTgzIDE2IDE2QzE2IDExLjU4MTcgMTIuNDE4MyA4IDggOCIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik04IDMyQzE2LjgzNjYgMzIgMjQgMjQuODM2NiAyNCAxNkMyNCA3LjE2MzQ0IDE2LjgzNjYgMCA4IDAiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMjAgMjhMMjQgMzJMMjggMjgiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PHBhdGggZD0iTTI0IDE4VjMyIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjxwYXRoIGQ9Ik0zMiAxNEMzMiAxNCAzMiA4IDI2IDgiIHN0cm9rZT0iIzhCQzM0QSIgc3Ryb2tlLXdpZHRoPSIyLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjxwYXRoIGQ9Ik0yOCAxOEMyOCAxOCAyOCAxNCAyNCAxNCIgc3Ryb2tlPSIjOEJDMzRBIiBzdHJva2Utd2lkdGg9IjIuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PC9zdmc+">
    
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper JS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    
    <!-- GSAP Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2D7738;
            --secondary-color: #F9B233;
            --accent-color: #8BC34A;
            --text-dark: #333;
            --text-light: #f8f9fa;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
        }
        
        .btn-primary {
            @apply bg-green-600 hover:bg-green-700 text-white py-2 px-6 rounded-md transition-all duration-300 inline-block font-medium;
        }
        
        .btn-secondary {
            @apply bg-transparent border-2 border-green-600 text-green-600 hover:bg-green-600 hover:text-white py-2 px-6 rounded-md transition-all duration-300 inline-block font-medium;
        }
        
        .btn-white {
            @apply bg-white text-green-600 hover:bg-green-50 py-2 px-6 rounded-md transition-all duration-300 inline-block font-medium;
        }
        
        .category-card {
            @apply bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 text-center;
        }
        
        .icon-wrapper {
            @apply mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center;
        }
        
        .product-card {
            @apply bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all duration-300;
        }
        
        /* Responsive navbar styles */
        .mobile-menu {
            @apply fixed top-0 left-0 w-full h-full bg-white z-50 transform transition-transform duration-300 ease-in-out;
        }
        
        .mobile-menu.hidden {
            @apply -translate-x-full;
        }
        
        /* Custom form styles */
        .form-input {
            @apply w-full border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent;
        }
        
        .form-label {
            @apply block text-gray-700 font-medium mb-2;
        }
        
        /* Animation classes */
        .fade-in {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        
        .fade-in.active {
            opacity: 1;
        }
        
        /* Swiper customization */
        .swiper-button-next:after, .swiper-button-prev:after {
            display: none; /* Hide default arrows */
        }
        
        .testimonials-next, .testimonials-prev {
            width: 30px !important;
            height: 30px !important;
        }
    </style>
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#2D7738',
                        'secondary': '#F9B233',
                        'accent': '#8BC34A',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col">

<?php 
// Flash message display
$flash = getFlashMessage();
if ($flash): 
?>
<div id="flash-message" class="fixed top-20 right-5 z-50 p-4 rounded-md shadow-md <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
    <div class="flex items-center">
        <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
        <p><?php echo $flash['message']; ?></p>
    </div>
    <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">
        <i class="fas fa-times"></i>
    </button>
</div>
<script>
    // Auto hide flash message after 5 seconds
    setTimeout(() => {
        const flashMessage = document.getElementById('flash-message');
        if (flashMessage) {
            flashMessage.remove();
        }
    }, 5000);
</script>
<?php endif; ?>
