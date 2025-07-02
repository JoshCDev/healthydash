<?php
// Session already started by api/index.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Ensure user is logged in
requireAuth();

// Debug: Log session info
error_log("DEBUG: Order history accessed by user_id: " . ($_SESSION['user_id'] ?? 'NOT_SET'));
error_log("DEBUG: Session data: " . print_r($_SESSION, true));

function getOrderHistory($userId) {
    try {
        $db = Database::getInstance();
        
        // Debug: Check if user has any orders
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $orderCount = $checkStmt->fetch(PDO::FETCH_ASSOC);
        error_log("DEBUG: User $userId has {$orderCount['count']} orders");
        
        // Debug: Check if menu_items table has data
        $menuStmt = $db->prepare("SELECT COUNT(*) as count FROM menu_items");
        $menuStmt->execute();
        $menuCount = $menuStmt->fetch(PDO::FETCH_ASSOC);
        error_log("DEBUG: Menu items table has {$menuCount['count']} items");
        
        $stmt = $db->prepare("
            SELECT 
                o.order_id,
                o.total_amount,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.created_at,
                oi.item_id,
                oi.quantity,
                oi.unit_price,
                oi.notes,
                mi.name as item_name,
                mi.image_url
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN menu_items mi ON oi.item_id = mi.item_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("DEBUG: Query returned " . count($results) . " results for user $userId");
        
        return $results;
    } catch (Exception $e) {
        error_log("Error fetching order history: " . $e->getMessage());
        return [];
    }
}

// Get user's orders
$orders = getOrderHistory($_SESSION['user_id']);

// Group orders by order_id
$groupedOrders = [];
foreach ($orders as $order) {
    if (!isset($groupedOrders[$order['order_id']])) {
        $groupedOrders[$order['order_id']] = [
            'order_id' => $order['order_id'],
            'total_amount' => $order['total_amount'],
            'payment_method' => $order['payment_method'],
            'payment_status' => $order['payment_status'],
            'order_status' => $order['order_status'],
            'created_at' => $order['created_at'],
            'items' => []
        ];
    }
    $groupedOrders[$order['order_id']]['items'][] = [
        'name' => $order['item_name'],
        'quantity' => $order['quantity'],
        'unit_price' => $order['unit_price'],
        'notes' => $order['notes'],
        'image_url' => $order['image_url']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/font/stylesheet.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Mona Sans";
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
}

/* Ensure smooth scrolling and proper overflow handling */
html {
    scroll-behavior: smooth;
    overflow-x: hidden;
    height: 100%;
}

body {
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    min-height: 100%;
    position: relative;
}

button, a {
    -webkit-tap-highlight-color: transparent;
    -webkit-user-select: none;
    user-select: none;
}

.container {
    max-width: 480px;
    margin: 0 auto;
    min-height: 100vh;
    background-color: rgb(252, 248, 245);
    padding-bottom: 40px;
}

/* Responsive container */
@media (min-width: 768px) {
    .container {
        max-width: 800px;
        padding-bottom: 40px;
    }
}

@media (min-width: 1024px) {
    .container {
        max-width: 1200px;
        padding-bottom: 40px;
    }
}

@media (min-width: 1440px) {
    .container {
        max-width: 1400px;
        padding-bottom: 40px;
    }
}

/* Specific fix for tablet scroll issues */
@media (min-width: 768px) and (max-width: 1023px) {
    body {
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    
    .container {
        min-height: 100dvh; /* Use dynamic viewport height */
        padding-bottom: max(40px, env(safe-area-inset-bottom, 0px));
    }
    
    .main-content {
        padding-bottom: 80px; /* Extra space specifically for tablet content */
    }
}

/* Header glass morphism */
header {
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.32);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(4.8px);
    -webkit-backdrop-filter: blur(4.8px);
    border-bottom: 1px solid #e5e7eb;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 1rem;
    z-index: 10;
}

@media (min-width: 768px) {
    header {
        border-radius: 0 0 12px 12px;
        padding: 12px 24px;
    }
}

h3 {
    font-family: "Source Serif";
    font-size: 24px;
    font-weight: 500;
}

/* Card Enhancements */
.card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform .3s ease, box-shadow .3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

/* Product list responsif */
.product {
    display: flex;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

/* Product image & details */
.product-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    flex: 1;
}

/* Larger image on wider screens */
@media (min-width: 528px) {
    .product-image {
        width: 96px;
        height: 96px;
    }
}

.product:last-child {
    border-bottom: none;
}

.product-title {
    font-weight: 500;
}

.main-content {
    padding: 16px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .main-content {
        padding: 24px;
        margin-bottom: 20px;
    }
}

@media (min-width: 1024px) {
    .main-content {
        padding: 32px;
        margin-bottom: 20px;
    }
}

.empty-cart-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 3rem 2rem;
    gap: 1.5rem;
    min-height: calc(100vh - 200px);
}

.empty-image {
    width: 120px;
    height: 120px;
    object-fit: contain;
    opacity: 0.6;
    filter: grayscale(50%);
}

.empty-text {
    color: #6b7280;
    font-size: 18px;
    margin-bottom: 8px;
}

.empty-subtext {
    color: #9ca3af;
    font-size: 14px;
}

.order-now-btn {
    margin-top: 1rem;
    padding: 12px 24px;
    background-color: #567733;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.order-now-btn:hover {
    background-color: #456028;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(86, 119, 51, 0.2);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.order-date {
    font-size: 14px;
    color: #666;
}

.order-status {
    font-size: 14px;
    font-weight: 500;
    color: #567733;
}

.order-total {
    font-weight: 500;
    color: #333;
}

.quantity {
    font-size: 14px;
    color: #666;
    margin: 4px 0;
}

.notes {
    font-size: 14px;
    color: #666;
    font-style: italic;
    margin-top: 4px;
}
    </style>
</head>
<body>
    <div class="container">
    <header>
        <button class="back-btn" onclick="window.location.href='settings.php'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
        </button>
        <h3>Order History</h3>
    </header>

    <div class="empty-cart-message" style="display: <?php echo empty($groupedOrders) ? 'flex' : 'none'; ?>">
        <img src="/assets/images/empty-box.png" alt="Empty history" class="empty-image">
        <p class="empty-text">No orders yet</p>
        <p class="empty-subtext">Looks like you haven't made any orders.<br>Start exploring our delicious menu!</p>
        <a href="menu.php" class="order-now-btn">Order Now</a>
    </div>

    <main class="main-content" style="display: <?php echo !empty($groupedOrders) ? 'block' : 'none'; ?>">
        <?php foreach ($groupedOrders as $order): ?>
            <div class="card">
                <div class="order-header">
                    <div class="order-info">
                        <p class="order-date"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                        <p class="order-status"><?php echo ucfirst($order['order_status']); ?></p>
                    </div>
                    <p class="order-total">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                </div>
                
                <?php foreach ($order['items'] as $item): ?>
                    <div class="product">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="product-details">
                            <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="quantity">Qty: <?php echo $item['quantity']; ?></p>
                            <p class="price">Rp <?php echo number_format($item['unit_price'], 0, ',', '.'); ?></p>
                            <?php if ($item['notes']): ?>
                                <p class="notes">Notes: <?php echo htmlspecialchars($item['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.querySelector('.main-content');
            const emptyMessage = document.querySelector('.empty-cart-message');
            
            // Show/hide content based on orders existence
            if (mainContent.children.length === 0) {
                mainContent.style.display = 'none';
                emptyMessage.style.display = 'flex';
            } else {
                mainContent.style.display = 'block';
                emptyMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>