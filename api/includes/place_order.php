<?php
// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

try {
    // Get and validate JSON data
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }

    $data = json_decode($json, true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Debug - log received data
    error_log("Received order data: " . print_r($data, true));

    $db = Database::getInstance();
    $db->beginTransaction();

    // Handle address
    $address_id = $data['address_id'];
    if ($data['new_address']) {
        try {
            $stmt = $db->prepare("
                INSERT INTO user_addresses (
                    user_id, 
                    address_type, 
                    address_line, 
                    latitude, 
                    longitude, 
                    created_at, 
                    updated_at
                ) VALUES (?, 'Other', ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $data['new_address']['address'],
                $data['new_address']['lat'],
                $data['new_address']['lng']
            ]);
            
            $address_id = $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Failed to save new address: ' . $e->getMessage());
        }
    }

    // Validate total amount
    if (!isset($data['total_amount']) || !is_numeric($data['total_amount'])) {
        throw new Exception('Invalid total amount');
    }

    // Create order
    try {
        $stmt = $db->prepare("
            INSERT INTO orders (
                user_id,
                address_id,
                total_amount,
                payment_method,
                payment_status,
                order_status,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, ?, ?, 'paid', 'delivered', NOW(), NOW()
            )
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $address_id,
            $data['total_amount'],
            $data['payment_method']
        ]);

        $order_id = $db->lastInsertId();
    } catch (Exception $e) {
        throw new Exception('Failed to create order: ' . $e->getMessage());
    }

    // Insert order items
    if (!isset($data['items']) || !is_array($data['items'])) {
        throw new Exception('No items in order');
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO order_items (
                order_id,
                item_id,
                quantity,
                unit_price,
                notes
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($data['items'] as $item) {
            $unit_price = (float) preg_replace('/[^\d.]/', '', $item['price']);
            
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $unit_price,
                $item['notes'] ?? null
            ]);
        }
    } catch (Exception $e) {
        throw new Exception('Failed to save order items: ' . $e->getMessage());
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log("Error placing order: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage() // Return actual error message for debugging
    ]);
}