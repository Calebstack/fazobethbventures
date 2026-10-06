<?php
$page_title = "Verify OTP";
require_once "classes/Config.php";
require_once "classes/User.php";

$message = '';
$message_type = 'info';

if(empty($_SESSION['password_reset_csrf'])){
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

// Read carried email from session or query param
$email = $_SESSION['reset_email'] ?? trim($_GET['email'] ?? '');

// Flash message
if(!empty($_SESSION['flash_message'])){
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals($_SESSION['password_reset_csrf'], $_POST['csrf_token'] ?? '')){
        $message = "Your request could not be verified. Please try again.";
        $message_type = 'danger';
    } else {
        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $message = "Please enter a valid email address.";
            $message_type = 'danger';
        } elseif(!preg_match('/^\d{6}$/', $otp)){
            $message = "Please enter the 6-digit code sent to your email.";
            $message_type = 'danger';
        } else {
            $result = (new User())->verify_otp($email, $otp);
            if($result['status']){
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_otp_verified'] = true;
                $_SESSION['flash_message'] = "Code verified! Please enter your new password below.";
                $_SESSION['flash_type'] = 'success';
                header("Location: reset-password.php");
                exit;
            } else {
                $message = $result['message'];
                $message_type = 'danger';
            }
        }
    }
}

require_once "partials/header.php";
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-panel border-0 shadow-lg overflow-hidden p-4 bg-white">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.png" alt="Fazobeth Ventures logo" class="mb-2">
                        <span class="auth-brand">Fazobeth Ventures</span>
                        <h4 class="fw-bold">Enter verification code</h4>
                        <p class="text-muted small">Enter the 6-digit code sent to your email to verify your identity.</p>
                    </div>

                    <?php if($message !== ''): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="status">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="verify-otp.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf']); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required autocomplete="email" value="<?php echo htmlspecialchars($email); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold">6-Digit Code (OTP)</label>
                            <input type="text" id="otp" name="otp" class="form-control text-center fw-bold fs-4 tracking-wider" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required autocomplete="one-time-code" autofocus>
                        </div>

                        <button type="submit" class="btn btn-fz-primary py-3 w-100 fw-bold">Verify Code</button>
                    </form>

                    <div class="text-center pt-3 mt-3 border-top">
                        <a href="forgot-password.php" class="small text-danger fw-bold">Resend code</a>
                        <span class="text-muted mx-2">|</span>
                        <a href="login.php" class="small text-danger fw-bold">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
