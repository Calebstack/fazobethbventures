<?php
$page_title = "User Sign Up";
require_once "classes/Config.php";
require_once "classes/User.php";

$redirect = $_GET['redirect'] ?? '';
$error_msg = '';

if(isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))){
        $error_msg = 'Your session expired. Refresh the page and try again.';
    } else {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm_pwd = (string)($_POST['confirm_password'] ?? '');
    $redirect_url = trim($_POST['redirect'] ?? '');

    if($password !== $confirm_pwd){
        $error_msg = "Passwords do not match!";
    } else {
        $userObj = new User();
        $res = $userObj->signup($firstname, $lastname, $email, $password, $phone);

        if($res['status']){
            // Log user in automatically
            $userObj->login($email, $password);
            if($redirect_url === 'checkout'){
                header("Location: checkout.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error_msg = $res['message'];
        }
    }
    }
}

require_once "partials/header.php";
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card auth-panel border-0 shadow-lg overflow-hidden p-4 bg-white">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo.png" alt="Fazobeth Ventures logo" class="mb-2">
                        <span class="auth-brand">Fazobeth Ventures</span>
                        <h4 class="fw-bold">Create an Account</h4>
                        <p class="text-muted small">Set up your account for faster checkout and order tracking.</p>
                    </div>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="signup.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name</label>
                                <input type="text" name="firstname" class="form-control" required value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name</label>
                                <input type="text" name="lastname" class="form-control" required value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="08012345678" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-fz-primary py-3 w-100 fw-bold shadow-sm mb-3">
                            Create Account <i class="bi bi-person-plus-fill ms-1"></i>
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <p class="small text-muted mb-0">
                            Already have an account? 
                            <a href="login.php<?php echo !empty($redirect) ? '?redirect='.urlencode($redirect) : ''; ?>" class="text-danger fw-bold">
                                Log in
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once "partials/footer.php"; ?>
