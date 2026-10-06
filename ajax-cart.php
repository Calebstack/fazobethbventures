<?php
header('Content-Type: application/json');
require_once __DIR__ . "/classes/Cart.php";

$cart = new Cart();
$action = $_POST['action'] ?? '';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Invalid request method."]);
    exit;
}

$session_csrf = (string)($_SESSION['csrf_token'] ?? '');
$request_csrf = (string)($_POST['csrf_token'] ?? '');
if($session_csrf === '' || !hash_equals($session_csrf, $request_csrf)){
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Invalid request token."]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($action === 'add'){
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $res = $cart->add_item($product_id, $quantity);
        echo json_encode($res);
        exit;
    }

    if($action === 'update'){
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $res = $cart->update_quantity($product_id, $quantity);
        echo json_encode($res);
        exit;
    }

    if($action === 'remove'){
        $product_id = $_POST['product_id'] ?? 0;
        $res = $cart->remove_item($product_id);
        echo json_encode($res);
        exit;
    }

    if($action === 'clear'){
        $cart->clear_cart();
        echo json_encode(["status" => true, "cart_count" => 0, "subtotal" => 0]);
        exit;
    }
}

echo json_encode(["status" => false, "message" => "Invalid request action."]);
exit;
?>
