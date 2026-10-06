</main>

<!-- Footer -->
<footer class="fz-footer">
    <div class="container">
        <div class="footer-ribbon">
            <div>
                <span class="footer-eyebrow">Fazobeth Ventures</span>
                <h2>Carefully chosen for growing families.</h2>
            </div>
        </div>

        <div class="row g-4 footer-grid">
            <!-- Col 1: About -->
            <div class="col-lg-4 col-md-6 footer-column">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="assets/images/logo.png" alt="Fazobeth Ventures logo" height="42" class="footer-logo">
                    <span class="fw-bold fs-5 text-white">Fazobeth Ventures</span>
                </div>
                <p class="small mb-3">
                    Baby, maternity, and nursery essentials selected to bring comfort to the everyday moments of family life.
                </p>
                <div class="footer-contact-icons" role="group" aria-label="Contact channels">
                    <a class="footer-contact-icon" href="https://wa.me/2348055471726" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp" title="WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                    <a class="footer-contact-icon" href="tel:+2348055471726" aria-label="Call Fazobeth Ventures" title="Call"><i class="bi bi-telephone-fill" aria-hidden="true"></i></a>
                    <a class="footer-contact-icon" href="mailto:fazobethventures@gmail.com" aria-label="Email Fazobeth Ventures" title="Email"><i class="bi bi-envelope-fill" aria-hidden="true"></i></a>
                </div>
            </div>

            <!-- Col 2: Quick Links -->
            <div class="col-lg-2 col-md-6 footer-column">
                <h5>Explore</h5>
                <ul class="list-unstyled small d-grid gap-2 footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Shop all</a></li>
                    <li><a href="cart.php">Your cart</a></li>
                    <li><a href="my-orders.php">Your orders</a></li>
                </ul>
            </div>

            <!-- Col 3: Delivery & Pickup Hubs -->
            <div class="col-lg-3 col-md-6 footer-column">
                <h5>Delivery & collection</h5>
                <ul class="list-unstyled small d-grid gap-3 footer-details">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>Pickup Location 1</strong><br><?php echo htmlspecialchars(PICKUP_LOCATION_1); ?></span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>Pickup Location 2</strong><br><?php echo htmlspecialchars(PICKUP_LOCATION_2); ?></span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-truck"></i>
                        <span>Doorstep delivery is available at checkout.</span>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Customer Support -->
            <div class="col-lg-3 col-md-6 footer-column">
                <h5>We're here to help</h5>
                <p class="small mb-3">Questions about an order? Get in touch with our customer care team.</p>
                <a class="footer-contact" href="mailto:fazobethventures@gmail.com"><i class="bi bi-envelope"></i> fazobethventures@gmail.com</a>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?php echo date("Y"); ?> Fazobeth Ventures. All rights reserved.</span>
            <span><i class="bi bi-shield-check"></i> Secure checkout powered by Paystack</span>
        </div>
    </div>
</footer>

<!-- Toast Notification for Add to Cart -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="cart-toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span id="toast-message">Item added to your cart successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- jQuery Script -->
<script src="assets/jq.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Custom Ajax Script -->
<script>
$(document).ready(function(){
    // Global Add To Cart Handler (No Page Reload)
    $(document).on('click', '.add-to-cart-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var qty = $('#qty-input-' + productId).val() || 1;

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Adding...');

        $.ajax({
            url: 'ajax-cart.php',
            type: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                quantity: qty,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response){
                $btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-1"></i> Add to Cart');
                if(response.status){
                    // Update badge counter in header
                    $('#cart-badge-count, #floating-cart-count').text(response.cart_count);
                    $('#floating-cart-link').attr('aria-label', 'Open your cart, ' + response.cart_count + ' items');

                    // Show Toast Notification
                    $('#toast-message').text(response.message || 'Item added to cart!');
                    var toastEl = document.getElementById('cart-toast');
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                } else {
                    alert(response.message || 'Failed to add item to cart.');
                }
            },
            error: function(){
                $btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-1"></i> Add to Cart');
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>

</body>
</html>
