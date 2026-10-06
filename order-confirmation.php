<?php
$page_title = "Order Confirmation";
require_once "classes/Config.php";
require_once "classes/Order.php";

$order_number = $_GET['number'] ?? '';
$orderObj = new Order();
$order = $orderObj->get_order_by_number($order_number);

if(!$order || empty($_SESSION['user']['id']) || (int)$order['user_id'] !== (int)$_SESSION['user']['id']){
    header("Location: index.php");
    exit;
}

require_once "partials/header.php";
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="display-1 text-success mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h2 class="fw-bold">Order Confirmed!</h2>
                        <p class="lead text-secondary">
                            Thank you for shopping at <strong>Fazobeth Ventures</strong>.
                        </p>
                        <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill mb-2">
                            Order #: <?php echo htmlspecialchars($order['order_number']); ?>
                        </span>
                        <p class="small text-muted">A confirmation receipt email has been sent to <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
                    </div>

                    <!-- Delivery Summary Box -->
                    <div class="bg-light rounded-4 p-4 mb-4 border">
                        <h5 class="fw-bold mb-3"><i class="bi bi-truck text-danger me-2"></i>Delivery & Status</h5>
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <strong class="d-block text-muted">Delivery Method:</strong>
                                <span><?php echo ($order['delivery_type'] === 'home_delivery') ? 'House Delivery' : 'Hub Pickup'; ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong class="d-block text-muted">Order Status:</strong>
                                <span class="badge bg-primary px-2 py-1"><?php echo ucfirst($order['order_status']); ?></span>
                            </div>
                            <div class="col-12">
                                <strong class="d-block text-muted">Destination / Location:</strong>
                                <span><?php echo htmlspecialchars($order['delivery_type'] === 'home_delivery' ? $order['delivery_address'] : $order['pickup_location']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h5 class="fw-bold mb-3">Order Details</h5>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo CURRENCY . number_format($item['price'], 2); ?></td>
                                        <td class="text-end fw-bold"><?php echo CURRENCY . number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-4">
                        <span class="fs-5 fw-bold">Total Amount Paid</span>
                        <span class="fs-3 fw-extrabold text-danger"><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="my-orders.php" class="btn btn-fz-primary px-4">
                            <i class="bi bi-box-seam me-2"></i> View My Orders
                        </a>
                        <a href="products.php" class="btn btn-fz-outline px-4">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
