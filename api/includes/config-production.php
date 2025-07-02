<?php
// Production-ready configuration for Vercel deployment with Aiven.io MySQL

// Database configuration - Use environment variables in production, fallback for local
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'healthydash-healthydash.c.aivencloud.com');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'defaultdb');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'avnadmin');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? 'your_aiven_password_here');
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '15146');
define('DB_SSL_MODE', $_ENV['DB_SSL_MODE'] ?? getenv('DB_SSL_MODE') ?? 'REQUIRED');

// Application configuration
define('SITE_URL', $_ENV['SITE_URL'] ?? getenv('SITE_URL') ?? 'http://localhost');
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?? 'your_google_client_id_here');
define('GOOGLE_MAPS_API_KEY', $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?? 'your_google_maps_api_key_here');

// Mailgun configuration for OTP emails
define('MAILGUN_API_KEY', $_ENV['MAILGUN_API_KEY'] ?? getenv('MAILGUN_API_KEY') ?? 'your_mailgun_api_key_here');
define('MAILGUN_DOMAIN', $_ENV['MAILGUN_DOMAIN'] ?? getenv('MAILGUN_DOMAIN') ?? 'otp.jflyc.com');
define('SENDER_EMAIL', $_ENV['SENDER_EMAIL'] ?? getenv('SENDER_EMAIL') ?? 'noreply@yourdomain.com');

// Database connection class
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            // Build DSN with SSL support for Aiven.io
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            // SSL configuration for Aiven.io
            if (DB_SSL_MODE === 'REQUIRED') {
                $dsn .= ";sslmode=verify-ca";
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Disable for serverless
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];

            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->conn->exec("SET time_zone = '+00:00'");
            
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            // Don't expose database errors in production
            if (isVercelEnvironment()) {
                die("Service temporarily unavailable. Please try again later.");
            } else {
                die("Connection failed: " . $e->getMessage());
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }

    public function __destruct() {
        $this->conn = null;
    }
}

// Utility functions
function isVercelEnvironment() {
    return isset($_ENV['VERCEL']) || getenv('VERCEL');
}

function getBaseUrl() {
    if (isVercelEnvironment()) {
        return SITE_URL;
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

function handleError($message, $code = 500) {
    error_log($message);
    http_response_code($code);
    
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'An error occurred. Please try again.']);
    } else {
        echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Service Error</h1><p>An error occurred. Please try again later.</p></body></html>';
    }
    exit;
}

// Session configuration (only for web requests, not CLI)
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isVercelEnvironment() ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', 1440);
        
        session_start();
    }
}
?> 