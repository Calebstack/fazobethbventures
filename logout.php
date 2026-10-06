<?php
require_once "classes/Config.php";
require_once "classes/User.php";

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	http_response_code(405);
	exit('Method not allowed.');
}
if(!hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))){
	http_response_code(403);
	exit('Invalid request token.');
}

$userObj = new User();
$userObj->logout();

header("Location: index.php");
exit;
?>
