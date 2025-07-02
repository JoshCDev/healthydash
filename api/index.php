<?php
// Main entry point for Vercel deployment
// This file handles routing for the entire application

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove query string for routing
$route = strtok($path, '?');

// Handle routing based on the path
switch ($route) {
    case '/':
    case '/index.php':
        // Redirect to signup page
        header('Location: /signup.php');
        exit;
        
    case '/signup.php':
        require_once __DIR__ . '/signup.php';
        break;
        
    case '/login.php':
        require_once __DIR__ . '/login.php';
        break;
        
    case '/menu.php':
        require_once __DIR__ . '/menu.php';
        break;
        
    case '/cart.php':
        require_once __DIR__ . '/cart.php';
        break;
        
    case '/settings.php':
        require_once __DIR__ . '/settings.php';
        break;
        
    case '/order-history.php':
        require_once __DIR__ . '/order-history.php';
        break;
        
    case '/address.php':
        require_once __DIR__ . '/address.php';
        break;
        
    case '/OTP.php':
        require_once __DIR__ . '/OTP.php';
        break;
        
    case '/forgot.php':
        require_once __DIR__ . '/forgot.php';
        break;
        
    case '/reset-password.php':
        require_once __DIR__ . '/reset-password.php';
        break;
        
    case '/success.php':
        require_once __DIR__ . '/success.php';
        break;
        
    case '/logout.php':
        require_once __DIR__ . '/logout.php';
        break;
        
    case '/save-address.php':
        require_once __DIR__ . '/save-address.php';
        break;
        
    case '/get-addresses.php':
        require_once __DIR__ . '/get-addresses.php';
        break;
        
    case '/place-order.php':
        require_once __DIR__ . '/place-order.php';
        break;
        
    // Handle includes routes
    case '/includes/logout.php':
        require_once __DIR__ . '/includes/logout.php';
        break;
        
    case '/includes/place_order.php':
        require_once __DIR__ . '/includes/place_order.php';
        break;
        
    case '/includes/get_addresses.php':
        require_once __DIR__ . '/includes/get_addresses.php';
        break;
        
    case '/includes/save_address.php':
        require_once __DIR__ . '/includes/save_address.php';
        break;
        
    case '/includes/check_availability.php':
        require_once __DIR__ . '/includes/check_availability.php';
        break;
        
    case '/includes/otp_handler.php':
        require_once __DIR__ . '/includes/otp_handler.php';
        break;
        
    case '/includes/reset_handler.php':
        require_once __DIR__ . '/includes/reset_handler.php';
        break;
        
    default:
        // 404 handling
        http_response_code(404);
        echo json_encode(['error' => 'Page not found']);
        break;
}
?> 