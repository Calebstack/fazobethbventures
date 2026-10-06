<?php

require_once __DIR__ . "/Db.php";
require_once __DIR__ . "/Mailer.php";

class User extends Db {
    private $dbconn;

    public function __construct(){
        $this->dbconn = $this->connect();
    }

    public function check_email_exists($email){
        try{
            $sql = "SELECT user_id FROM users WHERE email = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->rowCount() > 0;
        }catch(PDOException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    public function signup($firstname, $lastname, $email, $password, $phone = '', $address = ''){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Account creation is temporarily unavailable."];
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL) || trim($firstname) === '' || trim($lastname) === '' || strlen($password) < 8){
            return ["status" => false, "message" => "Enter valid account details and a password of at least 8 characters."];
        }

        if($this->check_email_exists($email)){
            return ["status" => false, "message" => "If this address is already registered, please sign in or reset your password."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try{
            $sql = "INSERT INTO users(firstname, lastname, email, password, phone, address) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$firstname, $lastname, $email, $hash, $phone, $address]);
            $user_id = $this->dbconn->lastInsertId();
            return ["status" => true, "user_id" => $user_id, "message" => "Account created successfully."];
        }catch(PDOException $e){
            error_log("User signup error: " . $e->getMessage());
            return ["status" => false, "message" => "Unable to create your account right now."];
        }
    }

    public function login($email, $password){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Login is temporarily unavailable."];
        }

        try{
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if($user){
                if(password_verify($password, $user['password'])){
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $user['user_id'],
                        'firstname' => $user['firstname'],
                        'lastname' => $user['lastname'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                        'address' => $user['address']
                    ];
                    return ["status" => true, "user" => $_SESSION['user']];
                }else{
                    return ["status" => false, "message" => "Invalid email or password."];
                }
            }else{
                return ["status" => false, "message" => "Invalid email or password."];
            }
        }catch(PDOException $e){
            error_log("User login error: " . $e->getMessage());
            return ["status" => false, "message" => "Login is temporarily unavailable."];
        }
    }

    public function request_password_reset($email){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Database connection failed."];
        }

        try{
            $stmt = $this->dbconn->prepare("SELECT firstname FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if(!$user){
                return ["status" => true, "message" => "If that email is registered, a reset code has been sent."];
            }

            $stmt = $this->dbconn->prepare("SELECT created_at FROM password_reset_otps WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            if($existing && strtotime($existing['created_at']) > time() - 60){
                return ["status" => true, "message" => "If that email is registered, a reset code has been sent."];
            }

            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');
            $expires_at = date('Y-m-d H:i:s', time() + 600);
            $stmt = $this->dbconn->prepare(
                "INSERT INTO password_reset_otps (email, otp_hash, expires_at, attempts, created_at)
                 VALUES (?, ?, ?, 0, ?)
                 ON DUPLICATE KEY UPDATE otp_hash = VALUES(otp_hash), expires_at = VALUES(expires_at), attempts = 0, created_at = VALUES(created_at)"
            );
            $stmt->execute([$email, $otp_hash, $expires_at, $now]);

            if(!Mailer::send_password_reset_otp($email, $user['firstname'], $otp)){
                $stmt = $this->dbconn->prepare("DELETE FROM password_reset_otps WHERE email = ?");
                $stmt->execute([$email]);
                return ["status" => false, "message" => "We couldn't send the reset code. Please try again later."];
            }

            return ["status" => true, "message" => "If that email is registered, a reset code has been sent."];
        }catch(PDOException $e){
            error_log("Password reset request error: " . $e->getMessage());
            return ["status" => false, "message" => "Password reset is temporarily unavailable."];
        }
    }

    public function verify_otp($email, $otp){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Database connection failed."];
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return ["status" => false, "message" => "Enter a valid email address."];
        }

        if(!preg_match('/^\d{6}$/', $otp)){
            return ["status" => false, "message" => "Enter the 6-digit code sent to your email."];
        }

