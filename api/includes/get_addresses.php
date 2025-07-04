<?php
// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($addresses);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load addresses']);
}