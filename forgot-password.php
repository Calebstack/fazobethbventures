<?php
$page_title = "Forgot Password";
require_once "classes/Config.php";
require_once "classes/User.php";

$message = '';
$message_type = 'info';

if(empty($_SESSION['password_reset_csrf'])){
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals($_SESSION['password_reset_csrf'], $_POST['csrf_token'] ?? '')){
        $message = "Your request could not be verified. Please try again.";
        $message_type = 'danger';
    } else {
        $email = trim($_POST['email'] ?? '');
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $message = "Enter a valid email address.";
            $message_type = 'danger';
        } else {
            $result = (new User())->request_password_reset($email);
            if($result['status']){
                $_SESSION['reset_email'] = $email;
                $_SESSION['flash_message'] = "A 6-digit verification code has been sent to " . $email . ".";
                $_SESSION['flash_type'] = 'info';
                header("Location: verify-otp.php");
                exit;
            } else {
                $message = $result['message'];
                $message_type = 'danger';
            }
        }
    }
}

// Check flash message
if(!empty($_SESSION['flash_message'])){
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
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
                        <h4 class="fw-bold">Reset your password</h4>
                        <p class="text-muted small">We will email you a one-time code to verify your account.</p>
                    </div>

                    <?php if($message !== ''): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="status">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf']); ?>">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-fz-primary py-3 w-100 fw-bold">Send reset code</button>
                    </form>

                    <div class="text-center pt-3 mt-3 border-top">
                        <a href="login.php" class="small text-danger fw-bold">Back to login</a>
                        <span class="text-muted mx-2">|</span>
                        <a href="verify-otp.php" class="small text-danger fw-bold">Enter verification code</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>