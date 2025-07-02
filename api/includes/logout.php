<?php
// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session
session_unset();
session_destroy();

// Clear remember me cookies if they exist
if (isset($_COOKIE['remember_token']) || isset($_COOKIE['user_id'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: /login.php');
exit;