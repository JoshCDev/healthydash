<?php
class PasswordReset {
    private $mailgun_api_key;
    private $sender_email;
    private $mailgun_domain;
    private $token_expiry = 600; // Increased to 10 minutes
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mailgun_api_key = MAILGUN_API_KEY;
        $this->mailgun_domain = MAILGUN_DOMAIN;
        $this->sender_email = SENDER_EMAIL;
    }

    public function findUser($identifier) {
        try {
            // Check if user exists by email or username
            $stmt = $this->db->prepare("
                SELECT user_id, email, username 
                FROM users 
                WHERE (email = ? OR username = ?) 
                AND is_active = 1
            ");
            $stmt->execute([$identifier, $identifier]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding user: " . $e->getMessage());
            return false;
        }
    }

    public function createToken($userId) {
        try {
            // First, invalidate any existing tokens
            $stmt = $this->db->prepare("
                UPDATE password_reset_tokens 
                SET used = 1 
                WHERE user_id = ? AND used = 0
            ");
            $stmt->execute([$userId]);

            // Generate new token
            $token = bin2hex(random_bytes(32));
            
            // Set expiration with proper timezone
            date_default_timezone_set('Asia/Jakarta');
            $expires = date('Y-m-d H:i:s', time() + $this->token_expiry);

            // Store new token with explicit timezone
            $stmt = $this->db->prepare("
                INSERT INTO password_reset_tokens (
                    user_id,
                    token,
                    expires_at,
                    created_at
                ) VALUES (?, ?, ?, NOW())
            ");
            $result = $stmt->execute([
                $userId,
                $token,
                $expires
            ]);

            // Verify token was stored
            if ($result) {
                $stmt = $this->db->prepare("
                    SELECT token FROM password_reset_tokens 
                    WHERE token = ? AND used = 0 
                    LIMIT 1
                ");
                $stmt->execute([$token]);
                if ($stmt->rowCount() === 0) {
                    error_log("Token verification failed after creation");
                    return false;
                }
            }

            return $result ? $token : false;
        } catch (Exception $e) {
            error_log("Error creating token: " . $e->getMessage());
            return false;
        }
    }

    public function verifyToken($token) {
        try {
            // Add buffer time to prevent edge-case expiration
            $stmt = $this->db->prepare("
                SELECT t.*, u.email, u.username 
                FROM password_reset_tokens t
                JOIN users u ON t.user_id = u.user_id
                WHERE t.token = ? 
                AND t.used = 0 
                AND t.expires_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                AND u.is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Token verified successfully: " . $token);
                error_log("Expires at: " . $result['expires_at']);
                error_log("Current time: " . date('Y-m-d H:i:s'));
            } else {
                error_log("Token verification failed: " . $token);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error verifying token: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword($token, $newPassword) {
        try {
            $this->db->beginTransaction();

            // Verify token again with same buffer time
            $tokenData = $this->verifyToken($token);
            if (!$tokenData) {
                error_log("Token validation failed during password reset");
                return false;
            }

            // Update password
            $password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->execute([$password_hash, $tokenData['user_id']]);

            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);

            // Invalidate remember me tokens
            $stmt = $this->db->prepare("UPDATE remember_tokens SET used = 1 WHERE user_id = ?");
            $stmt->execute([$tokenData['user_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error resetting password: " . $e->getMessage());
            return false;
        }
    }

    public function sendResetEmail($email, $token, $username) {
        try {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            
            // Generate proper reset URL for Vercel deployment
            $reset_url = sprintf(
                '%s://%s/reset-password.php?token=%s',
                $protocol,
                $host,
                urlencode($token)
            );

            $html_content = $this->getEmailTemplate($reset_url, $username);

            // Log configuration for debugging
            error_log("Mailgun configuration - Domain: " . $this->mailgun_domain);
            error_log("Sender email: " . $this->sender_email);
            error_log("Sending to: " . $email);
            
            // Use file_get_contents instead of curl for Vercel compatibility
            $postData = http_build_query([
                'from' => "HealthyDash <{$this->sender_email}>",
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
                    'timeout' => 10,
                    'ignore_errors' => true // This allows us to get response even on HTTP error
                ]
            ]);

            $response = @file_get_contents(
                "https://api.mailgun.net/v3/{$this->mailgun_domain}/messages",
                false,
                $context
            );
            
            // Get the actual HTTP response headers
            $http_response_header_string = isset($http_response_header) ? implode("\n", $http_response_header) : '';
            
            // Extract HTTP status code
            $http_status = 500; // Default to error
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header_string, $matches)) {
                $http_status = intval($matches[1]);
            }
            
            if ($response === false) {
                error_log("Failed to send email via Mailgun - No response received");
                error_log("Possible causes: Invalid API key, network error, or incorrect domain");
                return false;
            }

            error_log("Email send status: " . $http_status);
            error_log("Email response: " . $response);
            
            // Decode response to check for specific errors
            $responseData = json_decode($response, true);
            if ($http_status !== 200) {
                $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
                error_log("Mailgun error: " . $errorMessage);
                
                // Check for specific errors
                if (strpos($errorMessage, 'Forbidden') !== false) {
                    error_log("API Key authentication failed. Please check MAILGUN_API_KEY environment variable.");
                } elseif (strpos($errorMessage, 'not found') !== false) {
                    error_log("Domain not found. Please check MAILGUN_DOMAIN configuration.");
                }
            }

            return $http_status === 200;
        } catch (Exception $e) {
            error_log("Error sending reset email: " . $e->getMessage());
            return false;
        }
    }

    private function getEmailTemplate($reset_url, $username) {
        return '
            <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password - HealthyDash</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="text-align: center; margin-bottom: 15px;">
        <img src="../assets/images/healthydashlogo.png" alt="HealthyDash Logo" style="max-width: 200px;">
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
    }
}
