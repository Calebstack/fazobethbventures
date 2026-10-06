<?php
$page_title = "Shop the Catalog";
require_once "partials/header.php";
require_once "classes/Category.php";
require_once "classes/Product.php";

$categoryObj = new Category();
$productObj = new Product();

$categories = $categoryObj->get_all_categories();
$colors = $productObj->get_distinct_colors();

$search_param = $_GET['search'] ?? '';
$cat_param = $_GET['category'] ?? '';
$color_param = $_GET['color'] ?? '';
$sort_param = $_GET['sort'] ?? 'newest';
?>

<!-- Header Title -->
<section class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1">Product Catalog</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="text-muted"><i class="bi bi-bag-heart text-danger me-1"></i> Quality Mother & Baby Products</span>
            </div>
        </div>
    </div>
</section>

<!-- Filters and Products -->
<section class="catalog-section mb-5">
    <div class="container">
        <div class="fz-filter-card catalog-filter-bar mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-sliders text-danger me-2"></i>Find your essentials</h5>
                <button type="button" id="clear-filters-btn" class="btn btn-link btn-sm text-decoration-none p-0">Clear filters</button>
            </div>

            <form id="filter-form" class="catalog-filter-grid">
                <div class="catalog-filter-search">
                    <label class="form-label small fw-semibold" for="filter-search">Search products</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="filter-search" name="search" class="form-control" placeholder="Try baby clothes or bottles" value="<?php echo htmlspecialchars($search_param); ?>">
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold" for="filter-category">Category</label>
                    <select id="filter-category" name="category" class="form-select">
                        <option value="all">All categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat_param == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-semibold" for="filter-color">Color</label>
                    <select id="filter-color" name="color" class="form-select">
                        <option value="all">All colors</option>
                        <?php foreach($colors as $clr): ?>
                            <option value="<?php echo htmlspecialchars($clr); ?>" <?php echo (strtolower($color_param) === strtolower($clr)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($clr); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-semibold" for="filter-min-price">Price range (₦)</label>
                    <div class="catalog-price-range">
                        <input type="number" id="filter-min-price" name="min_price" class="form-control" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" min="0">
                        <input type="number" id="filter-max-price" name="max_price" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" min="0">
                    </div>
                </div>

                <div>
                    <label class="form-label small fw-semibold" for="filter-sort">Sort by</label>
                    <select id="filter-sort" name="sort" class="form-select">
                        <option value="newest" <?php echo ($sort_param === 'newest') ? 'selected' : ''; ?>>Newest arrivals</option>
                        <option value="price-low" <?php echo ($sort_param === 'price-low') ? 'selected' : ''; ?>>Price: low to high</option>
                        <option value="price-high" <?php echo ($sort_param === 'price-high') ? 'selected' : ''; ?>>Price: high to low</option>
                        <option value="name-asc" <?php echo ($sort_param === 'name-asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </div>
            </form>
        </div>

        <div id="products-container">
            <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading products...</span>
                </div>
                <p class="text-muted mt-2">Loading catalog...</p>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>

<script>
$(document).ready(function(){
    function fetchProducts(){
        var formData = $('#filter-form').serialize();
        $('#products-container').html('<div class="text-center py-5"><div class="spinner-border text-danger" role="status"></div><p class="text-muted mt-2">Updating products...</p></div>');

        $.ajax({
            url: 'ajax-products.php',
            type: 'GET',
            data: formData,
            success: function(response){
                $('#products-container').html(response);
            },
            error: function(){
                $('#products-container').html('<div class="alert alert-danger">Failed to load products. Please refresh the page.</div>');
            }
        });
    }

    // Initial Load
    fetchProducts();

    // Event listeners on form inputs
    $('#filter-search').on('keyup input', function(){
        clearTimeout(window.searchTimer);
        window.searchTimer = setTimeout(fetchProducts, 300);
    });

    $('#filter-category, #filter-color, #filter-sort').on('change', function(){
        fetchProducts();
    });

    $('#filter-min-price, #filter-max-price').on('change keyup', function(){
        clearTimeout(window.priceTimer);
        window.priceTimer = setTimeout(fetchProducts, 400);
    });

    $('#clear-filters-btn').on('click', function(){
        $('#filter-form')[0].reset();
        $('#filter-search, #filter-min-price, #filter-max-price').val('');
        $('#filter-category').val('all');
        $('#filter-color').val('all');
        $('#filter-sort').val('newest');
        fetchProducts();
    });
});
</script>
