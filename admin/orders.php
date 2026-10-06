<?php
require_once __DIR__ . "/partials/header.php";
require_once __DIR__ . "/../classes/Order.php";

$orderObj = new Order();
$msg = '';
$msg_type = 'success';
$allowed_order_statuses = ['cancelled', 'delivered', 'processing', 'shipped'];

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])){
    $order_id = (int)$_POST['order_id'];
    $new_status = strtolower(trim($_POST['order_status'] ?? ''));
    if($order_id > 0 && in_array($new_status, $allowed_order_statuses, true)){
        if($orderObj->update_order_status($order_id, $new_status)){
            $msg = "Order #$order_id status updated to '$new_status'.";
        } else {
            $msg = "Order #$order_id status could not be updated.";
            $msg_type = 'danger';
        }
    } else {
        $msg = 'Choose a valid status before updating the order.';
        $msg_type = 'warning';
    }
}

$orders = $orderObj->get_all_orders();
$selected_id = (int)($_GET['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Customer Orders</h3>
        <p class="text-muted mb-0">Track payments, delivery addresses, and update fulfillment status.</p>
    </div>
</div>

<?php if(!empty($msg)): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card admin-card bg-white p-4">
    <?php if(!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table align-middle admin-orders-table">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer Details</th>
                        <th>Total Amount</th>
                        <th>Delivery Type</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Change Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                        <tr class="<?php echo ($selected_id == $o['order_id']) ? 'table-warning' : ''; ?>">
                            <td>
                                <strong class="text-danger d-block">#<?php echo htmlspecialchars($o['order_number']); ?></strong>
                                <span class="small text-muted"><?php echo date("M d, Y H:i", strtotime($o['created_at'])); ?></span>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?php echo htmlspecialchars($o['firstname'] . ' ' . $o['lastname']); ?></strong>
                                <span class="small text-muted d-block"><?php echo htmlspecialchars($o['email']); ?></span>
                                <span class="small text-secondary"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($o['delivery_type'] === 'home_delivery' ? $o['delivery_address'] : $o['pickup_location']); ?></span>
                            </td>
                            <td class="fw-bold text-dark fs-6">
                                <?php echo CURRENCY . number_format($o['total_amount'], 2); ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo ($o['delivery_type'] === 'home_delivery') ? 'House Delivery' : 'Hub Pickup'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="bi bi-check-circle me-1"></i> Paid
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fw-bold">
                                    <?php echo ucfirst($o['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                    <form action="orders.php" method="POST" class="admin-order-status-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                        <?php $has_valid_status = in_array($o['order_status'], $allowed_order_statuses, true); ?>
                                        <input type="hidden" name="order_status" value="<?php echo $has_valid_status ? htmlspecialchars($o['order_status']) : ''; ?>">
                                        <div class="dropdown admin-order-status-dropdown">
                                            <button type="button" class="btn dropdown-toggle admin-order-status-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Choose status for order <?php echo htmlspecialchars($o['order_number']); ?>">
                                                <span class="admin-order-status-label"><?php echo $has_valid_status ? ucfirst($o['order_status']) : 'Choose status'; ?></span>
                                            </button>
                                            <ul class="dropdown-menu admin-order-status-menu">
                                                <?php foreach($allowed_order_statuses as $status): ?>
                                                    <li><button type="button" class="dropdown-item" data-order-status="<?php echo $status; ?>"><?php echo ucfirst($status); ?></button></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <button type="submit" class="btn admin-order-status-save" title="Save status" aria-label="Save status for order <?php echo htmlspecialchars($o['order_number']); ?>" <?php echo $has_valid_status ? '' : 'disabled'; ?>>
                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-check fs-1 text-muted"></i>
            <h4 class="fw-bold mt-3 text-secondary">No customer orders yet</h4>
            <p class="text-muted mb-0">When customers place orders, they will appear right here for processing.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>

<script>
document.querySelectorAll('.admin-order-status-form').forEach(function(form){
    var statusInput = form.querySelector('input[name="order_status"]');
    var statusLabel = form.querySelector('.admin-order-status-label');
    var saveButton = form.querySelector('.admin-order-status-save');

    form.querySelectorAll('[data-order-status]').forEach(function(option){
        option.addEventListener('click', function(){
            statusInput.value = option.dataset.orderStatus;
            statusLabel.textContent = option.textContent.trim();
            saveButton.disabled = false;
        });
    });
});
</script>
