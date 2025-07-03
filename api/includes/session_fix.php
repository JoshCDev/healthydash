<?php
// Session management fix for sudden logout issues
// This file handles session initialization with proper settings

function initializeSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters before starting session
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $isVercel = isset($_ENV['VERCEL']) || getenv('VERCEL');
        
        session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/',
            'domain' => '', // Empty for current domain
            'secure' => $isSecure || $isVercel,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Set session save handler options
        ini_set('session.gc_maxlifetime', 86400);
        ini_set('session.cookie_lifetime', 86400);
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 3600) { // Every hour
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Function to set auth session properly
function setAuthSession($userId) {
    initializeSession();
    
    // Set user session
    $_SESSION['user_id'] = $userId;
    $_SESSION['auth_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Log the session creation for debugging
    error_log("Session created for user: $userId at " . date('Y-m-d H:i:s'));
}

// Function to check if session is valid
function isSessionValid() {
    initializeSession();
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout (24 hours of inactivity)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
        // Session expired
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Function to safely destroy session
function destroyAuthSession() {
    if (session_status() !== PHP_SESSION_NONE) {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }
} 