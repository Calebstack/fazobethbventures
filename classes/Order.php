<?php

require_once __DIR__ . "/Db.php";
require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/Mailer.php";

class Order extends Db {
    private $dbconn;

    public function __construct(){
        $this->dbconn = $this->connect();
    }

    public function generate_order_number(){
        return "FAZ-" . date("Ymd") . "-" . rand(1000, 9999);
    }

    public function create_order($user_id, $total_amount, $delivery_type, $delivery_fee, $delivery_address, $pickup_location, $paystack_ref, $cart_items){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Database connection failed."];
        }

        if(empty($cart_items)){
            return ["status" => false, "message" => "Cart is empty."];
        }

        if($this->payment_reference_exists($paystack_ref)){
            return ["status" => false, "message" => "This payment reference has already been used."];
        }

        $order_number = $this->generate_order_number();

        try{
            $this->dbconn->beginTransaction();

            $sql = "INSERT INTO orders(order_number, user_id, total_amount, delivery_type, delivery_fee, delivery_address, pickup_location, payment_status, order_status, paystack_ref)
                    VALUES(?, ?, ?, ?, ?, ?, ?, 'paid', 'processing', ?)";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([
                $order_number,
                (int)$user_id,
                (float)$total_amount,
                $delivery_type,
                (float)$delivery_fee,
                $delivery_address,
                $pickup_location,
                $paystack_ref
            ]);
            $order_id = $this->dbconn->lastInsertId();

            // Insert order items
            $item_sql = "INSERT INTO order_items(order_id, product_id, product_name, quantity, price, subtotal) VALUES(?, ?, ?, ?, ?, ?)";
            $item_stmt = $this->dbconn->prepare($item_sql);

            // Also reduce stock quantity
            $stock_sql = "UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?";
            $stock_stmt = $this->dbconn->prepare($stock_sql);

            foreach($cart_items as $item){
                $subtotal = $item['price'] * $item['quantity'];
                $item_stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['product_name'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                ]);

                $stock_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if($stock_stmt->rowCount() !== 1){
                    throw new PDOException("Product stock changed during checkout.");
                }
            }

            $this->dbconn->commit();

            // Fetch user info for confirmation email
            $u_stmt = $this->dbconn->prepare("SELECT firstname, lastname, email FROM users WHERE user_id = ?");
            $u_stmt->execute([(int)$user_id]);
            $user_info = $u_stmt->fetch();

            if($user_info){
                $customer_name = $user_info['firstname'] . " " . $user_info['lastname'];
                $delivery_info = ($delivery_type === 'home_delivery') ? $delivery_address : $pickup_location;
                if(!Mailer::send_order_confirmation($user_info['email'], $customer_name, $order_number, $total_amount, $delivery_type, $delivery_info, $cart_items)){
                    error_log("Order confirmation email delivery failed for order " . $order_number);
                }
            }

            return [
                "status" => true,
                "order_id" => $order_id,
                "order_number" => $order_number,
                "message" => "Order placed successfully!"
            ];
        }catch(PDOException $e){
            $this->dbconn->rollBack();
            error_log($e->getMessage());
            return ["status" => false, "message" => "Failed to create order. Please contact support if payment was taken."];
        }
    }

    public function get_order_by_id($order_id){
        try{
            $sql = "SELECT o.*, u.firstname, u.lastname, u.email, u.phone 
                    FROM orders o 
                    INNER JOIN users u ON o.user_id = u.user_id 
                    WHERE o.order_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$order_id]);
            $order = $stmt->fetch();

            if($order){
                $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
                $items_stmt = $this->dbconn->prepare($items_sql);
                $items_stmt->execute([(int)$order_id]);
                $order['items'] = $items_stmt->fetchAll();
            }

            return $order;
        }catch(PDOException $e){
            return false;
        }
    }

    public function get_order_by_number($order_number){
        try{
            $sql = "SELECT order_id FROM orders WHERE order_number = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$order_number]);
            $res = $stmt->fetch();
            if($res){
                return $this->get_order_by_id($res['order_id']);
            }
            return false;
        }catch(PDOException $e){
            return false;
        }
    }

    public function payment_reference_exists($reference){
        try{
            $stmt = $this->dbconn->prepare("SELECT 1 FROM orders WHERE paystack_ref = ? LIMIT 1");
            $stmt->execute([$reference]);
            return (bool)$stmt->fetchColumn();
        }catch(PDOException $e){
            error_log("Payment reference lookup failed: " . $e->getMessage());
            return true;
        }
    }

    public function get_user_orders($user_id){
        try{
            $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_id DESC";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$user_id]);
            $orders = $stmt->fetchAll();

            foreach($orders as &$order){
                $i_sql = "SELECT * FROM order_items WHERE order_id = ?";
                $i_stmt = $this->dbconn->prepare($i_sql);
                $i_stmt->execute([$order['order_id']]);
                $order['items'] = $i_stmt->fetchAll();
            }

            return $orders;
        }catch(PDOException $e){
            return [];
        }
    }

    public function get_all_orders(){
        try{
            $sql = "SELECT o.*, u.firstname, u.lastname, u.email 
                    FROM orders o 
                    INNER JOIN users u ON o.user_id = u.user_id 
                    ORDER BY o.order_id DESC";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll();

            foreach($orders as &$order){
                $i_sql = "SELECT * FROM order_items WHERE order_id = ?";
                $i_stmt = $this->dbconn->prepare($i_sql);
                $i_stmt->execute([$order['order_id']]);
                $order['items'] = $i_stmt->fetchAll();
            }

            return $orders;
        }catch(PDOException $e){
            return [];
        }
    }

    public function update_order_status($order_id, $status){
        $allowed_statuses = ['cancelled', 'delivered', 'processing', 'shipped'];
        $status = strtolower(trim($status));
        if(!in_array($status, $allowed_statuses, true)){
            return false;
        }

        try{
            $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            return $stmt->execute([$status, (int)$order_id]);
        }catch(PDOException $e){
            return false;
        }
    }

    public function get_dashboard_analytics(){
        try{
            // Total Revenue
            $rev_stmt = $this->dbconn->query("SELECT SUM(total_amount) AS total_revenue FROM orders WHERE payment_status = 'paid'");
            $total_revenue = $rev_stmt->fetch()['total_revenue'] ?? 0.00;

            // Total Orders Count
            $ord_stmt = $this->dbconn->query("SELECT COUNT(*) AS total_orders FROM orders");
            $total_orders = $ord_stmt->fetch()['total_orders'] ?? 0;

            // Total Products Count
            $prd_stmt = $this->dbconn->query("SELECT COUNT(*) AS total_products FROM products");
            $total_products = $prd_stmt->fetch()['total_products'] ?? 0;

            // Total Users Count
            $usr_stmt = $this->dbconn->query("SELECT COUNT(*) AS total_users FROM users");
            $total_users = $usr_stmt->fetch()['total_users'] ?? 0;

            // Recent Orders (limit 5)
            $rec_stmt = $this->dbconn->query("SELECT o.*, u.firstname, u.lastname FROM orders o INNER JOIN users u ON o.user_id = u.user_id ORDER BY o.order_id DESC LIMIT 5");
            $recent_orders = $rec_stmt->fetchAll();

            return [
                "total_revenue" => (float)$total_revenue,
                "total_orders" => (int)$total_orders,
                "total_products" => (int)$total_products,
                "total_users" => (int)$total_users,
                "recent_orders" => $recent_orders
            ];
        }catch(PDOException $e){
            return [
                "total_revenue" => 0.00,
                "total_orders" => 0,
                "total_products" => 0,
                "total_users" => 0,
                "recent_orders" => []
            ];
        }
    }

    public static function verify_paystack($reference, $expected_amount_kobo, $expected_currency = 'NGN'){
        $reference = trim((string)$reference);
        $expected_amount_kobo = (int)$expected_amount_kobo;

        if($reference === '' || strlen($reference) > 100 || $expected_amount_kobo <= 0){
            return ["status" => false, "message" => "Invalid payment verification details."];
        }

        if(PAYSTACK_TEST_MODE && APP_ENV !== 'production' && str_starts_with($reference, 'FAZ_MOCK_')){
            return ["status" => true, "data" => [
                "status" => "success",
                "reference" => $reference,
                "amount" => $expected_amount_kobo,
                "currency" => $expected_currency
            ]];
        }

        if(PAYSTACK_SECRET_KEY === '' || !function_exists('curl_init')){
            error_log("Paystack verification is not configured.");
            return ["status" => false, "message" => "Payment verification is unavailable."];
        }

        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $request = curl_exec($ch);
        $http_status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($request === false){
            error_log("Paystack verification request failed: " . curl_error($ch));
        }
        curl_close($ch);

        if($request === false || $http_status < 200 || $http_status >= 300){
            return ["status" => false, "message" => "Payment verification failed."];
        }

        $result = json_decode($request, true);
        $data = $result['data'] ?? null;
        if(!is_array($data)
            || ($result['status'] ?? false) !== true
            || ($data['status'] ?? '') !== 'success'
            || !hash_equals($reference, (string)($data['reference'] ?? ''))
            || (int)($data['amount'] ?? 0) !== $expected_amount_kobo
            || strtoupper((string)($data['currency'] ?? '')) !== strtoupper($expected_currency)){
            return ["status" => false, "message" => "Payment verification failed."];
        }

        return ["status" => true, "data" => $data];
    }
}

?>
