<?php
$page_title = "Baby & Maternity Essentials";
require_once "partials/header.php";
require_once "classes/Product.php";
require_once "classes/Category.php";

$productObj = new Product();
$categoryObj = new Category();

$featured_products = $productObj->get_featured_products(8);
$categories = $categoryObj->get_all_categories();
?>

<!-- Hero Banner Section -->
<section class="fz-hero my-0">
    <div class="fz-hero-slides" aria-hidden="true">
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
        <div class="fz-hero-slide"></div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <span class="badge fz-hero-badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-heart-fill me-1"></i> Loving Care for Mom & Baby
                </span>
                <h1 class="display-4 fz-hero-title mb-3">
                    Everything Your <span>Baby & Mom</span> Deserves.
                </h1>
                <p class="lead text-secondary mb-4">
                    Explore high-quality clothing, gentle skincare, nursing gear, and nursery essentials designed to bring comfort and joy to your family.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="products.php" class="btn btn-fz-primary btn-lg shadow-sm">
                        <i class="bi bi-grid-fill me-2"></i> View All Products
                    </a>
                    <a href="products.php?category=1" class="btn btn-fz-outline btn-lg">
                        Shop Baby Outfits
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Marquee Section -->
<section class="fz-marquee" aria-hidden="true">
    <div class="marquee-wrapper">
        <div class="marquee-group">
            <span class="marquee-item">✨ Every little thing for children &amp; women</span>
            <span class="marquee-item">💕 Thoughtful essentials for every little beginning</span>
            <span class="marquee-item">👶 Premium baby clothing &amp; nursery must-haves</span>
            <span class="marquee-item">🤱 Maternity comfort &amp; nursing care</span>
            <span class="marquee-item">🛁 Gentle skincare &amp; bath time favorites</span>
            <span class="marquee-item">🎀 Newborn hampers &amp; gift sets</span>
            <span class="marquee-item">🍼 BPA-free feeding &amp; mealtime essentials</span>
            <span class="marquee-item">🌟 Tested safe &amp; pediatrician approved</span>
        </div>
        <div class="marquee-group" aria-hidden="true">
            <span class="marquee-item">✨ Every little thing for children &amp; women</span>
            <span class="marquee-item">💕 Thoughtful essentials for every little beginning</span>
            <span class="marquee-item">👶 Premium baby clothing &amp; nursery must-haves</span>
            <span class="marquee-item">🤱 Maternity comfort &amp; nursing care</span>
            <span class="marquee-item">🛁 Gentle skincare &amp; bath time favorites</span>
            <span class="marquee-item">🎀 Newborn hampers &amp; gift sets</span>
            <span class="marquee-item">🍼 BPA-free feeding &amp; mealtime essentials</span>
            <span class="marquee-item">🌟 Tested safe &amp; pediatrician approved</span>
        </div>
    </div>
</section>

