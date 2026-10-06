<?php
/**
 * Paystack Callback URL
 * Customer's browser is redirected here after payment (for redirect flow)
 * 
 * Configure this URL in Paystack Dashboard:
 * https://fazobethventures.site.je/fazobeth/payment/callback.php
 * 
 * Note: Current integration uses Inline/Popup which doesn't redirect.
 * This page handles cases where redirect might occur.
 */

require_once __DIR__ . '/../classes/Config.php';
require_once __DIR__ . '/../classes/Order.php';

$reference = $_GET['reference'] ?? $_GET['trxref'] ?? '';

$page_title = "Payment Callback";
require_once __DIR__ . '/../partials/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <?php if ($reference): ?>
                            <div class="mb-4">
                                <i class="bi bi-hourglass-split text-warning fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-3">Verifying Payment...</h3>
                            <p class="text-muted mb-4">
                                Your payment is being verified. Please wait while we confirm the transaction.
                            </p>
                            <p class="small text-muted">Reference: <code><?php echo htmlspecialchars($reference); ?></code></p>
                            
                            <form id="verify-form" action="process-order.php" method="POST" style="display: none;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <input type="hidden" name="paystack_ref" value="<?php echo htmlspecialchars($reference); ?>">
                                <input type="hidden" name="delivery_type" value="home_delivery">
                                <input type="hidden" name="delivery_address" value="">
                                <input type="hidden" name="phone" value="">
                                <input type="hidden" name="delivery_fee" value="1000">
                                <input type="hidden" name="total_amount" value="0">
                            </form>

                            <script>
                            // Auto-submit to process-order for verification
                            document.addEventListener('DOMContentLoaded', function() {
                                // In a real scenario, you'd get these from session or redirect params
                                // For now, redirect to order confirmation if order exists
                                setTimeout(function() {
                                    window.location.href = 'order-confirmation.php?number=' + new URLSearchParams(window.location.search).get('number');
                                }, 3000);
                            });
                            </script>
                            
                        <?php else: ?>
                            <div class="mb-4">
                                <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-3">No Payment Reference</h3>
                            <p class="text-muted mb-4">
                                We couldn't find a payment reference. This may happen if you closed the payment window before completion.
                            </p>
                            <a href="cart.php" class="btn btn-fz-primary">
                                <i class="bi bi-arrow-left me-2"></i> Return to Cart
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="small text-muted">
                        If nothing happens, <a href="my-orders.php">check your orders</a> or 
                        <a href="cart.php">return to cart</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>