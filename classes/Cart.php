<?php

require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/Product.php";

class Cart {

    public function __construct(){
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart'] = [];
        }
    }

    public function add_item($product_id, $quantity = 1){
        $product_id = (int)$product_id;
        $quantity = max(1, (int)$quantity);

        $productObj = new Product();
        $product = $productObj->get_product_by_id($product_id);

        if(!$product){
            return ["status" => false, "message" => "Product not found."];
        }

        if(isset($_SESSION['cart'][$product_id])){
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'price' => (float)$product['price'],
                'color' => $product['color'],
                'product_img' => $product['product_img'],
                'category_name' => $product['category_name'],
                'quantity' => $quantity
            ];
        }

        return [
            "status" => true, 
            "message" => "Added to cart!", 
            "cart_count" => $this->get_total_count(),
            "subtotal" => $this->get_subtotal()
        ];
    }

    public function update_quantity($product_id, $quantity){
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if(isset($_SESSION['cart'][$product_id])){
            if($quantity <= 0){
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
            return [
                "status" => true,
                "cart_count" => $this->get_total_count(),
                "subtotal" => $this->get_subtotal()
            ];
        }
        return ["status" => false, "message" => "Item not in cart."];
    }

    public function remove_item($product_id){
        $product_id = (int)$product_id;
        if(isset($_SESSION['cart'][$product_id])){
            unset($_SESSION['cart'][$product_id]);
            return [
                "status" => true,
                "cart_count" => $this->get_total_count(),
                "subtotal" => $this->get_subtotal()
            ];
        }
        return ["status" => false, "message" => "Item not in cart."];
    }

    public function get_items(){
        return $_SESSION['cart'] ?? [];
    }

    public function get_total_count(){
        $count = 0;
        if(isset($_SESSION['cart'])){
            foreach($_SESSION['cart'] as $item){
                $count += $item['quantity'];
            }
        }
        return $count;
    }

    public function get_subtotal(){
        $subtotal = 0.0;
        if(isset($_SESSION['cart'])){
            foreach($_SESSION['cart'] as $item){
                $subtotal += ($item['price'] * $item['quantity']);
            }
        }
        return $subtotal;
    }

    public function clear_cart(){
        $_SESSION['cart'] = [];
    }
}

?>
