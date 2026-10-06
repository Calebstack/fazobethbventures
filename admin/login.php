<?php
require_once __DIR__ . "/../classes/Config.php";
require_once __DIR__ . "/../classes/Admin.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!empty($_SESSION['admin']['id'])){
    header("Location: index.php");
    exit;
}

$error_msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))){
        $error_msg = 'Your session expired. Refresh the page and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $adminObj = new Admin();
        $res = $adminObj->login($username, $password);

        if($res['status']){
            header("Location: index.php");
            exit;
        } else {
            $error_msg = $res['message'];
        }
    }
}

require_once __DIR__ . "/partials/header.php";
?>

<div class="admin-login-wrap container py-5">
    <div class="admin-login-card">
                <div class="text-center mb-4">
                    <img src="../assets/images/logo.png" alt="Fazobeth Ventures logo" height="60" class="mb-2">
                    <h4 class="fw-bold text-dark">Admin Portal</h4>
                    <p class="text-muted small">Sign in to manage Fazobeth Ventures products and orders.</p>
                </div>

                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Username / Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Admin username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger py-3 w-100 fw-bold shadow-sm mb-2" style="background-color: #e83e8c; border:none;">
                        Sign In to Dashboard <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center pt-3 border-top mt-3">
                    <a href="../index.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Return to Main Website</a>
                </div>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
