<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth_check.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
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

        /* Hilangkan highlight biru pada tombol & link */
        button {
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        a {
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color:rgb(252, 248, 245);
            color: #333;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: rgb(252, 248, 245);
            padding-bottom: 64px; /* Ruang untuk bottom nav */
        }

        /* Responsive container */
        @media (min-width: 768px) {
            .container {
                max-width: 800px;
            }
        }

        @media (min-width: 1024px) {
            .container {
                max-width: 1200px;
            }
        }

        @media (min-width: 1440px) {
            .container {
                max-width: 1400px;
            }
        }

        /* Header glass-morphism agar konsisten */
        header {
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.32);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4.8px);
            -webkit-backdrop-filter: blur(4.8px);
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 16px;
            z-index: 10;
        }

        @media (min-width: 768px) {
            header {
                border-radius: 0 0 12px 12px;
            }
        }

        h3{
            font-family: 'source sans';
            font-size: 24px;
            font-weight: 500;
        }

        header h1 {
            font-size: 0.75rem;
            font-weight: normal;
            color: #666;
            letter-spacing: 0.05em;
        }

        /* Main Content */
        main {
            flex: 1;
        }

        /* Settings navigation list */
        .settings-nav {
            display: flex;
            flex-direction: column;
            gap: 16px; /* ruang antar card */
            padding: 24px 16px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            text-decoration: none;
            color: inherit;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background-color: #f9fafb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .logout-btn {
            color: #dc2626;
            border: 1px solid #fee2e2;
            background: #fff5f5;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        @media (min-width: 768px) {
            .button {
                padding: 12px 16px;
                font-size: 13px;
                gap: 8px;
            }
        }

        .bottom-nav {
            max-width: 480px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            /* From https://css.glass */
background: rgba(255, 255, 255, 0.32);
border-radius: 16px;
box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
backdrop-filter: blur(4.8px);
-webkit-backdrop-filter: blur(4.8px);
border: 1px solid rgba(255, 255, 255, 0.22);
            border-top: 1px solid #e5e7eb;
            padding: 8px;
            margin: 0 24px;
            margin-block-end: 16px;
            border-radius: 64px;
            display: flex;
            justify-content: stretch;
        }

        /* Responsive bottom nav */
        @media (min-width: 528px) {
            .bottom-nav {
                margin: 0 auto;
                margin-block-end: 16px;
            }
        }

        @media (min-width: 768px) {
            .bottom-nav {
                max-width: 800px;
            }
        }

        @media (min-width: 1024px) {
            .bottom-nav {
                max-width: 1200px;
            }
        }

        @media (min-width: 1440px) {
            .bottom-nav {
                max-width: 1400px;
            }
        }

        .nav-button {
            border: none;
            background: none;
            padding: 16px 24px;
            cursor: pointer;
            border-radius: 56px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin: 0 4px;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        .nav-button:hover {
            background-color: #f3f4f6;
        }

        .nav-button:active {
            background-color: #e5e7eb;
            transform: scale(0.98);
        }

        .nav-link.active {
            color: #000;
        }

        .icon {
            width: 24px;
            height: 24px;
        }

        /* Modal improvements */
        .modal-content {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            padding: 32px 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .modal-content h3 {
            font-family: "Source Serif";
            font-size: 26px;
            margin-bottom: 12px;
        }

        .modal-content p {
            color: #4b5563;
            margin-bottom: 32px;
            font-size: 16px;
        }

        .modal-buttons {
            display: flex;
            flex-direction: row;
            gap: 16px;
        }

        .modal-btn {
            flex: 1;
            padding: 14px 0;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cancel-btn {
            background: #f9fafb;
            color: #111;
            border: 1px solid #e5e7eb;
        }

        .cancel-btn:hover {
            background: #f3f4f6;
        }

        .confirm-btn {
            background: #567733;
            color: #ffffff;
            border: none;
        }

        .confirm-btn:hover {
            background: #456028;
            transform: translateY(-1px);
        }

        /* Add active class for showing the modal */
        .modal.active {
            display: flex;
        }

        /* Overlay modal */
        .modal {
            display: none; /* hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <h3>Settings</h3>
        </header>

        <!-- Main Content -->
        <main>
            <nav class="settings-nav">
                <a href="order-history.php" class="nav-item">Order History</a>
                <button class="nav-item logout-btn">Log Out</button>
            </nav>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <button class="nav-button" aria-label="Store" onclick="window.location.href='menu.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shop-window" viewBox="0 0 16 16">
                    <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h12V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5m2 .5a.5.5 0 0 1 .5.5V13h8V9.5a.5.5 0 0 1 1 0V13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5a.5.5 0 0 1 .5-.5"/>
                  </svg>
            </button>
            <button class="nav-button" aria-label="Settings" onclick="window.location.href='settings.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                  </svg>
            </button>
        </nav>
    </div>

    <div class="modal" id="logoutModal">
        <div class="modal-content">
            <h3>Sign Out</h3>
            <p>Are you sure you want to sign out?</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn">Cancel</button>
                <button class="modal-btn confirm-btn">Sign Out</button>
            </div>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    // Handle navigation item clicks
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            if (item.tagName === 'A') {
                // Regular navigation handled by href
                console.log('Navigating to:', item.getAttribute('href'));
            }
        });
    });

    // Handle bottom navigation clicks
    const bottomNavLinks = document.querySelectorAll('.bottom-nav .nav-link');
    bottomNavLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Remove active class from all links
            bottomNavLinks.forEach(l => l.classList.remove('active'));
            // Add active class to clicked link
            link.classList.add('active');
        });
    });

    // Logout Modal Handling
    const modal = document.getElementById('logoutModal');
    const logoutButton = document.querySelector('.logout-btn');
    const cancelButton = modal.querySelector('.cancel-btn');
    const confirmButton = modal.querySelector('.confirm-btn');

    function handleLogout() {
        confirmButton.textContent = 'Signing out...';
        confirmButton.disabled = true;
        window.location.href = '/logout.php';
    }

    // Show modal when logout button is clicked
    logoutButton.addEventListener('click', () => {
        modal.classList.add('active');
    });

    // Hide modal when cancel button is clicked
    cancelButton.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    // Handle logout confirmation
    confirmButton.addEventListener('click', handleLogout);

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    // Close modal with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
});
</script>
</body>
</html>