<?php
// Simple working configuration for HealthyDash

// Database configuration
define('DB_HOST', 'healthydash-healthydash.c.aivencloud.com');
define('DB_NAME', 'defaultdb');
define('DB_USER', 'avnadmin');
define('DB_PASS', getenv('DB_PASS') ?: 'your_aiven_password_here');
define('DB_PORT', '15146');
define('DB_SSL_MODE', 'REQUIRED');

// Application configuration
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'your_google_client_id_here');
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: 'your_google_maps_api_key_here');

// Mailgun configuration
define('MAILGUN_API_KEY', getenv('MAILGUN_API_KEY') ?: 'your_mailgun_api_key_here');
define('MAILGUN_DOMAIN', getenv('MAILGUN_DOMAIN') ?: 'otp.jflyc.com');
define('SENDER_EMAIL', getenv('SENDER_EMAIL') ?: 'noreply@yourdomain.com');

// Database connection class
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4;sslmode=verify-ca";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->conn->exec("SET time_zone = '+00:00'");
            
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }
}

// Utility functions
function isVercelEnvironment() {
    return isset($_ENV['VERCEL']) || getenv('VERCEL');
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

function handleError($message, $code = 500) {
    error_log($message);
    http_response_code($code);
    echo json_encode(['error' => 'An error occurred']);
    exit;
}

// Session configuration (only for web requests, not CLI)
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isVercelEnvironment() ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', 86400);
        
        session_start();
    }
}