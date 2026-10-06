<?php

require_once __DIR__ . "/Db.php";

class Category extends Db {
    private $dbconn;

    public function __construct(){
        $this->dbconn = $this->connect();
    }

    public function get_all_categories(){
        try{
            $sql = "SELECT c.*, COUNT(p.product_id) AS product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.category_id = p.category_id 
                    GROUP BY c.category_id 
                    ORDER BY c.category_name ASC";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }catch(PDOException $e){
            error_log($e->getMessage());
            return [];
        }
    }

    public function get_category_by_id($id){
        try{
            $sql = "SELECT * FROM categories WHERE category_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        }catch(PDOException $e){
            return false;
        }
    }

    public function add_category($name, $desc = ''){
        try{
            $sql = "INSERT INTO categories (category_name, category_desc) VALUES (?, ?)";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$name, $desc]);
            return $this->dbconn->lastInsertId();
        }catch(PDOException $e){
            return false;
        }
    }

    public function update_category($id, $name, $desc = ''){
        try{
            $sql = "UPDATE categories SET category_name = ?, category_desc = ? WHERE category_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            return $stmt->execute([$name, $desc, (int)$id]);
        }catch(PDOException $e){
            return false;
        }
    }

    public function delete_category($id){
        try{
            $sql = "DELETE FROM categories WHERE category_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            return $stmt->execute([(int)$id]);
        }catch(PDOException $e){
            return false;
        }
    }
}

?>
