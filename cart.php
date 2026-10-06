<?php
$page_title = "Shopping Cart";
require_once "partials/header.php";
require_once "classes/Cart.php";

$cartObj = new Cart();
$cart_items = $cartObj->get_items();
$subtotal = $cartObj->get_subtotal();
?>

<!-- Header -->
<section class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <h2 class="fw-bold mb-1"><i class="bi bi-cart3 text-danger me-2"></i>Shopping Cart</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">Cart</li>
            </ol>
        </nav>
    </div>
</section>

<section class="mb-5">
    <div class="container">
        <?php if(!empty($cart_items)): ?>
            <div class="row g-4">
                <!-- Cart Items Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table align-middle cart-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-4">Product</th>
                                        <th scope="col">Price</th>
                                        <th scope="col" style="width: 130px;">Quantity</th>
                                        <th scope="col">Total</th>
                                        <th scope="col" class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_items as $item): 
                                        $item_total = $item['price'] * $item['quantity'];
                                    ?>
                                        <tr id="cart-row-<?php echo $item['product_id']; ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?php echo htmlspecialchars($item['product_img']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" onerror="this.onerror=null; this.src='assets/images/logo.png';">
                                                    <div>
                                                        <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="fw-bold text-dark text-decoration-none d-block">
                                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                                        </a>
                                                        <span class="badge bg-light text-muted border">Color: <?php echo htmlspecialchars($item['color']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-semibold">
                                                <?php echo CURRENCY . number_format($item['price'], 2); ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary qty-minus-btn" data-id="<?php echo $item['product_id']; ?>" type="button">-</button>
                                                    <input type="number" class="form-control text-center qty-input" data-id="<?php echo $item['product_id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                                    <button class="btn btn-outline-secondary qty-plus-btn" data-id="<?php echo $item['product_id']; ?>" type="button">+</button>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-danger item-total-val" id="item-total-<?php echo $item['product_id']; ?>">
                                                <?php echo CURRENCY . number_format($item_total, 2); ?>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-id="<?php echo $item['product_id']; ?>" title="Remove item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                            <a href="products.php" class="btn btn-fz-outline btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                            </a>
                            <button type="button" id="clear-cart-btn" class="btn btn-link btn-sm text-danger text-decoration-none">
                                <i class="bi bi-trash me-1"></i> Clear Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Side Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                        <h4 class="fw-bold mb-3">Order Summary</h4>
                        
                        <div class="d-flex justify-content-between mb-2 fs-6">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold" id="summary-subtotal"><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 fs-6">
                            <span class="text-muted">Delivery</span>
                            <span class="text-success small fw-semibold">Calculated at Checkout</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4 fs-5 fw-bold">
                            <span>Estimated Total</span>
                            <span class="text-danger" id="summary-total"><?php echo CURRENCY . number_format($subtotal, 2); ?></span>
                        </div>

                        <?php if(isset($_SESSION['user'])): ?>
                            <a href="checkout.php" class="btn btn-fz-primary py-3 fs-6 w-100 fw-bold shadow-sm">
                                Proceed to Checkout <i class="bi bi-arrow-right me-1"></i>
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=checkout" class="btn btn-fz-primary py-3 fs-6 w-100 fw-bold shadow-sm mb-2">
                                Login to Checkout <i class="bi bi-box-arrow-in-right ms-1"></i>
                            </a>
                            <div class="text-center small text-muted">
                                Don't have an account? <a href="signup.php?redirect=checkout" class="text-danger fw-semibold">Sign Up Here</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-empty-state text-center py-5 bg-white rounded-4 shadow-sm">
                <div class="display-1 text-muted"><i class="bi bi-cart-x"></i></div>
                <h3 class="fw-bold mt-3 text-secondary">Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any baby or mother products yet.</p>
                <a href="products.php" class="btn btn-fz-primary btn-lg px-4">
                    <i class="bi bi-bag-plus me-2"></i> Start Shopping Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>

<script>
$(document).ready(function(){
    function updateItemQty(productId, newQty){
        $.ajax({
            url: 'ajax-cart.php',
            type: 'POST',
            data: { action: 'update', product_id: productId, quantity: newQty, csrf_token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function(res){
                if(res.status){
                    $('#cart-badge-count').text(res.cart_count);
                    location.reload(); // Refresh table state
                }
            }
        });
    }

    $('.qty-plus-btn').click(function(){
        var id = $(this).data('id');
        var input = $('.qty-input[data-id="' + id + '"]');
        var current = parseInt(input.val()) || 1;
        updateItemQty(id, current + 1);
    });

    $('.qty-minus-btn').click(function(){
        var id = $(this).data('id');
        var input = $('.qty-input[data-id="' + id + '"]');
        var current = parseInt(input.val()) || 1;
        if(current > 1){
            updateItemQty(id, current - 1);
        }
    });

    $('.remove-item-btn').click(function(){
        var id = $(this).data('id');
        if(confirm('Remove this product from your cart?')){
            $.ajax({
                url: 'ajax-cart.php',
                type: 'POST',
                data: { action: 'remove', product_id: id, csrf_token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        location.reload();
                    }
                }
            });
        }
    });

    $('#clear-cart-btn').click(function(){
        if(confirm('Are you sure you want to clear your entire cart?')){
            $.ajax({
                url: 'ajax-cart.php',
                type: 'POST',
                data: { action: 'clear', csrf_token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                success: function(res){
                    location.reload();
                }
            });
        }
    });
});
</script>
