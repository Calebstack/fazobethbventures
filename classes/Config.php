<?php
date_default_timezone_set('Africa/Lagos');

$app_env = strtolower(trim(getenv('FAZOBETH_APP_ENV') ?: 'development'));
define('APP_ENV', $app_env);
define('PAYSTACK_TEST_MODE', APP_ENV !== 'production' && filter_var(getenv('FAZOBETH_PAYSTACK_TEST_MODE') ?: 'false', FILTER_VALIDATE_BOOLEAN));

if(APP_ENV === 'production'){
    $required_environment = [
        'FAZOBETH_SITE_URL', 'FAZOBETH_DB_HOST', 'FAZOBETH_DB_NAME', 'FAZOBETH_DB_USER', 'FAZOBETH_DB_PASS',
        'FAZOBETH_PAYSTACK_PUBLIC_KEY', 'FAZOBETH_PAYSTACK_SECRET_KEY',
        'FAZOBETH_SMTP_HOST', 'FAZOBETH_SMTP_PORT', 'FAZOBETH_SMTP_USER', 'FAZOBETH_SMTP_PASS',
        'FAZOBETH_MAIL_FROM_NAME', 'FAZOBETH_MAIL_FROM_EMAIL'
    ];
    foreach($required_environment as $environment_name){
        $environment_value = getenv($environment_name);
        if($environment_value === false || trim($environment_value) === ''){
            error_log('Required production configuration is missing: ' . $environment_name);
            http_response_code(500);
            exit('Application configuration is incomplete.');
        }
    }
    if(strtolower((string)getenv('FAZOBETH_DB_USER')) === 'root'){
        error_log('Production database access must use a dedicated database user.');
        http_response_code(500);
        exit('Application configuration is incomplete.');
    }
}

if(APP_ENV === 'production'){
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
}

if(session_status() === PHP_SESSION_NONE){
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $is_https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $is_https || APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(!headers_sent()){
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

define('DB_HOST', getenv('FAZOBETH_DB_HOST') !== false ? getenv('FAZOBETH_DB_HOST') : 'sql308.infinityfree.com');
define('DB_NAME', getenv('FAZOBETH_DB_NAME') !== false ? getenv('FAZOBETH_DB_NAME') : 'if0_43055116_fazobeth_db');
define('DB_USER', getenv('FAZOBETH_DB_USER') !== false ? getenv('FAZOBETH_DB_USER') : 'if0_43055116');
define('DB_PASS', getenv('FAZOBETH_DB_PASS') !== false ? getenv('FAZOBETH_DB_PASS') : '');

// Localhost fallbacks (commented out):
// define('DB_HOST', getenv('FAZOBETH_DB_HOST') !== false ? getenv('FAZOBETH_DB_HOST') : 'localhost');
// define('DB_NAME', getenv('FAZOBETH_DB_NAME') !== false ? getenv('FAZOBETH_DB_NAME') : 'fazobeth_db');
// define('DB_USER', getenv('FAZOBETH_DB_USER') !== false ? getenv('FAZOBETH_DB_USER') : 'root');
// define('DB_PASS', getenv('FAZOBETH_DB_PASS') !== false ? getenv('FAZOBETH_DB_PASS') : '');

define('SITE_NAME', 'Fazobeth Ventures');
define('SITE_URL', getenv('FAZOBETH_SITE_URL') ?: 'https://fazobethventures.site.je/fazobeth');
define('CURRENCY', '₦');

define('HOME_DELIVERY_FEE', 1000.00);
define('PICKUP_LOCATION_1', 'Car Park B, Lotto, Tree of life (FREE)');
define('PICKUP_LOCATION_2', 'Canaan Land Market (FREE)');

define('PAYSTACK_PUBLIC_KEY', getenv('FAZOBETH_PAYSTACK_PUBLIC_KEY') ?: '');
define('PAYSTACK_SECRET_KEY', getenv('FAZOBETH_PAYSTACK_SECRET_KEY') ?: '');
define('SMTP_HOST', getenv('FAZOBETH_SMTP_HOST') ?: 'ssl://smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('FAZOBETH_SMTP_PORT') ?: 465));
define('SMTP_USER', getenv('FAZOBETH_SMTP_USER') ?: '');
define('SMTP_PASS', getenv('FAZOBETH_SMTP_PASS') ?: '');
define('MAIL_FROM_NAME', getenv('FAZOBETH_MAIL_FROM_NAME') ?: SITE_NAME);
define('MAIL_FROM_EMAIL', getenv('FAZOBETH_MAIL_FROM_EMAIL') ?: SMTP_USER);

define('UPLOAD_DIR', __DIR__ . '/../upload/');
define('UPLOAD_URL', 'upload/');