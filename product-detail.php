<?php
require_once "classes/Config.php";
require_once "classes/Product.php";

$productObj = new Product();
$id = $_GET['id'] ?? 0;
$product = $productObj->get_product_by_id($id);

if(!$product){
    header("Location: products.php");
    exit;
}

$page_title = $product['product_name'];
require_once "partials/header.php";

// Fetch related products from same category
$related_products = $productObj->search_and_filter('', $product['category_id'], '', '', '', 'newest');

// Fetch product gallery images
$product_images = $productObj->get_product_images($id);
if(empty($product_images)){
    $product_images = [[
        'image_id' => 0,
        'product_id' => $id,
        'image_path' => $product['product_img'],
        'is_primary' => 1
    ]];
}
?>

<!-- Breadcrumb -->
<section class="bg-light py-3 border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['product_name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail Container -->
<section class="product-detail-section mb-5">
    <div class="container">
        <div class="product-detail-layout">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="product-detail-gallery">
                        <div class="product-detail-media">
                            <span class="product-detail-category"><i class="bi bi-grid me-1"></i><?php echo htmlspecialchars($product['category_name']); ?></span>
                            
                            <button type="button" class="gallery-expand-btn" id="open-lightbox-btn" title="Click to view full screen">
                                <i class="bi bi-arrows-fullscreen"></i> Full View
                            </button>

                            <img id="main-product-img" src="<?php echo htmlspecialchars($product['product_img']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" title="Click to expand full picture" onerror="this.onerror=null; this.src='assets/images/logo.png';">

                            <?php if(count($product_images) > 1): ?>
                                <button type="button" class="gallery-nav-btn gallery-nav-prev" id="prev-img-btn" aria-label="Previous image">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="gallery-nav-btn gallery-nav-next" id="next-img-btn" aria-label="Next image">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if(count($product_images) > 1): ?>
                            <div class="product-gallery-thumbnails" role="tablist" aria-label="Product side angles">
                                <?php foreach($product_images as $idx => $pimg): 
                                    $is_active = ($pimg['image_path'] === $product['product_img']) || ($idx === 0 && empty($product['product_img']));
                                ?>
                                    <button type="button" 
                                            class="gallery-thumb-btn <?php echo $is_active ? 'active' : ''; ?>" 
                                            data-src="<?php echo htmlspecialchars($pimg['image_path']); ?>"
                                            data-index="<?php echo $idx; ?>"
                                            title="View side picture <?php echo $idx + 1; ?>"
                                            aria-label="View picture <?php echo $idx + 1; ?>">
                                        <img src="<?php echo htmlspecialchars($pimg['image_path']); ?>" alt="Side angle <?php echo $idx + 1; ?>" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="product-detail-info">
                        <div class="product-detail-stock <?php echo ($product['stock'] > 0) ? 'is-available' : 'is-unavailable'; ?>">
                            <i class="bi <?php echo ($product['stock'] > 0) ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                            <?php if($product['stock'] > 0): ?>
                                In stock <span><?php echo (int)$product['stock']; ?> available</span>
                            <?php else: ?>
                                Currently unavailable
                            <?php endif; ?>
                        </div>

                        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

                        <div class="product-detail-color">
                            <span>Color</span>
                            <strong><?php echo htmlspecialchars($product['color']); ?></strong>
                        </div>

                        <div class="product-detail-price"><?php echo CURRENCY . number_format($product['price'], 2); ?></div>

                        <?php if(!empty($product['description'])): ?>
                            <p class="product-detail-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <?php endif; ?>

                        <div class="product-purchase-controls">
                            <div class="product-quantity-control">
                                <label for="qty-input-<?php echo $product['product_id']; ?>">Quantity</label>
                                <input type="number" id="qty-input-<?php echo $product['product_id']; ?>" class="form-control" value="1" min="1" max="<?php echo max(1, $product['stock']); ?>" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                            </div>
                            <button type="button" class="btn btn-fz-primary add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="bi bi-cart-plus me-2"></i> Add to cart
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <aside class="product-delivery-panel" aria-labelledby="delivery-panel-title">
                        <h2 id="delivery-panel-title"><i class="bi bi-truck me-2"></i>Delivery & pickup</h2>
                        <p class="delivery-panel-intro">Choose how you would like to receive your order at checkout.</p>

                        <div class="delivery-method">
                            <span class="delivery-method-icon"><i class="bi bi-house-door"></i></span>
                            <div>
                                <strong>Doorstep delivery</strong>
                                <span>Delivery fee</span>
                            </div>
                            <b><?php echo CURRENCY . number_format(HOME_DELIVERY_FEE, 0); ?></b>
                        </div>

                        <div class="delivery-method">
                            <span class="delivery-method-icon"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <strong>Pickup Location 1</strong>
                                <span><?php echo htmlspecialchars(PICKUP_LOCATION_1); ?></span>
                            </div>
                            <b class="delivery-free">Free</b>
                        </div>

                        <div class="delivery-method">
                            <span class="delivery-method-icon"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <strong>Pickup Location 2</strong>
                                <span><?php echo htmlspecialchars(PICKUP_LOCATION_2); ?></span>
                            </div>
                            <b class="delivery-free">Free</b>
                        </div>

                        <div class="delivery-payment-note">
                            <i class="bi bi-shield-check"></i>
                            <span>Online payments are securely processed by Paystack at checkout.</span>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if(count($related_products) > 1): ?>
            <div class="mt-5">
                <h4 class="fw-bold mb-4">Related Products</h4>
                <div class="row g-4">
                    <?php 
                    $count = 0;
                    foreach($related_products as $rp):
                        if($rp['product_id'] == $product['product_id']) continue;
                        if($count >= 4) break;
                        $count++;
                    ?>
                        <div class="col-6 col-lg-3">
                            <div class="fz-product-card">
                                <div class="fz-card-img-wrap">
                                    <span class="fz-badge-color"><?php echo htmlspecialchars($rp['color']); ?></span>
                                    <a href="product-detail.php?id=<?php echo $rp['product_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($rp['product_img']); ?>" alt="<?php echo htmlspecialchars($rp['product_name']); ?>" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                                    </a>
                                </div>
                                <div class="fz-card-body">
                                    <a href="product-detail.php?id=<?php echo $rp['product_id']; ?>" class="fz-product-title">
                                        <?php echo htmlspecialchars($rp['product_name']); ?>
                                    </a>
                                    <div class="fz-product-price mt-auto">
                                        <?php echo CURRENCY . number_format($rp['price'], 2); ?>
                                    </div>
                                    <div class="d-grid mt-2">
                                        <button type="button" class="btn btn-fz-primary btn-sm add-to-cart-btn" data-product-id="<?php echo $rp['product_id']; ?>">
                                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Fullscreen Lightbox Modal (Full Display) -->
