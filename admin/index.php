<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Order.php";

$orderObj = new Order();
$analytics = $orderObj->get_dashboard_analytics();
?>

<!-- Header title -->
<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        <span class="admin-eyebrow">Overview</span>
        <h3 class="fw-bold mb-1 text-dark">Store dashboard</h3>
        <p class="text-muted mb-0">Sales, orders, and inventory at a glance.</p>
    </div>
    <div>
        <a href="add-product.php" class="btn btn-danger admin-primary-action px-3 py-2 fw-semibold">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Upload product
        </a>
    </div>
</div>

<!-- Key Stat Cards Row -->
<div class="row g-3 g-xl-4 mb-4 admin-stat-grid">
    <!-- Total Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card admin-stat-card admin-stat-revenue h-100">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="admin-stat-copy">
                    <span class="admin-stat-label">Total revenue</span>
                    <h3 class="admin-stat-value"><span class="admin-stat-currency"><?php echo CURRENCY; ?></span><?php echo number_format($analytics['total_revenue'], 2); ?></h3>
                </div>
                <span class="admin-stat-icon admin-stat-icon-revenue"><i class="bi bi-currency-exchange" aria-hidden="true"></i></span>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card admin-stat-card admin-stat-orders h-100">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="admin-stat-copy">
                    <span class="admin-stat-label">Total orders</span>
                    <h3 class="admin-stat-value"><?php echo number_format($analytics['total_orders']); ?></h3>
                </div>
                <span class="admin-stat-icon admin-stat-icon-orders"><i class="bi bi-bag-check" aria-hidden="true"></i></span>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card admin-stat-card admin-stat-products h-100">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="admin-stat-copy">
                    <span class="admin-stat-label">Active products</span>
                    <h3 class="admin-stat-value"><?php echo number_format($analytics['total_products']); ?></h3>
                </div>
                <span class="admin-stat-icon admin-stat-icon-products"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
            </div>
        </div>
    </div>

    <!-- Total Registered Users -->
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card admin-stat-card admin-stat-users h-100">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div class="admin-stat-copy">
                    <span class="admin-stat-label">Registered users</span>
                    <h3 class="admin-stat-value"><?php echo number_format($analytics['total_users']); ?></h3>
                </div>
                <span class="admin-stat-icon admin-stat-icon-users"><i class="bi bi-people" aria-hidden="true"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card admin-card admin-table-panel bg-white p-4">
    <div class="admin-panel-heading d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <span class="admin-eyebrow">Fulfillment</span>
            <h5 class="fw-bold mb-0">Recent customer orders</h5>
        </div>
        <a href="orders.php" class="btn btn-outline-secondary btn-sm">View All Orders</a>
    </div>

    <?php if(!empty($analytics['recent_orders'])): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($analytics['recent_orders'] as $ord): ?>
                        <tr>
                            <td class="fw-bold text-danger">#<?php echo htmlspecialchars($ord['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($ord['firstname'] . ' ' . $ord['lastname']); ?></td>
                            <td class="small text-muted"><?php echo date("M d, Y", strtotime($ord['created_at'])); ?></td>
                            <td class="fw-bold"><?php echo CURRENCY . number_format($ord['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo ($ord['delivery_type'] === 'home_delivery') ? 'House Delivery' : 'Hub Pickup'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info fw-bold">
                                    <?php echo ucfirst($ord['order_status']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="orders.php?id=<?php echo $ord['order_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-4">
            <p class="text-muted mb-0">No customer orders recorded yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
