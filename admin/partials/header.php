<?php
require_once __DIR__ . "/../../classes/Config.php";
require_once __DIR__ . "/../../classes/Admin.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_admin_page = basename($_SERVER['PHP_SELF']);

if($current_admin_page !== 'login.php' && empty($_SESSION['admin']['id'])){
    header("Location: login.php");
    exit;
}

if($current_admin_page !== 'login.php' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $session_csrf = (string)($_SESSION['csrf_token'] ?? '');
    $request_csrf = (string)($_POST['csrf_token'] ?? '');
    if($session_csrf === '' || !hash_equals($session_csrf, $request_csrf)){
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fazobeth Ventures</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/admin.css'); ?>">
</head>
<body class="admin-body">

<?php if(!empty($_SESSION['admin']['id'])): ?>
<div class="d-flex admin-shell">
    <!-- Sidebar -->
    <aside class="admin-sidebar offcanvas-lg offcanvas-start" id="adminSidebar" tabindex="-1" aria-labelledby="adminSidebarTitle">
        <div class="admin-brand d-flex align-items-center gap-2">
            <img src="../assets/images/logo.png" alt="Fazobeth Ventures logo" class="bg-white p-1 rounded">
            <div>
                <h6 class="fw-bold mb-0" id="adminSidebarTitle">Fazobeth Ventures</h6>
                <span class="small text-muted">Store Manager</span>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close admin menu"></button>
        </div>

        <div class="offcanvas-body">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo ($current_admin_page == 'index.php') ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="products.php" class="nav-link <?php echo ($current_admin_page == 'products.php' || $current_admin_page == 'edit-product.php') ? 'active' : ''; ?>">
                        <i class="bi bi-box-seam me-2"></i> Products List
                    </a>
                </li>
                <li>
                    <a href="add-product.php" class="nav-link <?php echo ($current_admin_page == 'add-product.php') ? 'active' : ''; ?>">
                        <i class="bi bi-plus-circle me-2"></i> Upload Product
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="nav-link <?php echo ($current_admin_page == 'categories.php') ? 'active' : ''; ?>">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="nav-link <?php echo ($current_admin_page == 'orders.php') ? 'active' : ''; ?>">
                        <i class="bi bi-bag-check me-2"></i> Customer Orders
                    </a>
                </li>
                <li class="mt-3 border-top pt-3">
                    <a href="../index.php" target="_blank" rel="noopener noreferrer" class="nav-link text-info">
                        <i class="bi bi-globe me-2"></i> View Live Store
                    </a>
                </li>
            </ul>

            <div class="admin-user border-top pt-3 mt-3 px-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="admin-user-name"><i class="bi bi-person-circle" aria-hidden="true"></i> <?php echo htmlspecialchars($_SESSION['admin']['username']); ?></span>
                    <form action="logout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="admin-logout-btn" title="Log out" aria-label="Log out"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Logout</span></button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <div class="admin-mobilebar d-lg-none">
            <button type="button" class="admin-menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open admin menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <span>Store administration</span>
        </div>
<?php endif; ?>