        try{
            $stmt = $this->dbconn->prepare("SELECT otp_hash, expires_at, attempts FROM password_reset_otps WHERE email = ?");
            $stmt->execute([$email]);
            $reset = $stmt->fetch();

            if(!$reset){
                return ["status" => false, "message" => "No reset code found or it has expired. Please request a new code."];
            }

            if((int) $reset['attempts'] >= 5){
                return ["status" => false, "message" => "Too many invalid attempts. Please request a new code."];
            }

            if(strtotime($reset['expires_at']) <= time()){
                return ["status" => false, "message" => "This code has expired. Please request a new code."];
            }

            if(!password_verify($otp, $reset['otp_hash'])){
                $stmt = $this->dbconn->prepare("UPDATE password_reset_otps SET attempts = attempts + 1 WHERE email = ?");
                $stmt->execute([$email]);
                return ["status" => false, "message" => "The verification code is invalid. Please check your email and try again."];
            }

            return ["status" => true, "message" => "Code verified successfully."];
        }catch(PDOException $e){
            error_log("OTP verification error: " . $e->getMessage());
            return ["status" => false, "message" => "Verification is temporarily unavailable."];
        }
    }

    public function reset_password_with_otp($email, $otp, $new_password){
        if(!$this->dbconn){
            return ["status" => false, "message" => "Database connection failed."];
        }

        if(strlen($new_password) < 8){
            return ["status" => false, "message" => "Your password must be at least 8 characters long."];
        }

        try{
            $this->dbconn->beginTransaction();
            $stmt = $this->dbconn->prepare("SELECT otp_hash, expires_at, attempts FROM password_reset_otps WHERE email = ? FOR UPDATE");
            $stmt->execute([$email]);
            $reset = $stmt->fetch();

            if(!$reset || strtotime($reset['expires_at']) <= time() || (int) $reset['attempts'] >= 5){
                $this->dbconn->rollBack();
                return ["status" => false, "message" => "That code is invalid or expired. Request a new one."];
            }

            if(!password_verify($otp, $reset['otp_hash'])){
                $stmt = $this->dbconn->prepare("UPDATE password_reset_otps SET attempts = attempts + 1 WHERE email = ?");
                $stmt->execute([$email]);
                $this->dbconn->commit();
                return ["status" => false, "message" => "That code is invalid or expired. Request a new one."];
            }

            $stmt = $this->dbconn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $email]);
            $stmt = $this->dbconn->prepare("DELETE FROM password_reset_otps WHERE email = ?");
            $stmt->execute([$email]);
            $this->dbconn->commit();

            return ["status" => true, "message" => "Your password has been reset. You can now log in."];
        }catch(PDOException $e){
            if($this->dbconn->inTransaction()){
                $this->dbconn->rollBack();
            }
            error_log("Password reset error: " . $e->getMessage());
            return ["status" => false, "message" => "Password reset is temporarily unavailable."];
        }
    }

    public function get_user_by_id($id){
        try{
            $sql = "SELECT user_id, firstname, lastname, email, phone, address, created_at FROM users WHERE user_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }catch(PDOException $e){
            return false;
        }
    }

    public function update_profile($user_id, $firstname, $lastname, $phone, $address){
        try{
            $sql = "UPDATE users SET firstname = ?, lastname = ?, phone = ?, address = ? WHERE user_id = ?";
            $stmt = $this->dbconn->prepare($sql);
            $res = $stmt->execute([$firstname, $lastname, $phone, $address, $user_id]);
            
            if($res && isset($_SESSION['user']) && $_SESSION['user']['id'] == $user_id){
                $_SESSION['user']['firstname'] = $firstname;
                $_SESSION['user']['lastname'] = $lastname;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;
            }
            return true;
        }catch(PDOException $e){
            return false;
        }
    }

    public function logout(){
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}

?>