<!-- Shop by Category Section -->
<section class="category-section py-5">
    <div class="container">
        <header class="section-header text-center mb-5">
            <h2 class="fw-bold">Shop by Category</h2>
            <p>Find everything tailored specifically for your baby and maternity needs</p>
        </header>
        <div class="category-grid">
            <?php foreach($categories as $cat): ?>
                <?php
                    $category_name = strtolower($cat['category_name']);
                    $category_icon = 'bi-bag-heart';
                    if (strpos($category_name, 'cloth') !== false || strpos($category_name, 'outfit') !== false) {
                        $category_icon = 'bi-person-standing-dress';
                    } elseif (strpos($category_name, 'nurser') !== false || strpos($category_name, 'home') !== false) {
                        $category_icon = 'bi-house-heart';
                    } elseif (strpos($category_name, 'matern') !== false || strpos($category_name, 'nurs') !== false) {
                        $category_icon = 'bi-heart-pulse';
                    } elseif (strpos($category_name, 'skin') !== false || strpos($category_name, 'bath') !== false) {
                        $category_icon = 'bi-droplet';
                    } elseif (strpos($category_name, 'toy') !== false || strpos($category_name, 'gift') !== false) {
                        $category_icon = 'bi-stars';
                    }
                ?>
                <a href="products.php?category=<?php echo $cat['category_id']; ?>" class="category-card">
                    <span class="category-icon" aria-hidden="true"><i class="bi <?php echo $category_icon; ?>"></i></span>
                    <span class="category-name"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                    <span class="category-count"><?php echo $cat['product_count']; ?> Items</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 fz-featured-section">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded fw-semibold small mb-2 d-inline-block">
                    <i class="bi bi-stars me-1"></i> Top Picks
                </span>
                <h2 class="fw-bold mb-1">Featured Arrivals</h2>
                <p class="text-muted mb-0">Handpicked essentials for your baby and maternity journey</p>
            </div>
            <div>
                <a href="products.php" class="btn btn-fz-outline btn-sm ms-md-2">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <?php if(!empty($featured_products)): ?>
            <div class="fz-product-scroller" role="region" aria-label="Featured products" tabindex="0">
                <div class="fz-product-scroll-track">
                <?php foreach($featured_products as $p): ?>
                    <div class="fz-scroll-card-wrap">
                        <div class="fz-product-card h-100">
                            <div class="fz-card-img-wrap">
                                <span class="fz-badge-color"><i class="bi bi-palette me-1"></i><?php echo htmlspecialchars($p['color']); ?></span>
                                <a href="product-detail.php?id=<?php echo $p['product_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($p['product_img']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                                </a>
                            </div>
                            <div class="fz-card-body d-flex flex-column">
                                <div>
                                    <span class="category-pill"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                </div>
                                <a href="product-detail.php?id=<?php echo $p['product_id']; ?>" class="fz-product-title">
                                    <?php echo htmlspecialchars($p['product_name']); ?>
                                </a>
                                <div class="fz-product-price mt-auto pt-2">
                                    <?php echo CURRENCY . number_format($p['price'], 2); ?>
                                </div>
                                <div class="d-grid mt-2">
                                    <button type="button" class="btn btn-fz-primary btn-sm add-to-cart-btn" data-product-id="<?php echo $p['product_id']; ?>">
                                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No products uploaded yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us / Features -->
<section class="fz-features py-5">
    <div class="container">
        <header class="fz-features-header text-center mb-5">
            <span class="fz-features-eyebrow">The Fazobeth Difference</span>
            <h2 class="fw-bold">Thoughtful shopping, from basket to doorstep.</h2>
            <p class="fz-features-lead">Every detail is designed around you—fast delivery, flexible pickup, secure payments, and genuine support.</p>
        </header>
        <div class="fz-features-grid">
            <article class="fz-feature-card" data-feature="delivery">
                <div class="fz-feature-icon">
                    <i class="bi bi-truck"></i>
                    <span class="fz-feature-badge">01</span>
                </div>
                <h3 class="fz-feature-title">To Your Doorstep</h3>
                <p class="fz-feature-desc">Choose home delivery at checkout and we'll bring your order straight to you—anywhere in our coverage area.</p>
            </article>
            <article class="fz-feature-card" data-feature="pickup">
                <div class="fz-feature-icon">
                    <i class="bi bi-geo-alt"></i>
                    <span class="fz-feature-badge">02</span>
                </div>
                <h3 class="fz-feature-title">Two Free Hubs</h3>
                <p class="fz-feature-desc">Collect from our designated pickup locations at no extra cost—convenient, quick, and completely free.</p>
            </article>
            <article class="fz-feature-card" data-feature="payment">
                <div class="fz-feature-icon">
                    <i class="bi bi-shield-lock"></i>
                    <span class="fz-feature-badge">03</span>
                </div>
                <h3 class="fz-feature-title">Secure Checkout</h3>
                <p class="fz-feature-desc">Pay online through Paystack's bank-grade encrypted payment gateway—your data stays protected.</p>
            </article>
            <article class="fz-feature-card" data-feature="care">
                <div class="fz-feature-icon">
                    <i class="bi bi-heart"></i>
                    <span class="fz-feature-badge">04</span>
                </div>
                <h3 class="fz-feature-title">Here to Help</h3>
                <p class="fz-feature-desc">Our team is available to help you choose the right essentials for your growing family.</p>
            </article>
        </div>
        <div class="fz-features-trust row g-3 mt-4">
            <div class="col-6 col-lg-3">
                <div class="fz-trust-item">
                    <i class="bi bi-shield-check"></i>
                    <span>100% Secure</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="fz-trust-item">
                    <i class="bi bi-arrow-return-left"></i>
                    <span>Easy Returns</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="fz-trust-item">
                    <i class="bi bi-gift"></i>
                    <span>Gift Options</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="fz-trust-item">
                    <i class="bi bi-chat-dots"></i>
                    <span>Live Support</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
