<?php
$page_title = "User Login";
require_once "classes/Config.php";
require_once "classes/User.php";

$redirect = $_GET['redirect'] ?? '';
$error_msg = '';

if(isset($_SESSION['user'])){
    if($redirect === 'checkout'){
        header("Location: checkout.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))){
        $error_msg = 'Your session expired. Refresh the page and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $redirect_url = trim($_POST['redirect'] ?? '');

        $userObj = new User();
        $result = $userObj->login($email, $password);

        if($result['status']){
            if($redirect_url === 'checkout'){
                header("Location: checkout.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error_msg = $result['message'];
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
                        <h4 class="fw-bold">Welcome Back</h4>
                        <p class="text-muted small">Log in to your account to complete your order</p>
                    </div>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="text-end mb-3">
                            <a href="forgot-password.php" class="small text-danger fw-semibold">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-fz-primary py-3 w-100 fw-bold shadow-sm mb-3">
                            Login <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <p class="small text-muted mb-0">
                            Don't have an account? 
                            <a href="signup.php<?php echo !empty($redirect) ? '?redirect='.urlencode($redirect) : ''; ?>" class="text-danger fw-bold">
                                Create an account
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
