<?php

require_once __DIR__ . "/Db.php";

class Product extends Db {
    private $dbconn;

    public function __construct(){
        $this->dbconn = $this->connect();
    }

    public function get_all_products($limit = null){
        try{
            $sql = "SELECT p.*, c.category_name FROM products p
                    INNER JOIN categories c ON p.category_id = c.category_id
                    ORDER BY p.product_id DESC";
            if($limit){
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return [];
        }
    }

    public function get_featured_products($limit = 6){
        return $this->get_all_products($limit);
    }

    public function search_and_filter($search = '', $category_id = '', $color = '', $min_price = '', $max_price = '', $sort = 'newest'){
        try{
            $sql = "SELECT p.*, c.category_name FROM products p
                    INNER JOIN categories c ON p.category_id = c.category_id
                    WHERE 1=1";
            $params = [];

            if(!empty($search)){
                $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)";
                $searchTerm = "%" . trim($search) . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if(!empty($category_id) && $category_id !== 'all'){
                $sql .= " AND p.category_id = ?";
                $params[] = (int)$category_id;
            }

            if(!empty($color) && $color !== 'all'){
                $sql .= " AND LOWER(p.color) = LOWER(?)";
                $params[] = trim($color);
            }

            if(!empty($min_price) && is_numeric($min_price)){
                $sql .= " AND p.price >= ?";
                $params[] = (float)$min_price;
            }

            if(!empty($max_price) && is_numeric($max_price)){
                $sql .= " AND p.price <= ?";
                $params[] = (float)$max_price;
            }

            switch($sort){
                case 'price-low':
                    $sql .= " ORDER BY p.price ASC";
                    break;
                case 'price-high':
                    $sql .= " ORDER BY p.price DESC";
                    break;
                case 'name-asc':
                    $sql .= " ORDER BY p.product_name ASC";
                    break;
                case 'newest':
                default:
                    $sql .= " ORDER BY p.product_id DESC";
                    break;
            }

            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return [];
        }
    }

    public function get_product_by_id($id){
        try{
            $sql = "SELECT p.*, c.category_name FROM products p
                    INNER JOIN categories c ON p.category_id = c.category_id
                    WHERE p.product_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function get_distinct_colors(){
        try{
            $sql = "SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color != '' ORDER BY color ASC";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){
            return [];
        }
    }

    public function add_product($name, $category_id, $price, $color, $description, $product_img, $stock = 10, $status = 'available'){
        try{
            $sql = "INSERT INTO products(product_name, category_id, price, color, description, product_img, stock, status) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$name, $category_id, $price, $color, $description, $product_img, $stock, $status]);
            return $this->dbconn->lastInsertId();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function add_product_with_images($name, $category_id, $price, $color, $description, $image_paths = [], $stock = 10, $status = 'available'){
        try{
            $primary_img = !empty($image_paths) ? $image_paths[0] : 'assets/images/logo.png';
            $product_id = $this->add_product($name, $category_id, $price, $color, $description, $primary_img, $stock, $status);
            if(!$product_id){
                return false;
            }

            if(!empty($image_paths)){
                foreach($image_paths as $idx => $img_path){
                    $this->add_product_image($product_id, $img_path, $idx === 0 ? 1 : 0);
                }
            } else {
                $this->add_product_image($product_id, $primary_img, 1);
            }

            return $product_id;
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function update_product($id, $name, $category_id, $price, $color, $description, $product_img, $stock, $status){
        try{
            $sql = "UPDATE products SET product_name = ?, category_id = ?, price = ?, color = ?, description = ?, product_img = ?, stock = ?, status = ? WHERE product_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            return $stmt->execute([$name, $category_id, $price, $color, $description, $product_img, $stock, $status, $id]);
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete_product($id){
        try{
            $imgs = $this->get_product_images($id);
            foreach($imgs as $img){
                if(!empty($img['image_path']) && strpos($img['image_path'], 'upload/') === 0 && file_exists(__DIR__ . "/../" . $img['image_path'])){
                    @unlink(__DIR__ . "/../" . $img['image_path']);
                }
            }
            $delImgs = $this->dbconn->prepare("DELETE FROM product_images WHERE product_id = ?");
            $delImgs->execute([(int)$id]);

            $sql = "DELETE FROM products WHERE product_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            return $stmt->execute([(int)$id]);
        }catch(PDOException $e){
            return false;
        }
    }

    public function get_product_images($product_id){
        try{
            $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, image_id ASC";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$product_id]);
            $images = $stmt->fetchAll();
            if(empty($images)){
                $prod = $this->get_product_by_id($product_id);
                if($prod && !empty($prod['product_img'])){
                    return [[
                        'image_id' => 0,
                        'product_id' => (int)$product_id,
                        'image_path' => $prod['product_img'],
                        'is_primary' => 1
                    ]];
                }
            }
            return $images;
        }catch(PDOException $e){
            error_log($e->getMessage());
            return [];
        }
    }

    public function add_product_image($product_id, $image_path, $is_primary = 0){
        try{
            if($is_primary){
                $stmtReset = $this->dbconn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                $stmtReset->execute([(int)$product_id]);

                $stmtProd = $this->dbconn->prepare("UPDATE products SET product_img = ? WHERE product_id = ?");
                $stmtProd->execute([$image_path, (int)$product_id]);
            }

            $sql = "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$product_id, $image_path, $is_primary ? 1 : 0]);
            return $this->dbconn->lastInsertId();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function set_primary_image($product_id, $image_id){
        try{
            $stmt = $this->dbconn->prepare("SELECT image_path FROM product_images WHERE image_id = ? AND product_id = ?");
            $stmt->execute([(int)$image_id, (int)$product_id]);
            $img = $stmt->fetch();
            if(!$img) return false;

            $stmtReset = $this->dbconn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            $stmtReset->execute([(int)$product_id]);

            $stmtSet = $this->dbconn->prepare("UPDATE product_images SET is_primary = 1 WHERE image_id = ?");
            $stmtSet->execute([(int)$image_id]);

            $stmtProd = $this->dbconn->prepare("UPDATE products SET product_img = ? WHERE product_id = ?");
            $stmtProd->execute([$img['image_path'], (int)$product_id]);

            return true;
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete_product_image($image_id, $product_id){
        try{
            $stmt = $this->dbconn->prepare("SELECT * FROM product_images WHERE image_id = ? AND product_id = ?");
            $stmt->execute([(int)$image_id, (int)$product_id]);
            $img = $stmt->fetch();
            if(!$img) return false;

            $product_id = (int)$img['product_id'];
            $was_primary = (int)$img['is_primary'];
            $file_path = $img['image_path'];

            $delStmt = $this->dbconn->prepare("DELETE FROM product_images WHERE image_id = ?");
            $delStmt->execute([(int)$image_id]);

            if(strpos($file_path, 'upload/') === 0 && file_exists(__DIR__ . "/../" . $file_path)){
                @unlink(__DIR__ . "/../" . $file_path);
            }

            if($was_primary){
                $stmtNext = $this->dbconn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY image_id ASC LIMIT 1");
                $stmtNext->execute([$product_id]);
                $nextImg = $stmtNext->fetch();
                if($nextImg){
                    $this->set_primary_image($product_id, $nextImg['image_id']);
                } else {
                    $stmtReset = $this->dbconn->prepare("UPDATE products SET product_img = 'assets/images/logo.png' WHERE product_id = ?");
                    $stmtReset->execute([$product_id]);
                }
            }

            return true;
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function upload_image($file){
        if(!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK
            || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])){
            return false;
        }

        $max_file_size = 8 * 1024 * 1024;
        $file_size = filesize($file['tmp_name']);
        if($file_size === false || $file_size <= 0 || $file_size > $max_file_size){
            return false;
        }

        $allowed_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif'
        ];
        $file_name = basename((string)($file['name'] ?? ''));
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if(!isset($allowed_types[$ext]) || !function_exists('finfo_open')){
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if($finfo === false){
            return false;
        }
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $image_info = @getimagesize($file['tmp_name']);
        if($mime_type !== $allowed_types[$ext] || $image_info === false
            || $image_info[0] > 6000 || $image_info[1] > 6000
            || ($image_info[0] * $image_info[1]) > 30000000){
            return false;
        }

        $target_dir = UPLOAD_DIR;
        if(!is_dir($target_dir) && !mkdir($target_dir, 0755, true) && !is_dir($target_dir)){
            return false;
        }

        try{
            $new_filename = "product_" . bin2hex(random_bytes(16)) . "." . $ext;
        }catch(Throwable $e){
            return false;
        }

        $target_filepath = $target_dir . $new_filename;

        if(move_uploaded_file($file['tmp_name'], $target_filepath)){
            return "upload/" . $new_filename;
        }

        return false;
    }

    public function upload_multiple_images($files_array){
        $uploaded_paths = [];
        if(!isset($files_array['name'])){
            return $uploaded_paths;
        }

        if(!is_array($files_array['name'])){
            if(isset($files_array['error']) && $files_array['error'] === UPLOAD_ERR_OK){
                $res = $this->upload_image($files_array);
                if($res) $uploaded_paths[] = $res;
            }
            return $uploaded_paths;
        }

        $count = count($files_array['name']);
        if($count > 8){
            return $uploaded_paths;
        }
        for($i = 0; $i < $count; $i++){
            if(isset($files_array['error'][$i]) && $files_array['error'][$i] === UPLOAD_ERR_OK){
                $single = [
                    'name' => $files_array['name'][$i],
                    'type' => $files_array['type'][$i] ?? '',
                    'tmp_name' => $files_array['tmp_name'][$i],
                    'error' => $files_array['error'][$i],
                    'size' => $files_array['size'][$i] ?? 0
                ];
                $path = $this->upload_image($single);
                if($path){
                    $uploaded_paths[] = $path;
                }
            }
        }
        return $uploaded_paths;
    }
}

?>
