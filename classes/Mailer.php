<?php
require_once __DIR__ . '/Config.php';

class Mailer {

  private static $smtp_host = SMTP_HOST;
  private static $smtp_port = SMTP_PORT;
  private static $smtp_user = SMTP_USER;
  private static $smtp_pass = SMTP_PASS;
  private static $from_name = MAIL_FROM_NAME;
  private static $from_email = MAIL_FROM_EMAIL;

    /**
     * Sends an email via Gmail SMTP socket connection
     */
    public static function send_mail($to_email, $to_name, $subject, $html_message){
        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if(self::$smtp_user === '' || self::$smtp_pass === '' || !filter_var(self::$smtp_user, FILTER_VALIDATE_EMAIL)){
          error_log('SMTP is not configured. Set the Fazobeth SMTP environment variables.');
          return false;
        }

        if(self::$from_email === '' || strcasecmp(self::$from_email, self::$smtp_user) !== 0){
            error_log('SMTP sender address must match the authenticated Gmail account.');
            return false;
        }

        $subject = self::clean_header($subject);
        $to_name = self::clean_header($to_name);

        try {
            $context = stream_context_create([
                'ssl' => [
              'verify_peer' => true,
              'verify_peer_name' => true
                ]
            ]);

            $socket = @stream_socket_client(self::$smtp_host . ':' . self::$smtp_port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
            if (!$socket) {
                error_log("SMTP Connection Error: $errstr ($errno)");
              return false;
            }

            self::read_response($socket);

            fwrite($socket, "EHLO localhost\r\n");
            self::read_response($socket);

            fwrite($socket, "AUTH LOGIN\r\n");
            self::read_response($socket);

            fwrite($socket, base64_encode(self::$smtp_user) . "\r\n");
            self::read_response($socket);

            fwrite($socket, base64_encode(self::$smtp_pass) . "\r\n");
            $auth_res = self::read_response($socket);

            if (strpos($auth_res, '235') === false) {
                error_log("SMTP Auth Failed: $auth_res");
                fclose($socket);
                return false;
            }

            fwrite($socket, "MAIL FROM: <" . self::$smtp_user . ">\r\n");
            self::read_response($socket);

            fwrite($socket, "RCPT TO: <" . $to_email . ">\r\n");
            self::read_response($socket);

            fwrite($socket, "DATA\r\n");
            self::read_response($socket);

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . self::clean_header(self::$from_name) . " <" . self::$from_email . ">\r\n";
            $headers .= "To: " . ($to_name ?: $to_email) . " <" . $to_email . ">\r\n";
            $headers .= "Subject: " . $subject . "\r\n";

            $email_content = $headers . "\r\n" . $html_message . "\r\n.\r\n";
            fwrite($socket, $email_content);
            $send_res = self::read_response($socket);

            fwrite($socket, "QUIT\r\n");
            fclose($socket);

            return strpos($send_res, '250') !== false;
        } catch (Throwable $e) {
            error_log("SMTP Exception: " . $e->getMessage());
          return false;
        }
    }

    private static function read_response($socket) {
        $response = '';
        while ($str = fgets($socket, 512)) {
            $response .= $str;
            if (substr($str, 3, 1) == ' ') break;
        }
        return $response;
    }

    private static function clean_header($value){
      return trim(preg_replace('/[\r\n]+/', ' ', (string)$value));
    }

    public static function send_password_reset_otp($to_email, $first_name, $otp){
        $subject = "Your Fazobeth password reset code";
        $safe_name = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
        $message = "<html><body style='font-family:Arial,sans-serif;color:#333;'>
          <div style='max-width:500px;margin:0 auto;padding:20px;border:1px solid #eee;border-radius:8px;'>
            <h2 style='color:#c9563d;margin-top:0;'>Fazobeth Ventures</h2>
            <p>Hello " . $safe_name . ",</p>
            <p>Use this one-time code to reset your password:</p>
            <div style='background:#f8f9fa;padding:15px;text-align:center;border-radius:6px;margin:20px 0;'>
              <span style='font-size:32px;font-weight:bold;letter-spacing:8px;color:#c9563d;'>" . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . "</span>
            </div>
            <p style='color:#666;font-size:14px;'>This code expires in 10 minutes. If you did not request a reset, you can ignore this email.</p>
          </div>
        </body></html>";

        return self::send_mail($to_email, $first_name, $subject, $message);
    }

    public static function send_order_confirmation($to_email, $customer_name, $order_number, $total_amount, $delivery_type, $delivery_info, $items = []){
        $subject = "Order Confirmation #" . $order_number . " - Fazobeth Ventures";

        $item_rows = "";
        foreach($items as $item){
            $item_rows .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: center;'>" . $item['quantity'] . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>₦" . number_format($item['price'], 2) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>₦" . number_format($item['price'] * $item['quantity'], 2) . "</td>
            </tr>";
        }

        $message = "
        <html>
        <head>
          <title>Order Confirmation</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f6f8; padding: 20px;'>
          <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <div style='text-align: center; margin-bottom: 20px;'>
              <h2 style='color: #c9563d; margin: 0;'>Fazobeth Ventures</h2>
              <p style='color: #6c757d; font-size: 14px;'>Thank you for your order!</p>
            </div>
            
            <p>Dear <strong>" . htmlspecialchars($customer_name) . "</strong>,</p>
            <p>We are excited to let you know that your order <strong>#" . htmlspecialchars($order_number) . "</strong> has been confirmed and is now being prepared for shipping!</p>

            <div style='background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0;'>
              <h4 style='margin-top:0; color:#333;'>Delivery Information:</h4>
              <p style='margin:5px 0;'><strong>Method:</strong> Online payments</p>
              <p style='margin:5px 0;'><strong>Details / Address:</strong> " . htmlspecialchars($delivery_info) . "</p>
            </div>

            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
              <thead>
                <tr style='background: #e9ecef;'>
                  <th style='padding: 8px; text-align: left;'>Item</th>
                  <th style='padding: 8px; text-align: center;'>Qty</th>
                  <th style='padding: 8px; text-align: right;'>Price</th>
                  <th style='padding: 8px; text-align: right;'>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                " . $item_rows . "
              </tbody>
            </table>

            <div style='text-align: right; margin-top: 15px;'>
              <h3 style='color: #2b2b2b;'>Total Paid: <span style='color: #d63384;'>₦" . number_format($total_amount, 2) . "</span></h3>
            </div>

            <hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;' />
            <p style='font-size: 12px; color: #888; text-align: center;'>If you have any questions, feel free to contact us at support@fazobeth.com.</p>
          </div>
        </body>
        </html>
        ";

        return self::send_mail($to_email, $customer_name, $subject, $message);
    }
}

?>
