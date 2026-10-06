<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Category.php";

$catObj = new Category();

$msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['add_category'])){
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['category_desc'] ?? '');
        if(!empty($name)){
            $catObj->add_category($name, $desc);
            $msg = "Category added successfully!";
        }
    }

    if(isset($_POST['edit_category'])){
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['category_desc'] ?? '');
        if($id > 0 && !empty($name)){
            $catObj->update_category($id, $name, $desc);
            $msg = "Category updated successfully!";
        }
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete'){
    $delete_id = (int)($_POST['id'] ?? 0);
    if($delete_id > 0){
        $catObj->delete_category($delete_id);
        $msg = "Category deleted.";
    }
}

$categories = $catObj->get_all_categories();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Store Categories</h3>
        <p class="text-muted mb-0">Organize products into baby & maternity departments.</p>
    </div>
</div>

<?php if(!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Category Form -->
    <div class="col-md-4">
        <div class="card admin-card admin-category-form bg-white p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-plus-square text-danger me-2"></i>Add Category</h5>
            <form action="categories.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="add_category" value="1">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name</label>
                    <input type="text" name="category_name" class="form-control" placeholder="e.g. Baby Toys" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="category_desc" class="form-control" rows="3" placeholder="Brief info about category"></textarea>
                </div>
                <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #e83e8c; border:none;">
                    Save Category
                </button>
            </form>
        </div>
    </div>

    <!-- Category List Table -->
    <div class="col-md-8">
        <div class="card admin-card bg-white p-4">
            <h5 class="fw-bold mb-3">Existing Categories</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Products Count</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $c): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($c['category_name']); ?></td>
                                <td class="small text-muted"><?php echo htmlspecialchars($c['category_desc']); ?></td>
                                <td><span class="badge bg-danger-subtle text-danger fs-6"><?php echo $c['product_count']; ?></span></td>
                                <td class="text-end">
                                    <form action="categories.php" method="POST" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$c['category_id']; ?>">
                                        <button type="submit" class="btn btn-sm admin-category-delete" title="Delete category" aria-label="Delete category <?php echo htmlspecialchars($c['category_name']); ?>">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
