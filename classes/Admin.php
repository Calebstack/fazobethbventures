<?php

require_once __DIR__ . "/Db.php";

class Admin extends Db {
    private $dbconn;

    public function __construct(){
        $this->dbconn = $this->connect();
    }

    public function login($username, $password){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Database connection error."];
        }

        try{
            $sql = "SELECT * FROM admin WHERE admin_username = ? OR admin_email = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if($admin){
                if(password_verify($password, $admin['admin_password'])){
                    session_regenerate_id(true);
                    $_SESSION['admin'] = [
                        'id' => $admin['admin_id'],
                        'username' => $admin['admin_username'],
                        'email' => $admin['admin_email']
                    ];
                    return ["status" => true, "admin" => $_SESSION['admin']];
                } else {
                    return ["status" => false, "message" => "Invalid username or password."];
                }
            } else {
                return ["status" => false, "message" => "Invalid username or password."];
            }
        }catch(PDOException $e){
            error_log("Admin login error: " . $e->getMessage());
            return ["status" => false, "message" => "Unable to sign in right now."];
        }
    }

    public function get_admin_by_id($id){
        try{
            $sql = "SELECT admin_id, admin_username, admin_email FROM admin WHERE admin_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        }catch(PDOException $e){
            return false;
        }
    }

    public function logout(){
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }
}

?>
