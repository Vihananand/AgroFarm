<?php
$testimonials = [
    [
        'name' => 'John Smith',
        'role' => 'Farmer',
        'content' => 'AgroFarm has revolutionized my farming experience. Their high-quality machinery and excellent customer service have helped me increase my productivity by 40%.',
        'rating' => 5,
        'image' => 'https://picsum.photos/id/1005/100/100'
    ],
    [
        'name' => 'Sarah Johnson',
        'role' => 'Home Gardener',
        'content' => 'I love the organic produce and seeds from AgroFarm. Everything is fresh, and their delivery is always on time. Highly recommend!',
        'rating' => 5,
        'image' => 'https://picsum.photos/id/1011/100/100'
    ],
    [
        'name' => 'Michael Brown',
        'role' => 'Agricultural Engineer',
        'content' => 'The farming equipment from AgroFarm is top-notch. Durable, efficient, and very well-designed. Great value for money.',
        'rating' => 4,
        'image' => 'https://picsum.photos/id/1012/100/100'
    ],
    [
        'name' => 'Jennifer Wilson',
        'role' => 'Organic Farm Owner',
        'content' => 'Switching to AgroFarm\'s organic fertilizers has made a huge difference in the quality of our produce. Our customers have noticed the improvement!',
        'rating' => 5,
        'image' => 'https://picsum.photos/id/1027/100/100'
    ],
    [
        'name' => 'David Thompson',
        'role' => 'Agricultural Supplier',
        'content' => 'AgroFarm has been our reliable partner for years. Their product range is extensive and they always deliver on their promises.',
        'rating' => 5,
        'image' => 'https://picsum.photos/id/1074/100/100'
    ],
    [
        'name' => 'Emily Davis',
        'role' => 'Sustainable Farming Advocate',
        'content' => 'I appreciate AgroFarm\'s commitment to sustainable farming practices. Their products and advice have helped many farmers transition to more eco-friendly methods.',
        'rating' => 4,
        'image' => 'https://picsum.photos/id/1084/100/100'
    ]
];
?>

<div class="swiper-testimonials overflow-hidden relative">
    <div class="swiper-wrapper py-8">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="swiper-slide">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <img class="h-12 w-12 rounded-full object-cover" src="<?php echo $testimonial['image']; ?>" alt="<?php echo $testimonial['name']; ?>">
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold"><?php echo $testimonial['name']; ?></h4>
                        <p class="text-gray-600"><?php echo $testimonial['role']; ?></p>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="flex text-yellow-400 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $testimonial['rating']): ?>
                            <i class="fas fa-star"></i>
                            <?php else: ?>
                            <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 italic">"<?php echo $testimonial['content']; ?>"</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="swiper-button-next testimonials-next absolute right-2 top-1/2 -translate-y-1/2 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-md z-10 text-green-600">
        <i class="fas fa-chevron-right text-xs"></i>
    </div>
    <div class="swiper-button-prev testimonials-prev absolute left-2 top-1/2 -translate-y-1/2 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-md z-10 text-green-600">
        <i class="fas fa-chevron-left text-xs"></i>
    </div>

    <div class="swiper-pagination testimonials-pagination absolute bottom-0 left-0 right-0 text-center"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            const testimonialsSwiper = new Swiper('.swiper-testimonials', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.testimonials-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.testimonials-next',
                    prevEl: '.testimonials-prev',
                    disabledClass: 'opacity-50 cursor-not-allowed'
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                }
            });
            
            document.querySelector('.testimonials-next').classList.remove('swiper-button-next');
            document.querySelector('.testimonials-prev').classList.remove('swiper-button-prev');
        } else {
            console.error('Swiper is not loaded');
            document.querySelector('.swiper-testimonials').innerHTML = `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
                    <?php foreach ($testimonials as $testimonial): ?>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <img class="h-12 w-12 rounded-full object-cover" src="<?php echo $testimonial['image']; ?>" alt="<?php echo $testimonial['name']; ?>">
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold"><?php echo $testimonial['name']; ?></h4>
                                <p class="text-gray-600"><?php echo $testimonial['role']; ?></p>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="flex text-yellow-400 mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $testimonial['rating']): ?>
                                    <i class="fas fa-star"></i>
                                    <?php else: ?>
                                    <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <p class="text-gray-700 italic">"<?php echo $testimonial['content']; ?>"</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            `;
        }
    });
</script> 