<?php
$page_title = "Checkout";
require_once "classes/Config.php";
require_once "classes/Cart.php";
require_once "classes/User.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php?redirect=checkout");
    exit;
}

$cartObj = new Cart();
$cart_items = $cartObj->get_items();
$subtotal = $cartObj->get_subtotal();

if(empty($cart_items)){
    header("Location: cart.php");
    exit;
}

$user = $_SESSION['user'];
$home_delivery_fee = HOME_DELIVERY_FEE;
$pickup_1 = PICKUP_LOCATION_1;
$pickup_2 = PICKUP_LOCATION_2;
$payments_configured = PAYSTACK_PUBLIC_KEY !== '' && PAYSTACK_SECRET_KEY !== '';

require_once "partials/header.php";
?>

<section class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-check text-danger me-2"></i>Checkout & Payment</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php" class="text-decoration-none">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <form id="checkout-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-4">
                <!-- Delivery & Customer Information -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h4 class="fw-bold mb-3"><i class="bi bi-person-circle text-danger me-2"></i>Customer Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">First Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Last Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['lastname']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Email Address</label>
                                <input type="email" id="customer-email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Phone Number</label>
                                <input type="tel" id="customer-phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method Options -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h4 class="fw-bold mb-3"><i class="bi bi-truck text-danger me-2"></i>Delivery Options</h4>
                        
                        <div class="form-check p-3 border rounded-3 mb-3 delivery-option-box shadow-sm">
                            <input class="form-check-input ms-0 me-2" type="radio" name="delivery_type" id="delivery_home" value="home_delivery" checked>
                            <label class="form-check-label w-100" for="delivery_home">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block text-dark"><i class="bi bi-house-door me-1 text-danger"></i> House / Doorstep Delivery</strong>
                                        <span class="small text-muted">Direct delivery to your specified home address</span>
                                    </div>
                                    <span class="fw-bold text-danger">+<?php echo CURRENCY . number_format($home_delivery_fee, 2); ?></span>
                                </div>
                            </label>
                        </div>

                        <!-- Address input for home delivery -->
                        <div id="home-address-wrap" class="mb-4">
                            <label class="form-label fw-semibold">Delivery Address</label>
                            <textarea id="delivery-address" class="form-control" rows="3" placeholder="Enter your full street address, house number, city and state"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>

                        <h6 class="fw-bold text-muted mb-2 mt-4"><i class="bi bi-geo-alt me-1"></i> Or Pick Up From Our Hubs (FREE):</h6>
                        
                        <div class="form-check p-3 border rounded-3 mb-2 delivery-option-box">
                            <input class="form-check-input ms-0 me-2" type="radio" name="delivery_type" id="delivery_pickup_1" value="pickup_ikeja">
                            <label class="form-check-label w-100" for="delivery_pickup_1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block text-dark"><i class="bi bi-building me-1 text-primary"></i> Pickup Location 1 (Lotto)</strong>
                                        <span class="small text-muted"><?php echo $pickup_1; ?></span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fs-6">FREE</span>
                                </div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded-3 mb-3 delivery-option-box">
                            <input class="form-check-input ms-0 me-2" type="radio" name="delivery_type" id="delivery_pickup_2" value="pickup_lekki">
                            <label class="form-check-label w-100" for="delivery_pickup_2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block text-dark"><i class="bi bi-building me-1 text-primary"></i> Pickup Location 2 (Canaan Land)</strong>
                                        <span class="small text-muted"><?php echo $pickup_2; ?></span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fs-6">FREE</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Paystack Action -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                        <h4 class="fw-bold mb-3">Order Items</h4>

                        <div class="mb-3" style="max-height: 220px; overflow-y: auto;">
                            <?php foreach($cart_items as $item): ?>
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo htmlspecialchars($item['product_img']); ?>" alt="" style="width: 45px; height: 45px; object-fit: contain;" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                                        <div>
                                            <span class="small fw-bold d-block text-truncate" style="max-width: 170px;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="small text-muted"><?php echo $item['quantity']; ?> x <?php echo CURRENCY . number_format($item['price'], 2); ?></span>
                                        </div>
                                    </div>
                                    <span class="fw-semibold small"><?php echo CURRENCY . number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold"><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Delivery Fee</span>
                            <span class="fw-bold text-danger" id="display-delivery-fee"><?php echo CURRENCY . number_format($home_delivery_fee, 2); ?></span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4 fs-4 fw-bold">
                            <span>Total Payable</span>
                            <span class="text-danger" id="display-total-amount"><?php echo CURRENCY . number_format($subtotal + $home_delivery_fee, 2); ?></span>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if(!$payments_configured): ?>
                                <div class="alert alert-warning small mb-1" role="alert">Online payments are temporarily unavailable. Please contact store support.</div>
                            <?php endif; ?>
                            <button type="button" id="pay-now-btn" class="btn btn-fz-primary py-3 fs-6 fw-bold shadow-sm" <?php echo $payments_configured ? '' : 'disabled'; ?>>
                                <i class="bi bi-credit-card-fill me-2"></i> Pay Now with Paystack
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary btn-sm">Modify Cart</a>
                        </div>

                        <div class="text-center mt-3">
                            <span class="small text-muted"><i class="bi bi-shield-lock-fill me-1 text-success"></i> Secured by Paystack Encrypted Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>

