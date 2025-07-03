<?php
require_once 'config.php';

class OTPHandler {
    private $mailgun_api_key;
    private $sender_email;
    private $mailgun_domain;
    private $resend_delay = 60; // seconds
    private $otp_length = 6;
    private $otp_expiry = 180; // 3 minutes

    public function __construct() {
        $this->mailgun_api_key = MAILGUN_API_KEY;
        $this->sender_email = SENDER_EMAIL;
        $this->mailgun_domain = MAILGUN_DOMAIN;
    }

    public function generateOTP() {
        return str_pad(random_int(0, pow(10, $this->otp_length) - 1), $this->otp_length, '0', STR_PAD_LEFT);
    }

    public function sendOTP($email) {
        try {
            $otp = $this->generateOTP();
            $otp_id = bin2hex(random_bytes(16)); // Generate unique OTP ID
            
            // Store OTP in database with timestamp
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO otp_codes (otp_id, email, otp_code, created_at, expires_at) 
                                VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))");
            $stmt->execute([$otp_id, $email, password_hash($otp, PASSWORD_DEFAULT), $this->otp_expiry]);

            // Prepare email content
            $html_content = $this->getEmailTemplate($otp);

            // Send via Mailgun API using file_get_contents (Vercel compatible)
            $postData = http_build_query([
                'from' => "healthyDash OTP <{$this->sender_email}>",
                'to' => $email,
                'subject' => 'Kode verifikasi healthyDash anda',
                'html' => $html_content,
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Basic ' . base64_encode("api:{$this->mailgun_api_key}"),
                        'Content-Type: application/x-www-form-urlencoded',
                        'Content-Length: ' . strlen($postData)
                    ],
                    'content' => $postData,
                    'timeout' => 10
                ]
            ]);

            $response = file_get_contents("https://api.mailgun.net/v3/{$this->mailgun_domain}/messages", false, $context);
            
            if ($response === false) {
                throw new Exception("Failed to send email");
            }

            return $otp_id;

        } catch (Exception $e) {
            error_log("OTP Send Error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyOTP($otp_id, $otp_code) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT otp_code, expires_at FROM otp_codes 
                                WHERE otp_id = ? AND used = 0 AND expires_at > NOW()");
            $stmt->execute([$otp_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return false;
            }

            $valid = password_verify($otp_code, $result['otp_code']);

            if ($valid) {
                // Mark OTP as used
                $stmt = $db->prepare("UPDATE otp_codes SET used = 1 WHERE otp_id = ?");
                $stmt->execute([$otp_id]);
            }

            return $valid;

        } catch (Exception $e) {
            error_log("OTP Verification Error: " . $e->getMessage());
            return false;
        }
    }

    public function canResendOTP() {
        return !isset($_SESSION['last_otp_time']) || 
               (time() - $_SESSION['last_otp_time'] >= $this->resend_delay);
    }

    public function getRemainingDelay() {
        if (!isset($_SESSION['last_otp_time'])) return 0;
        $elapsed = time() - $_SESSION['last_otp_time'];
        return max(0, $this->resend_delay - $elapsed);
    }

    private function getEmailTemplate($otp) {
        $site_url = SITE_URL;
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>healthyDash Verification Code</title>
        </head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="text-align: center; margin-bottom: 15px;">
        <img src="' . $site_url . '/assets/images/healthydashlogo.png" alt="healthyDash Logo" style="max-width: 200px;">
    </div>
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
        <h2 style="color: #567733; margin-bottom: 20px; text-align: center; font-size: 24px;">Your Verification Code</h2>
        <p style="margin-bottom: 30px; text-align: center; color: #444444;">Welcome to healthyDash! Please enter this verification code to complete your registration:</p>
        <div style="background-color: #f8f8f8; padding: 20px; border-radius: 5px; text-align: center; margin-bottom: 30px; border: 2px solid #567733;">
            <span style="font-size: 32px; letter-spacing: 5px; font-weight: bold; color: #567733;">' . $otp . '</span>
        </div>
        <div style="background-color: #FFF5E6; border: 1px solid #FFE0B2; color: #8B4513; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <strong>⚠️ Important</strong><br>
            For your security, never share this code with anyone.
        </div>
        <p style="font-size: 14px; color: #666666; text-align: center;">This verification code will expire in 3 minutes.</p>
        <p style="font-size: 14px; color: #666666; text-align: center;">If you did not sign up for healthyDash, please ignore this email.</p>
    </div>
    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #567733;">
        <p>&copy; ' . date('Y') . ' healthyDash. All rights reserved.</p>
    </div>
</body>
        </html>';
    }
}