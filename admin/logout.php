<?php
require_once __DIR__ . "/../classes/Config.php";
require_once __DIR__ . "/../classes/Admin.php";

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	http_response_code(405);
	exit('Method not allowed.');
}
if(!hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)($_POST['csrf_token'] ?? ''))){
	http_response_code(403);
	exit('Invalid request token.');
}

$adminObj = new Admin();
$adminObj->logout();

header("Location: login.php");
exit;
?>
