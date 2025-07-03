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
    
    // If menu_items is empty, populate with actual menu data from menu.php
    if ($menuCount['count'] == 0) {
        $sampleMenuData = [
            ['name' => 'Avocado Toast', 'description' => 'Nutrient-dense breakfast with healthy monounsaturated fats from avocados and complex carbohydrates from whole grain bread', 'price' => 25000, 'image_url' => 'https://www.giallozafferano.com/images/273-27388/Avocado-toast_1200x800.jpg'],
            ['name' => 'Healthy Chicken Sandwich', 'description' => 'Balanced meal combining lean protein from chicken breast, complex carbohydrates from whole wheat bread, and essential vitamins from fresh vegetables', 'price' => 23000, 'image_url' => 'https://www.eatingwell.com/thmb/lWAiwknQ9yapq6UuXAYrUdrcKbk=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Rotisserie-Chicken-Sandwich-202-2000-485b673fe411460e95b512fbf805a5d9.jpg'],
            ['name' => 'Scrambled Eggs with Vegetables', 'description' => 'Protein-rich breakfast with essential amino acids, vitamins A, D, E, and B-complex, and minerals including iron and selenium', 'price' => 18000, 'image_url' => 'https://zucchinizone.com/wp-content/uploads/2024/01/scrambled-eggs-with-veggies-closeup-500x500.jpg'],
            ['name' => 'Classic Tuna Salad', 'description' => 'High in lean protein from tuna and omega-3 fatty acids, balanced meal rich in protein, healthy fats, and fiber', 'price' => 20000, 'image_url' => 'https://thedefineddish.com/wp-content/uploads/2020/06/240201_classic-tuna-salad-20.jpg'],
            ['name' => 'Healthy Grilled Cheese with Tomato Soup', 'description' => 'Comforting classic made healthier with whole grain bread, reduced-fat cheese, and homemade tomato soup rich in lycopene', 'price' => 20000, 'image_url' => 'https://simply-delicious-food.com/wp-content/uploads/2019/08/Tomato-soup-with-grilled-cheese-5.jpg'],
            ['name' => 'Lean Beef Burger', 'description' => 'Healthier version using lean ground beef and whole grain bun, rich in protein, iron, and B vitamins', 'price' => 25000, 'image_url' => 'https://canadabeef.ca/wp-content/uploads/2015/05/Canadian-Beef-Best-Ever-Lean-Beef-Burgers.jpg'],
            ['name' => 'Loaded Baked Potato', 'description' => 'Complex carbohydrates, fiber, vitamin C, and potassium with healthy toppings like Greek yogurt and vegetables', 'price' => 15000, 'image_url' => 'https://cdn.apartmenttherapy.info/image/upload/f_jpg,q_auto:eco,c_fill,g_auto,w_1500,ar_1:1/k%2FPhoto%2FRecipe%20Ramp%20Up%2F2021-07-Loaded-Baked-Potato%2FLoaded_Baked_Potato2'],
            ['name' => 'Vegetable Stir-Fried Rice', 'description' => 'Fiber-rich meal using brown rice and plenty of vegetables, with egg providing protein and essential nutrients', 'price' => 20000, 'image_url' => 'https://www.dinneratthezoo.com/wp-content/uploads/2016/10/veggie-fried-rice-6.jpg'],
            ['name' => 'Fruit and Yogurt Bowl', 'description' => 'Protein-rich breakfast packed with probiotics, vitamins, and antioxidants from fresh fruits and Greek yogurt', 'price' => 22000, 'image_url' => 'https://www.modernhoney.com/wp-content/uploads/2016/10/IMG_1210edit-copycrop.jpg'],
            ['name' => 'Simple Pasta with Tomato Sauce', 'description' => 'Whole grain pasta with homemade tomato sauce providing lycopene and vitamins without added sugars', 'price' => 23000, 'image_url' => 'https://www.budgetbytes.com/wp-content/uploads/2016/07/Pasta-with-Butter-Tomato-Sauce-and-Toasted-Bread-Crumbs-forkful.jpg']
        ];
        
        $insertStmt = $db->prepare("
            INSERT IGNORE INTO menu_items (name, description, price, image_url, is_available, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $inserted = 0;
        foreach ($sampleMenuData as $item) {
            if ($insertStmt->execute([$item['name'], $item['description'], $item['price'], $item['image_url']])) {
                $inserted++;
            }
        }
        
        $result['populated_menu_items'] = $inserted;
        $result['message'] = "Populated $inserted menu items from menu.php data";
    }
    
    // If there are missing menu items for existing orders, create them
    if (!empty($missingItems)) {
        $insertStmt = $db->prepare("
            INSERT IGNORE INTO menu_items (item_id, name, description, price, image_url, is_available, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $createdItems = 0;
        $fallbackMenuData = [
            'name' => 'Special Menu Item',
            'description' => 'Previously ordered menu item',
            'price' => 20000,
            'image_url' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=500&h=500&fit=crop'
        ];
        
        foreach ($missingItems as $itemId) {
            // Create a menu item for this specific ID using fallback data
            if ($insertStmt->execute([
                $itemId, 
                $fallbackMenuData['name'] . " #$itemId", 
                $fallbackMenuData['description'],
                $fallbackMenuData['price'], 
                $fallbackMenuData['image_url']
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