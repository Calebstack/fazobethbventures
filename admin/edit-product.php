<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Product.php";
require_once __DIR__ . "/../classes/Category.php";

$productObj = new Product();
$categoryObj = new Category();

$id = (int)($_GET['id'] ?? 0);
$product = $productObj->get_product_by_id($id);

if(!$product){
    header("Location: products.php");
    exit;
}

$msg = $_GET['msg'] ?? '';
$action = $_POST['action'] ?? '';

// Handle setting primary image
if($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'set_primary'){
    $img_id = (int)($_POST['image_id'] ?? 0);
    if($img_id > 0){
        $productObj->set_primary_image($id, $img_id);
        header("Location: edit-product.php?id=$id&msg=primary_set");
        exit;
    }
}

// Handle deleting an image
if($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_img'){
    $img_id = (int)($_POST['image_id'] ?? 0);
    if($img_id > 0){
        $productObj->delete_product_image($img_id, $id);
        header("Location: edit-product.php?id=$id&msg=img_deleted");
        exit;
    }
}

$categories = $categoryObj->get_all_categories();

$success_msg = '';
$error_msg = '';

if($msg === 'primary_set'){
    $success_msg = "Front-face picture updated successfully!";
} elseif($msg === 'img_deleted'){
    $success_msg = "Image removed successfully.";
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['product_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $color = trim($_POST['color'] ?? 'Multi');
    $stock = (int)($_POST['stock'] ?? 10);
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');

    // Current front face image
    $img_path = $product['product_img'];

    // Handle new multiple image uploads
    $new_uploaded = [];
    if(isset($_FILES['product_images']) && !empty($_FILES['product_images']['name']) && !empty($_FILES['product_images']['name'][0])){
        $new_uploaded = $productObj->upload_multiple_images($_FILES['product_images']);
    } elseif(isset($_FILES['product_img']) && $_FILES['product_img']['error'] === UPLOAD_ERR_OK){
        $single = $productObj->upload_image($_FILES['product_img']);
        if($single){
            $new_uploaded[] = $single;
        }
    }

    if(empty($name) || $category_id <= 0 || $price <= 0){
        $error_msg = "Please fill in required fields (Name, Category, Price).";
    } else {
        // Save new images
        if(!empty($new_uploaded)){
            $existing_imgs = $productObj->get_product_images($id);
            $has_existing = !empty($existing_imgs) && $existing_imgs[0]['image_id'] > 0;

            foreach($new_uploaded as $idx => $npath){
                // If product currently has no images or default logo, make the first new one primary
                $make_primary = (!$has_existing && $idx === 0) ? 1 : 0;
                $productObj->add_product_image($id, $npath, $make_primary);
                if($make_primary){
                    $img_path = $npath;
                }
            }
        }

        // Get the latest primary image
        $current_imgs = $productObj->get_product_images($id);
        if(!empty($current_imgs)){
            $img_path = $current_imgs[0]['image_path'];
        }

        $res = $productObj->update_product($id, $name, $category_id, $price, $color, $description, $img_path, $stock, $status);
        if($res){
            $success_msg = "Product updated successfully!";
            $product = $productObj->get_product_by_id($id); // Refresh
        } else {
            $error_msg = "Failed to update product.";
        }
    }
}

// Fetch all product pictures
$product_images = $productObj->get_product_images($id);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Edit Product #<?php echo $id; ?></h3>
        <p class="text-muted mb-0">Update pricing, pictures, side angles, stock, or product descriptions.</p>
    </div>
    <div>
        <a href="products.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card admin-card bg-white p-4 p-md-5">
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Existing Pictures Management Section -->
            <div class="mb-5 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-images me-2 text-danger"></i> Product Pictures Gallery (<?php echo count($product_images); ?>)
                    </h5>
                    <span class="text-muted small">Click "Set as Front Face" to choose the main picture shown on cards</span>
                </div>

                <div class="row g-3">
                    <?php if(!empty($product_images)): ?>
                        <?php foreach($product_images as $pimg): ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="card h-100 border text-center p-2 position-relative shadow-sm <?php echo !empty($pimg['is_primary']) ? 'border-success border-2' : ''; ?>">
                                    <?php if(!empty($pimg['is_primary'])): ?>
                                        <span class="badge bg-success position-absolute top-0 start-50 translate-middle-x mt-1 px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-star-fill me-1"></i> Front Face (Main)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary position-absolute top-0 start-50 translate-middle-x mt-1 px-2 py-1" style="font-size: 0.68rem;">
                                            Side View
                                        </span>
                                    <?php endif; ?>

                                    <div class="d-flex align-items-center justify-content-center mt-3 mb-2" style="height: 120px;">
                                        <img src="../<?php echo htmlspecialchars($pimg['image_path']); ?>" alt="" style="max-height: 110px; max-width: 100%; object-fit: contain;" class="rounded" onerror="this.onerror=null; this.src='../assets/images/logo.png';">
                                    </div>

                                    <div class="mt-auto pt-2 border-top d-flex gap-1 justify-content-center">
                                        <?php if(empty($pimg['is_primary']) && !empty($pimg['image_id'])): ?>
                                            <form action="edit-product.php?id=<?php echo $id; ?>" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="action" value="set_primary">
                                                <input type="hidden" name="image_id" value="<?php echo (int)$pimg['image_id']; ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2" title="Make this the front-face picture" style="font-size: 0.72rem;">
                                                    <i class="bi bi-check2 me-1"></i> Make Front
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if(!empty($pimg['image_id']) && count($product_images) > 1): ?>
                                            <form action="edit-product.php?id=<?php echo $id; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this picture?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="action" value="delete_img">
                                                <input type="hidden" name="image_id" value="<?php echo (int)$pimg['image_id']; ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" title="Delete picture" aria-label="Delete picture" style="font-size: 0.72rem;">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-muted">No pictures uploaded yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Edit Form -->
            <form action="edit-product.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Product Name / Title</label>
                        <input type="text" name="product_name" class="form-control" required value="<?php echo htmlspecialchars($product['product_name']); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Price (₦)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $product['price']; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Color / Tag</label>
                        <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($product['color']); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Availability Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?php echo ($product['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="out_of_stock" <?php echo ($product['status'] === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <!-- Upload Additional Pictures -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold">
                            Upload Additional Pictures / Side Views
                            <span class="badge bg-secondary-subtle text-dark ms-2 fw-normal">Select 1 or more</span>
                        </label>
                        <input type="file" name="product_images[]" id="edit_product_images_input" class="form-control" accept="image/*" multiple>
                        <div class="form-text text-muted">You can select multiple photos to add new side views or alternate angles to this product.</div>

                        <!-- Live preview for newly selected images -->
                        <div id="edit_preview_container" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                            <div class="fw-semibold text-secondary mb-2 small text-uppercase">New Pictures to Add:</div>
                            <div id="edit_preview_grid" class="d-flex flex-wrap gap-3"></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('edit_product_images_input').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('edit_preview_container');
    const previewGrid = document.getElementById('edit_preview_grid');
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
            card.style.width = '110px';

            const badge = document.createElement('span');
            badge.className = 'badge bg-primary position-absolute top-0 start-50 translate-middle-x mt-1';
            badge.style.fontSize = '0.62rem';
            badge.innerText = `New #${index + 1}`;

            const img = document.createElement('img');
            img.src = evt.target.result;
            img.className = 'rounded mt-3';
            img.style.width = '90px';
            img.style.height = '90px';
            img.style.objectFit = 'contain';

            card.appendChild(badge);
            card.appendChild(img);
            previewGrid.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
