<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Product.php";
require_once __DIR__ . "/../classes/Category.php";

$productObj = new Product();
$categoryObj = new Category();

$categories = $categoryObj->get_all_categories();

$success_msg = '';
$error_msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['product_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $color = trim($_POST['color'] ?? 'Multi');
    $stock = (int)($_POST['stock'] ?? 10);
    $description = trim($_POST['description'] ?? '');

    $uploaded_images = [];

    // Handle multiple uploaded images
    if(isset($_FILES['product_images']) && !empty($_FILES['product_images']['name']) && !empty($_FILES['product_images']['name'][0])){
        $uploaded_images = $productObj->upload_multiple_images($_FILES['product_images']);
        if(empty($uploaded_images)){
            $error_msg = "Image upload failed. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
        }
    } elseif(isset($_FILES['product_img']) && $_FILES['product_img']['error'] === UPLOAD_ERR_OK){
        // Backward compatibility if single image input used
        $single = $productObj->upload_image($_FILES['product_img']);
        if($single){
            $uploaded_images[] = $single;
        } else {
            $error_msg = "Image upload failed. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
        }
    }

    if(empty($error_msg)){
        if(empty($name) || $category_id <= 0 || $price <= 0){
            $error_msg = "Please fill in all required fields (Product Name, Category, and Price).";
        } else {
            $product_id = $productObj->add_product_with_images($name, $category_id, $price, $color, $description, $uploaded_images, $stock, 'available');
            if($product_id){
                $img_count = count($uploaded_images);
                $success_msg = "Product '$name' published successfully" . ($img_count > 0 ? " with $img_count picture(s)!" : "!");
            } else {
                $error_msg = "Database error saving product.";
            }
        }
    }
}
?>

<div class="admin-page-heading mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Upload New Product</h3>
        <p class="text-muted mb-0">Upload product photos, set front-face picture, side views, price, and extra details.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card admin-card bg-white p-4 p-md-5">
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                    <div class="mt-2">
                        <a href="add-product.php" class="btn btn-sm btn-success me-2">Upload Another Product</a>
                        <a href="products.php" class="btn btn-sm btn-outline-success">View Products List</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="add-product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-4">
                    <!-- Product Title -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Product Name / Title <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control form-control-lg" placeholder="e.g. Soft Organic Baby Onesie" required value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
                    </div>

                    <!-- Category -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select form-select-lg" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Price (₦) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g. 8500" required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                    </div>

                    <!-- Color -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Color / Tag</label>
                        <input type="text" name="color" class="form-control" placeholder="e.g. Pink, Blue, White, Pastel" value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : 'Multi'; ?>">
                    </div>

                    <!-- Stock Quantity -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" value="10" min="1">
                    </div>

                    <!-- Multiple Image Upload -->
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            Product Pictures (Upload Multiple Files)
                            <span class="badge bg-secondary-subtle text-dark ms-2 fw-normal">Select 1 or more images</span>
                        </label>
                        <input type="file" name="product_images[]" id="product_images_input" class="form-control form-control-lg" accept="image/*" multiple>
                        <div class="form-text text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i> You can select multiple images at once (front view, side views, back view). 
                            <strong>The 1st picture selected will be the front-face picture</strong> shown on catalog cards, and all pictures will be viewable in the gallery when shoppers click to view product details.
                        </div>

                        <!-- Live Preview Box -->
                        <div id="preview_container" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                            <div class="fw-semibold text-secondary mb-2 small text-uppercase">Selected Pictures Preview:</div>
                            <div id="preview_grid" class="d-flex flex-wrap gap-3"></div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Extra Product Information & Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter details about size, material care, age suitability, features..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold shadow-sm" style="background-color: #e83e8c; border:none;">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Save & Publish Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('product_images_input').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview_container');
    const previewGrid = document.getElementById('preview_grid');
    previewGrid.innerHTML = '';

    const files = Array.from(e.target.files);
    if(files.length === 0){
        previewContainer.style.display = 'none';
        return;
    }

    previewContainer.style.display = 'block';

    files.forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            const card = document.createElement('div');
            card.className = 'border rounded p-2 bg-white text-center position-relative shadow-sm';
            card.style.width = '130px';

            const badge = document.createElement('span');
            badge.className = index === 0 
                ? 'badge bg-success position-absolute top-0 start-50 translate-middle-x mt-1' 
                : 'badge bg-secondary position-absolute top-0 start-50 translate-middle-x mt-1';
            badge.style.fontSize = '0.65rem';
            badge.innerText = index === 0 ? 'Front Face (Main)' : `Side View #${index}`;

            const img = document.createElement('img');
            img.src = evt.target.result;
            img.className = 'rounded mt-3';
            img.style.width = '110px';
            img.style.height = '110px';
            img.style.objectFit = 'contain';

            const nameSpan = document.createElement('div');
            nameSpan.className = 'small text-truncate mt-1 text-muted';
            nameSpan.style.maxWidth = '110px';
            nameSpan.title = file.name;
            nameSpan.innerText = file.name;

            card.appendChild(badge);
            card.appendChild(img);
            card.appendChild(nameSpan);
            previewGrid.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
