<?php
require_once __DIR__ . "/classes/Config.php";
require_once __DIR__ . "/classes/Product.php";

$productObj = new Product();

$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$color = $_GET['color'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$products = $productObj->search_and_filter($search, $category_id, $color, $min_price, $max_price, $sort);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Showing <strong><?php echo count($products); ?></strong> product(s)</p>
</div>

<?php if(!empty($products)): ?>
    <div class="row g-4">
        <?php foreach($products as $p): ?>
            <div class="col-6 col-lg-3">
                <div class="fz-product-card">
                    <div class="fz-card-img-wrap">
                        <span class="fz-badge-color"><i class="bi bi-palette me-1"></i><?php echo htmlspecialchars($p['color']); ?></span>
                        <a href="product-detail.php?id=<?php echo $p['product_id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['product_img']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                        </a>
                    </div>
                    <div class="fz-card-body">
                        <div>
                            <span class="category-pill"><?php echo htmlspecialchars($p['category_name']); ?></span>
                        </div>
                        <a href="product-detail.php?id=<?php echo $p['product_id']; ?>" class="fz-product-title">
                            <?php echo htmlspecialchars($p['product_name']); ?>
                        </a>
                        <p class="small text-muted mb-2 text-truncate"><?php echo htmlspecialchars($p['description']); ?></p>
                        <div class="fz-product-price mt-auto">
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
<?php else: ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm my-4">
        <i class="bi bi-search fs-1 text-muted"></i>
        <h4 class="fw-bold mt-3 text-secondary">No matching products found</h4>
        <p class="text-muted">Try clearing some of your filters or searching for another item.</p>
    </div>
<?php endif; ?>
