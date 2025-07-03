<?php
// Database checker and menu items populator
// This file should be DELETED after fixing database issues

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

try {
    $db = Database::getInstance();
    
    // Check menu_items table
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM menu_items");
    $stmt->execute();
    $menuCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check sample menu items
    $stmt = $db->prepare("SELECT item_id, name, price, image_url FROM menu_items LIMIT 5");
    $stmt->execute();
    $sampleMenuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check order_items table
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM order_items");
    $stmt->execute();
    $orderItemsCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check sample order items with their item_ids
    $stmt = $db->prepare("SELECT DISTINCT item_id FROM order_items LIMIT 10");
    $stmt->execute();
    $orderItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Check if order item_ids exist in menu_items
    $missingItems = [];
    foreach ($orderItemIds as $itemId) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM menu_items WHERE item_id = ?");
        $stmt->execute([$itemId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($exists['count'] == 0) {
            $missingItems[] = $itemId;
        }
    }
    
    $result = [
        'menu_items_count' => $menuCount['count'],
        'order_items_count' => $orderItemsCount['count'],
        'sample_menu_items' => $sampleMenuItems,
        'order_item_ids' => $orderItemIds,
        'missing_menu_items' => $missingItems,
        'issue_found' => !empty($missingItems) || $menuCount['count'] == 0
    ];
    
    // If menu_items is empty, populate with sample data
    if ($menuCount['count'] == 0) {
        $sampleMenuData = [
            ['name' => 'Nasi Goreng Spesial', 'description' => 'Nasi goreng dengan telur dan ayam', 'price' => 25000, 'image_url' => '/assets/images/nasi-goreng.jpg'],
            ['name' => 'Ayam Bakar', 'description' => 'Ayam bakar bumbu khas dengan lalapan', 'price' => 30000, 'image_url' => '/assets/images/ayam-bakar.jpg'],
            ['name' => 'Gado-Gado', 'description' => 'Salad sayuran segar dengan bumbu kacang', 'price' => 20000, 'image_url' => '/assets/images/gado-gado.jpg'],
            ['name' => 'Soto Ayam', 'description' => 'Sup ayam dengan kuah bening dan rempah', 'price' => 23000, 'image_url' => '/assets/images/soto-ayam.jpg'],
            ['name' => 'Es Teh Manis', 'description' => 'Teh manis dingin segar', 'price' => 8000, 'image_url' => '/assets/images/es-teh.jpg'],
            ['name' => 'Jus Jeruk', 'description' => 'Jus jeruk segar tanpa gula tambahan', 'price' => 12000, 'image_url' => '/assets/images/jus-jeruk.jpg'],
            ['name' => 'Rendang', 'description' => 'Daging sapi rendang bumbu Padang', 'price' => 35000, 'image_url' => '/assets/images/rendang.jpg'],
            ['name' => 'Pecel Lele', 'description' => 'Lele goreng dengan sambal dan lalapan', 'price' => 18000, 'image_url' => '/assets/images/pecel-lele.jpg']
        ];
        
        $insertStmt = $db->prepare("
            INSERT INTO menu_items (name, description, price, image_url, is_available, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $inserted = 0;
        foreach ($sampleMenuData as $item) {
            if ($insertStmt->execute([$item['name'], $item['description'], $item['price'], $item['image_url']])) {
                $inserted++;
            }
        }
        
        $result['populated_menu_items'] = $inserted;
        $result['message'] = "Populated $inserted menu items";
    }
    
    // If there are missing menu items for existing orders, create them
    if (!empty($missingItems)) {
        $insertStmt = $db->prepare("
            INSERT INTO menu_items (item_id, name, description, price, image_url, is_available, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $createdItems = 0;
        foreach ($missingItems as $itemId) {
            // Create a generic menu item for this ID
            if ($insertStmt->execute([
                $itemId, 
                "Food Item #$itemId", 
                'Menu item yang dipesan sebelumnya',
                20000, 
                '/assets/images/default-food.jpg'
            ])) {
                $createdItems++;
            }
        }
        
        $result['created_missing_items'] = $createdItems;
        $result['message'] = ($result['message'] ?? '') . " Created $createdItems missing menu items";
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} 