<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';

// Ensure we're sending JSON response
header('Content-Type: application/json');

try {
    // Get database connection
    $db = Database::getInstance();
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $type = $_POST['type'] ?? '';
    $value = trim($_POST['value'] ?? '');
    
    if (empty($type) || empty($value)) {
        throw new Exception('Invalid input');
    }
    
    // Check based on type
    switch($type) {
        case 'username':
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$value]);
            $count = $stmt->fetchColumn();
            echo json_encode(['available' => ($count === 0)]);
            break;
            
        case 'email':
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$value]);
            $count = $stmt->fetchColumn();
            echo json_encode(['available' => ($count === 0)]);
            break;
            
        case 'user_check':
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$value, $value]);
            $count = $stmt->fetchColumn();
            echo json_encode(['exists' => ($count > 0)]);
            break;
            
        default:
            throw new Exception('Invalid type');
    }

} catch (Exception $e) {
    // Log error but don't expose details
    error_log("Availability check error: " . $e->getMessage());
    echo json_encode([
        'available' => false,
        'error' => 'An error occurred while checking availability'
    ]);
}
?>