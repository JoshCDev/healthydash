<?php
// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clean up database session if using custom handler
if (defined('VERCEL') && VERCEL && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/session_handler.php';
    
    try {
        $handler = new DatabaseSessionHandler();
        $handler->cleanupUserSessions($_SESSION['user_id'], false); // Clean all user sessions
    } catch (Exception $e) {
        error_log("Session cleanup error: " . $e->getMessage());
    }
}

// Clear session
session_unset();
session_destroy();

// Clear remember me cookies if they exist
if (isset($_COOKIE['remember_token']) || isset($_COOKIE['user_id'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
}

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header('Location: /login.php');
exit;