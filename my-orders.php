<?php
$page_title = "My Orders";
require_once "classes/Config.php";
require_once "classes/Order.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$orderObj = new Order();
$orders = $orderObj->get_user_orders($_SESSION['user']['id']);

require_once "partials/header.php";
?>

<section class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="bi bi-box-seam text-danger me-2"></i>My Orders</h2>
        <p class="text-muted mb-0">Track and view your purchase history with Fazobeth Ventures.</p>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <?php if(!empty($orders)): ?>
            <div class="d-grid gap-4">
                <?php foreach($orders as $o): ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-white p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="fw-bold fs-5 text-dark me-2">Order #<?php echo htmlspecialchars($o['order_number']); ?></span>
                                <span class="small text-muted">Placed on <?php echo date("M d, Y", strtotime($o['created_at'])); ?></span>
                            </div>
                            <div>
                                <span class="badge bg-danger-subtle text-danger fs-6 me-2">
                                    Status: <?php echo ucfirst($o['order_status']); ?>
                                </span>
                                <span class="badge bg-success-subtle text-success fs-6">
                                    Payment: <?php echo ucfirst($o['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-8">
                                    <div class="d-grid gap-2">
                                        <?php foreach($o['items'] as $item): ?>
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                <div>
                                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                    <span class="small text-muted ms-2">(x<?php echo $item['quantity']; ?>)</span>
                                                </div>
                                                <span class="small fw-bold"><?php echo CURRENCY . number_format($item['subtotal'], 2); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end border-start-md">
                                    <span class="small text-muted d-block">Delivery Type:</span>
                                    <strong class="d-block mb-2"><?php echo ($o['delivery_type'] === 'home_delivery') ? 'House Delivery' : 'Pickup Hub'; ?></strong>
                                    
                                    <span class="small text-muted d-block">Total Paid:</span>
                                    <h4 class="fw-bold text-danger mb-0"><?php echo CURRENCY . number_format($o['total_amount'], 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-box2 fs-1 text-muted"></i>
                <h4 class="fw-bold mt-3 text-secondary">No orders placed yet</h4>
                <p class="text-muted mb-4">You haven't made any purchases with this account yet.</p>
                <a href="products.php" class="btn btn-fz-primary px-4">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
