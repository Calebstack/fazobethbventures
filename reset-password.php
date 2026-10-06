<?php
$page_title = "Set New Password";
require_once "classes/Config.php";
require_once "classes/User.php";

$message = '';
$message_type = 'danger';
$reset_complete = false;

if(empty($_SESSION['password_reset_csrf'])){
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

// Session guard: Ensure user has verified their OTP first
$email = $_SESSION['reset_email'] ?? '';
$otp = $_SESSION['reset_otp'] ?? '';
$is_verified = !empty($_SESSION['reset_otp_verified']) && !empty($email) && !empty($otp);

// Check flash message
if(!empty($_SESSION['flash_message'])){
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

if(!$is_verified && $_SERVER['REQUEST_METHOD'] !== 'POST'){
    $_SESSION['flash_message'] = "Please enter your email and verify your code first.";
    $_SESSION['flash_type'] = 'danger';
    header("Location: verify-otp.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals($_SESSION['password_reset_csrf'], $_POST['csrf_token'] ?? '')){
        $message = "Your request could not be verified. Please try again.";
        $message_type = 'danger';
    } else {
        $email = trim($_POST['email'] ?? $email);
        $otp = trim($_POST['otp'] ?? $otp);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if(empty($email) || empty($otp)){
            $message = "Session expired. Please request a new reset code.";
            $message_type = 'danger';
        } elseif(strlen($password) < 8){
            $message = "Your password must be at least 8 characters long.";
            $message_type = 'danger';
        } elseif($password !== $confirm_password){
            $message = "The passwords do not match.";
            $message_type = 'danger';
        } else {
            $result = (new User())->reset_password_with_otp($email, $otp, $password);
            $message = $result['message'];
            $reset_complete = $result['status'];
            $message_type = $result['status'] ? 'success' : 'danger';
            if($reset_complete){
                unset($_SESSION['password_reset_csrf']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_otp']);
                unset($_SESSION['reset_otp_verified']);
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
                        <h4 class="fw-bold"><?php echo $reset_complete ? 'Password Reset Successful' : 'Enter New Password'; ?></h4>
                        <p class="text-muted small">
                            <?php echo $reset_complete ? 'Your password has been updated. You can now log in to your account.' : 'Create a strong new password for <strong>' . htmlspecialchars($email) . '</strong>'; ?>
                        </p>
                    </div>

                    <?php if($message !== ''): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>" role="status">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!$reset_complete): ?>
                        <form action="reset-password.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf']); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="otp" value="<?php echo htmlspecialchars($otp); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password" autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required minlength="8" autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn btn-fz-primary py-3 w-100 fw-bold">Save New Password</button>
                        </form>
                    <?php else: ?>
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-fz-primary py-3 w-100 fw-bold">Proceed to Login</a>
                        </div>
                    <?php endif; ?>

                    <div class="text-center pt-3 mt-3 border-top">
                        <a href="login.php" class="small text-danger fw-bold">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>