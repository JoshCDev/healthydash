<?php
class ResetHandler {
    private $mailgun_api_key;
    private $sender_email;
    private $mailgun_domain;
    private $token_expiry = 180; // 3 minutes in seconds

    public function __construct() {
        $this->mailgun_api_key = MAILGUN_API_KEY;
        $this->sender_email = SENDER_EMAIL;
        $this->mailgun_domain = MAILGUN_DOMAIN;
    }

    public function generateToken() {
        return bin2hex(random_bytes(16)); // 32 characters
    }

    public function createResetToken($userId) {
        try {
            $db = Database::getInstance();
            
            // First, invalidate any existing tokens
            $stmt = $db->prepare("
                UPDATE password_reset_tokens 
                SET used = 1 
                WHERE user_id = ? AND used = 0
            ");
            $stmt->execute([$userId]);
            
            // Generate new token
            $token = $this->generateToken();
            $expires = date('Y-m-d H:i:s', time() + $this->token_expiry);

            // Store new token
            $stmt = $db->prepare("
                INSERT INTO password_reset_tokens (
                    user_id,
                    token,
                    expires_at,
                    created_at
                ) VALUES (?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([$userId, $token, $expires]);
            
            // Verify token was stored correctly
            if ($result) {
                $stmt = $db->prepare("SELECT token FROM password_reset_tokens WHERE token = ? AND used = 0 LIMIT 1");
                $stmt->execute([$token]);
                if ($stmt->rowCount() === 0) {
                    error_log("Token not found after insertion");
                    return false;
                }
            }
            
            return $result ? $token : false;
        } catch (Exception $e) {
            error_log("Error creating reset token: " . $e->getMessage());
            return false;
        }
    }

    public function sendResetEmail($email, $token) {
        // Generate absolute URL that points to pages/reset-password.php
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
// Should be
$reset_url = sprintf(
    '%s://%s/healthydash/pages/reset-password.php?token=%s',
    $protocol,
    $host,
    urlencode($token)
);
        
        error_log("Generated reset URL: " . $reset_url);

        $html_content = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reset Your Password - HealthyDash</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="../assets/images/healthydashlogo.png" alt="healthyDash Logo" style="max-width: 200px;">
            </div>
            <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;">
                <h2 style="color: #567733; margin-top: 0; margin-bottom: 20px; text-align: center; font-size: 24px;">Reset Your Password</h2>
                <p style="margin-bottom: 30px; text-align: center; color: #444444;">A password reset was requested for your healthyDash account. Click the button below to reset your password.</p>
                <div style="text-align: center; margin-bottom: 30px;">
                    <a href="' . $reset_url . '" style="background-color: #567733; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; border: none;">Reset Password</a>
                </div>
                <p style="margin-top: 20px; text-align: center; color: #666666;">Or copy and paste this URL into your browser:</p>
                <p style="background-color: #f8f8f8; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px; text-align: center; border: 1px solid #e0e0e0; color: #567733;">' . $reset_url . '</p>
                <p style="font-size: 14px; color: #666666; text-align: center;">This link will expire in 3 minutes.</p>
                <p style="font-size: 14px; color: #666666; text-align: center;">If you did not request this password reset, please ignore this email or contact support if you have concerns.</p>
                <div style="background-color: #FFF5E6; border: 1px solid #FFE0B2; color: #8B4513; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
                    <strong>⚠️ Security Notice</strong><br>
                    For your security, never share this link with anyone.
                </div>
            </div>
            <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #567733;">
                <p>&copy; ' . date('Y') . ' HealthyDash. All rights reserved.</p>
            </div>
        </body>
        </html>';

        try {
            // Send via Mailgun API using file_get_contents (Vercel compatible)
            $postData = http_build_query([
                    'from' => "HealthyDash Password Reset <{$this->sender_email}>",
                    'to' => $email,
                    'subject' => 'Reset Your HealthyDash Password',
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
            
            error_log("Mailgun response: " . ($response !== false ? 'success' : 'failed'));

            return $response !== false;
        } catch (Exception $e) {
            error_log("Error sending reset email: " . $e->getMessage());
            return false;
        }
    }
}