<!-- Paystack Inline JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
$(document).ready(function(){
    var baseSubtotal = <?php echo (float)$subtotal; ?>;
    var homeFee = <?php echo (float)$home_delivery_fee; ?>;
    var currentDeliveryFee = homeFee;

    $('input[name="delivery_type"]').change(function(){
        var val = $(this).val();
        if(val === 'home_delivery'){
            $('#home-address-wrap').slideDown();
            currentDeliveryFee = homeFee;
            $('#display-delivery-fee').text('₦' + currentDeliveryFee.toLocaleString('en-US', {minimumFractionDigits: 2}));
        } else {
            $('#home-address-wrap').slideUp();
            currentDeliveryFee = 0.00;
            $('#display-delivery-fee').text('FREE (₦0.00)');
        }
        var grandTotal = baseSubtotal + currentDeliveryFee;
        $('#display-total-amount').text('₦' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
    });

    $('#pay-now-btn').click(function(e){
        e.preventDefault();

        var deliveryType = $('input[name="delivery_type"]:checked').val();
        var address = $('#delivery-address').val().trim();
        var phone = $('#customer-phone').val().trim();
        var email = $('#customer-email').val().trim();
        var firstname = <?php echo json_encode($user['firstname'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
        var lastname = <?php echo json_encode($user['lastname'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE); ?>;

        if(deliveryType === 'home_delivery' && address.length < 5){
            alert('Please enter a valid home delivery address.');
            return;
        }

        var totalAmountInKobo = Math.round((baseSubtotal + currentDeliveryFee) * 100);

        var handler = PaystackPop.setup({
            key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
            email: email,
            amount: totalAmountInKobo,
            currency: 'NGN',
            ref: 'FAZ_' + Math.floor((Math.random() * 1000000000) + 1),
            metadata: {
                custom_fields: [
                    { display_name: "Customer Name", variable_name: "customer_name", value: firstname + " " + lastname },
                    { display_name: "Delivery Type", variable_name: "delivery_type", value: deliveryType }
                ]
            },
            callback: function(response){
                // Payment successful! Process order via AJAX
                $('#pay-now-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Finalizing Order...');

                $.ajax({
                    url: 'process-order.php',
                    type: 'POST',
                    data: {
                        csrf_token: $('#checkout-form input[name="csrf_token"]').val(),
                        paystack_ref: response.reference,
                        delivery_type: deliveryType,
                        delivery_address: address,
                        phone: phone,
                        delivery_fee: currentDeliveryFee,
                        total_amount: baseSubtotal + currentDeliveryFee
                    },
                    dataType: 'json',
                    success: function(res){
                        if(res.status){
                            window.location.href = 'order-confirmation.php?number=' + res.order_number;
                        } else {
                            alert(res.message || 'Error processing order.');
                            $('#pay-now-btn').prop('disabled', false).html('<i class="bi bi-credit-card-fill me-2"></i> Pay Now with Paystack');
                        }
                    },
                    error: function(){
                        alert('Server communication error. Order will be verified shortly.');
                    }
                });
            },
            onClose: function(){
                alert('Payment window closed. Your cart remains saved.');
            }
        });
        handler.openIframe();
    });
});
</script>