<div class="fz-lightbox" id="product-lightbox" role="dialog" aria-modal="true" aria-label="Product Full View">
    <button type="button" class="fz-lightbox-close" id="lightbox-close-btn" aria-label="Close full view">&times;</button>
    <?php if(count($product_images) > 1): ?>
        <button type="button" class="fz-lightbox-nav fz-lightbox-prev" id="lightbox-prev-btn" aria-label="Previous image">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="fz-lightbox-nav fz-lightbox-next" id="lightbox-next-btn" aria-label="Next image">
            <i class="bi bi-chevron-right"></i>
        </button>
    <?php endif; ?>
    <div class="fz-lightbox-img-wrap" id="lightbox-wrap">
        <img src="<?php echo htmlspecialchars($product['product_img']); ?>" id="lightbox-img" class="fz-lightbox-img" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainImg = document.getElementById('main-product-img');
    const thumbBtns = document.querySelectorAll('.gallery-thumb-btn');
    const prevBtn = document.getElementById('prev-img-btn');
    const nextBtn = document.getElementById('next-img-btn');
    const expandBtn = document.getElementById('open-lightbox-btn');
    const lightbox = document.getElementById('product-lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.getElementById('lightbox-close-btn');
    const lightboxPrev = document.getElementById('lightbox-prev-btn');
    const lightboxNext = document.getElementById('lightbox-next-btn');

    let currentIndex = 0;
    if (thumbBtns.length > 0) {
        thumbBtns.forEach((btn, idx) => {
            if (btn.classList.contains('active')) currentIndex = idx;
        });
    }

    function switchImage(index) {
        if (thumbBtns.length === 0) return;
        if (index < 0) index = thumbBtns.length - 1;
        if (index >= thumbBtns.length) index = 0;
        currentIndex = index;

        const targetBtn = thumbBtns[currentIndex];
        const newSrc = targetBtn.getAttribute('data-src');

        // Smooth transition effect on main image
        if (mainImg) {
            mainImg.style.opacity = '0.25';
            setTimeout(() => {
                mainImg.src = newSrc;
                mainImg.style.opacity = '1';
            }, 120);
        }

        // Update lightbox image if open
        if (lightboxImg) {
            lightboxImg.src = newSrc;
        }

        thumbBtns.forEach(btn => btn.classList.remove('active'));
        targetBtn.classList.add('active');
        targetBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    thumbBtns.forEach((btn, idx) => {
        btn.addEventListener('click', function() {
            switchImage(idx);
        });
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            switchImage(currentIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            switchImage(currentIndex + 1);
        });
    }

    // Lightbox Controls
    function openLightbox() {
        if (!lightbox) return;
        const currentSrc = mainImg ? mainImg.src : '<?php echo htmlspecialchars($product['product_img']); ?>';
        if (lightboxImg) lightboxImg.src = currentSrc;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mainImg) {
        mainImg.addEventListener('click', openLightbox);
    }
    if (expandBtn) {
        expandBtn.addEventListener('click', openLightbox);
    }
    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target.id === 'lightbox-wrap') {
                closeLightbox();
            }
        });
    }

    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', function(e) {
            e.stopPropagation();
            switchImage(currentIndex - 1);
        });
    }
    if (lightboxNext) {
        lightboxNext.addEventListener('click', function(e) {
            e.stopPropagation();
            switchImage(currentIndex + 1);
        });
    }

    // Keyboard navigation (Arrow keys + Escape key)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox && lightbox.classList.contains('active')) {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            if ((lightbox && lightbox.classList.contains('active')) || document.activeElement.closest('.product-detail-gallery')) {
                e.preventDefault();
                switchImage(currentIndex - 1);
            }
        } else if (e.key === 'ArrowRight') {
            if ((lightbox && lightbox.classList.contains('active')) || document.activeElement.closest('.product-detail-gallery')) {
                e.preventDefault();
                switchImage(currentIndex + 1);
            }
        }
    });
});
</script>

<?php require_once "partials/footer.php"; ?>
