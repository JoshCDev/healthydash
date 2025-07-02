<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

function checkAuth() {
    // Regular session check
    if (isset($_SESSION['user_id'])) {
        // Check if session is not too old (24 minutes)
        if (isset($_SESSION['auth_time']) && (time() - $_SESSION['auth_time'] < 1440)) {
            return true;
        }
        // Session expired, clear it
        unset($_SESSION['user_id']);
        unset($_SESSION['auth_time']);
    }

    // Only check remember token if session isn't valid
    if (isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
        try {
            $db = Database::getInstance();
            
            // Check for valid remember token and get user data
            $stmt = $db->prepare("
                SELECT t.*, u.is_active 
                FROM remember_tokens t 
                JOIN users u ON t.user_id = u.user_id 
                WHERE t.user_id = ? 
                AND t.used = 0 
                AND t.expires_at > NOW()
                ORDER BY t.created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$_COOKIE['user_id']]);
            $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($token_data && $token_data['is_active'] && password_verify($_COOKIE['remember_token'], $token_data['token'])) {
                // Mark old token as used
                $stmt = $db->prepare("UPDATE remember_tokens SET used = 1 WHERE id = ?");
                $stmt->execute([$token_data['id']]);

                // Create new token
                $new_token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $db->prepare("
                    INSERT INTO remember_tokens (
                        user_id,
                        token,
                        expires_at,
                        created_at
                    ) VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $token_data['user_id'],
                    password_hash($new_token, PASSWORD_DEFAULT),
                    $expires
                ]);

                // Set new cookies
                setcookie('remember_token', $new_token, strtotime('+30 days'), '/', '', true, true);
                setcookie('user_id', $token_data['user_id'], strtotime('+30 days'), '/', '', true, false);

                // Create new session
                $_SESSION['user_id'] = $token_data['user_id'];
                $_SESSION['auth_time'] = time();
                return true;
            }
        } catch (Exception $e) {
            error_log("Remember token check error: " . $e->getMessage());
        }

        // Invalid or expired token, clear cookies
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
    }

    return false;
}

function requireAuth() {
    if (!checkAuth()) {
        // Store the requested URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

function requireGuest() {
    if (checkAuth()) {
        header("Location: menu.php");
        exit;
    }
}