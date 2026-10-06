<?php
header('Content-Type: application/json');
require_once __DIR__ . "/classes/Config.php";
require_once __DIR__ . "/classes/Cart.php";
require_once __DIR__ . "/classes/Product.php";
require_once __DIR__ . "/classes/Order.php";

if(!isset($_SESSION['user'])){
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Unauthorized user session."]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Invalid request method."]);
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
if(empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
    http_response_code(403);
    echo json_encode(["status" => false, "message" => "Your checkout session expired. Refresh and try again."]);
    exit;
}

$cartObj = new Cart();
$cart_items = $cartObj->get_items();

if(empty($cart_items)){
    echo json_encode(["status" => false, "message" => "Cart is empty."]);
    exit;
}

$user_id = (int)($_SESSION['user']['id'] ?? 0);
$paystack_ref = trim($_POST['paystack_ref'] ?? '');
$delivery_type = trim($_POST['delivery_type'] ?? 'home_delivery');
$delivery_address = trim($_POST['delivery_address'] ?? '');

if($user_id <= 0 || $paystack_ref === '' || !in_array($delivery_type, ['home_delivery', 'pickup_ikeja', 'pickup_lekki'], true)){
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Invalid checkout details."]);
    exit;
}

if($delivery_type === 'home_delivery' && (strlen($delivery_address) < 5 || strlen($delivery_address) > 1000)){
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Enter a valid delivery address."]);
    exit;
}

$pickup_location = null;
if($delivery_type === 'pickup_ikeja'){
    $pickup_location = PICKUP_LOCATION_1;
} elseif($delivery_type === 'pickup_lekki'){
    $pickup_location = PICKUP_LOCATION_2;
}

$delivery_fee = ($delivery_type === 'home_delivery') ? HOME_DELIVERY_FEE : 0.00;
$productObj = new Product();
$subtotal = 0.00;
foreach($cart_items as $product_id => &$item){
    $product_id = (int)$product_id;
    $quantity = (int)($item['quantity'] ?? 0);
    $product = $productObj->get_product_by_id($product_id);
    if(!$product || $quantity < 1 || (int)$product['stock'] < $quantity){
        http_response_code(409);
        echo json_encode(["status" => false, "message" => "A cart item is unavailable or no longer has enough stock."]);
        exit;
    }

    $item['product_id'] = $product_id;
    $item['product_name'] = $product['product_name'];
    $item['price'] = (float)$product['price'];
    $item['quantity'] = $quantity;
    $subtotal += $item['price'] * $quantity;
}
unset($item);
$total_amount = round($subtotal + $delivery_fee, 2);

// Verify Paystack Payment
$orderObj = new Order();
if($orderObj->payment_reference_exists($paystack_ref)){
    http_response_code(409);
    echo json_encode(["status" => false, "message" => "This payment has already been processed."]);
    exit;
}

$verify = Order::verify_paystack($paystack_ref, (int)round($total_amount * 100), 'NGN');
if(!$verify['status']){
    http_response_code(402);
    echo json_encode(["status" => false, "message" => "Payment verification failed."]);
    exit;
}

// Create Order Record
$result = $orderObj->create_order(
    $user_id,
    $total_amount,
    $delivery_type,
    $delivery_fee,
    $delivery_address,
    $pickup_location,
    $paystack_ref,
    $cart_items
);

if($result['status']){
    // Clear session cart on successful order creation
    $cartObj->clear_cart();
    echo json_encode([
        "status" => true,
        "order_number" => $result['order_number'],
        "message" => "Order created successfully!"
    ]);
    exit;
} else {
    echo json_encode($result);
    exit;
}
?>
