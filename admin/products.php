<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Product.php";

$productObj = new Product();

$msg = $_GET['msg'] ?? '';

// Handle Delete Request
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete'){
    $delete_id = (int)($_POST['id'] ?? 0);
    if($delete_id > 0){
        $productObj->delete_product($delete_id);
        header("Location: products.php?msg=deleted");
        exit;
    }
}

$products = $productObj->get_all_products();
?>

<div class="admin-page-heading mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Manage Inventory Products</h3>
        <p class="text-muted mb-0">View, edit, or remove store products.</p>
    </div>
</div>

<?php if($msg === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Product deleted successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card bg-white p-4">
    <?php if(!empty($products)): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Color</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($p['product_img']); ?>" alt="" style="width: 50px; height: 50px; object-fit: contain;" class="border rounded bg-light p-1" onerror="this.onerror=null; this.src='../assets/images/logo.png';">
                            </td>
                            <td>
                                <a href="../product-detail.php?id=<?php echo $p['product_id']; ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                    <?php echo htmlspecialchars($p['product_name']); ?>
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category_name']); ?></span></td>
                            <td class="fw-bold text-danger"><?php echo CURRENCY . number_format($p['price'], 2); ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($p['color']); ?></span></td>
                            <td><span class="admin-stock-count"><?php echo $p['stock']; ?> left</span></td>
                            <td>
                                <?php if($p['stock'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="admin-product-actions" role="group" aria-label="Actions for <?php echo htmlspecialchars($p['product_name']); ?>">
                                <a href="edit-product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-primary admin-product-edit" title="Edit product">
                                    <i class="bi bi-pencil" aria-hidden="true"></i><span>Edit</span>
                                </a>
                                <form action="products.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['product_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger admin-product-delete" title="Delete product">
                                        <i class="bi bi-trash" aria-hidden="true"></i><span>Delete</span>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <h4 class="fw-bold mt-3 text-secondary">No products uploaded yet</h4>
            <p class="text-muted mb-4">Click below to upload the first product picture and details for your mom's store!</p>
            <a href="add-product.php" class="btn btn-danger px-4" style="background-color: #e83e8c; border:none;">Upload First Product</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
