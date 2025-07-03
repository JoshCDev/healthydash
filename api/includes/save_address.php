<?php
// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Parse incoming data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid data received');
    }

    // Validate required fields
    $required_fields = ['address', 'lat', 'lng', 'address_type'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate latitude and longitude
    $lat = floatval($data['lat']);
    $lng = floatval($data['lng']);
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        throw new Exception('Invalid latitude or longitude');
    }

    // Validate and sanitize address type
    $address_type = trim($data['address_type']);
    if (empty($address_type)) {
        throw new Exception('Address type is required');
    }

    // If not a predefined type, validate custom type length
    if (!in_array($address_type, ['Home', 'Office', 'Other']) && strlen($address_type) > 50) {
        throw new Exception('Custom address type is too long (maximum 50 characters)');
    }

    // Sanitize the address type
    $address_type = htmlspecialchars($address_type, ENT_QUOTES, 'UTF-8');

    // Sanitize the address
    $address = htmlspecialchars($data['address'], ENT_QUOTES, 'UTF-8');

    $db = Database::getInstance();
    
    // Start transaction
    $db->beginTransaction();

    try {
        // Check for duplicate address
        $check_stmt = $db->prepare("
            SELECT COUNT(*) AS count
            FROM user_addresses
            WHERE user_id = ? 
            AND (
                (address_line = ? AND latitude = ? AND longitude = ?)
                OR
                (
                    ST_Distance_Sphere(
                        point(longitude, latitude),
                        point(?, ?)
                    ) <= 50  -- 50 meters threshold
                )
            )
        ");
        
        $check_stmt->execute([
            $_SESSION['user_id'], 
            $address,
            $lat,
            $lng,
            $lng,  // Note: point() function takes longitude first
            $lat
        ]);
        
        $duplicate_check = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($duplicate_check['count'] > 0) {
            throw new Exception('An address already exists at or very close to this location');
        }

        // Handle default address logic
        $is_default = isset($data['is_default']) && $data['is_default'] === true;
        
        if ($is_default) {
            // Remove default status from other addresses
            $update_stmt = $db->prepare("
                UPDATE user_addresses
                SET is_default = 0
                WHERE user_id = ?
            ");
            $update_stmt->execute([$_SESSION['user_id']]);
        } else {
            // Check if this is the first address (make it default if so)
            $count_stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM user_addresses 
                WHERE user_id = ?
            ");
            $count_stmt->execute([$_SESSION['user_id']]);
            $is_default = ($count_stmt->fetch(PDO::FETCH_ASSOC)['count'] === 0);
        }

        // Limit the number of addresses per user (e.g., maximum 5)
        $count_stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM user_addresses 
            WHERE user_id = ?
        ");
        $count_stmt->execute([$_SESSION['user_id']]);
        if ($count_stmt->fetch(PDO::FETCH_ASSOC)['count'] >= 5) {
            throw new Exception('Maximum number of saved addresses reached (5)');
        }

        // Insert new address
        $stmt = $db->prepare("
            INSERT INTO user_addresses (
                user_id,
                address_type,
                address_line,
                latitude,
                longitude,
                is_default,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        $result = $stmt->execute([
            $_SESSION['user_id'],
            $address_type,
            $address,
            $lat,
            $lng,
            $is_default ? 1 : 0
        ]);

        if (!$result) {
            throw new Exception('Failed to save address');
        }

        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Address saved successfully',
            'data' => [
                'address_id' => $db->lastInsertId(),
                'is_default' => $is_default
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error saving address: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}