<?php
// Test email configuration endpoint
// This file should be DELETED after testing

// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');

// Only allow authenticated users to access this debug endpoint
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$config = [
    'mailgun_api_key_set' => !empty(MAILGUN_API_KEY) && MAILGUN_API_KEY !== 'your_mailgun_api_key_here',
    'mailgun_domain' => MAILGUN_DOMAIN,
    'sender_email' => SENDER_EMAIL,
    'sender_email_set' => !empty(SENDER_EMAIL) && SENDER_EMAIL !== 'noreply@yourdomain.com',
    'is_vercel' => isVercelEnvironment(),
    'api_key_length' => strlen(MAILGUN_API_KEY),
    'environment_vars' => [
        'MAILGUN_API_KEY_ENV' => !empty(getenv('MAILGUN_API_KEY')),
        'SENDER_EMAIL_ENV' => !empty(getenv('SENDER_EMAIL')),
    ]
];

// Test actual API connection (without sending email)
$testUrl = "https://api.mailgun.net/v3/" . MAILGUN_DOMAIN . "/domains";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Authorization: Basic ' . base64_encode("api:" . MAILGUN_API_KEY),
        ],
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($testUrl, false, $context);
$http_response_header_string = isset($http_response_header) ? implode("\n", $http_response_header) : '';

// Extract HTTP status code
$http_status = 0;
if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header_string, $matches)) {
    $http_status = intval($matches[1]);
}

$config['api_test'] = [
    'status_code' => $http_status,
    'success' => $http_status === 200,
    'error' => $http_status === 401 ? 'Invalid API Key' : ($http_status === 404 ? 'Domain not found' : null)
];

echo json_encode($config, JSON_PRETTY_PRINT); 