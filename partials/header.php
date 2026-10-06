<?php
require_once __DIR__ . "/../classes/Config.php";
require_once __DIR__ . "/../classes/Cart.php";

$cartObj = new Cart();
$cart_count = $cartObj->get_total_count();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " - " . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="shortcut icon" href="assets/images/logo.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?php echo ($current_page === 'cart.php') ? 'cart-page' : ''; ?>">

<!-- Navbar -->
<div class="store-announcement">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <span><i class="bi bi-heart-fill me-2"></i>Thoughtful essentials for every little beginning</span>
        <span class="announcement-detail">Choose home delivery or collection at checkout</span>
    </div>
</div>
<nav class="navbar navbar-expand-lg fz-navbar sticky-top">
    <div class="container">
        <!-- Brand Logo & Title -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/images/logo.png" alt="Fazobeth Ventures logo">
            <span class="brand-copy"><strong>Fazobeth Ventures</strong><small>Mother &amp; baby essentials</small></span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#fazobethNavbar" aria-controls="fazobethNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="fazobethNavbar">
            <!-- Nav Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link fz-nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fz-nav-link <?php echo ($current_page == 'products.php') ? 'active' : ''; ?>" href="products.php">Shop</a>
                </li>
            </ul>

            <!-- Search Bar in Header -->
            <form action="products.php" method="GET" class="d-none d-lg-flex me-3 mb-2 mb-lg-0 fz-search-form">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-sm border-end-0 rounded-start-pill ps-3" placeholder="Search essentials" aria-label="Search products" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-secondary btn-sm border-start-0 rounded-end-pill px-3" type="submit" aria-label="Submit product search">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <!-- Action Buttons: Cart & Auth -->
            <div class="fz-nav-actions d-flex align-items-center gap-2">
                <!-- Cart Button -->
                <a href="cart.php" class="btn cart-btn text-dark text-decoration-none d-flex align-items-center gap-1">
                    <i class="bi bi-bag-heart-fill fs-5 text-danger"></i>
                    <span class="d-none d-md-inline fw-semibold">Cart</span>
                    <span class="cart-badge" id="cart-badge-count"><?php echo $cart_count; ?></span>
                </a>

                <!-- User Account Dropdown / Auth Link -->
                <?php if(isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-fz-outline btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user']['firstname']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="my-orders.php"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="logout.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-fz-primary btn-sm px-3">
                        <i class="bi bi-person me-1"></i> Login / Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if($current_page === 'index.php' || $current_page === 'products.php'): ?>
    <a href="cart.php" id="floating-cart-link" class="floating-cart-link" title="Open your cart" aria-label="Open your cart, <?php echo (int)$cart_count; ?> items">
        <i class="bi bi-bag-heart-fill" aria-hidden="true"></i>
        <span class="floating-cart-count" id="floating-cart-count"><?php echo (int)$cart_count; ?></span>
    </a>
<?php endif; ?>

<!-- Main Container Wrap -->
<main>
