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
    <title>Menu Dashboard</title>
    <link rel="stylesheet" href="/assets/font/stylesheet.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Mona Sans';
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }

        body {
            background-color:rgb(252, 248, 245);
        }

        /* Remove blue highlight on all buttons */
        button {
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        a {
            -webkit-tap-highlight-color: transparent;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background-color:rgb(252, 248, 245);
            position: relative;
            padding-bottom: 64px; /* Space for bottom nav */
        }

        /* Responsive container for different screen sizes */
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

        .delivery-text{
            padding: 8px;
            background: none;
            border: none;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }
        .header {
            position: sticky;
            top: 0;
            /* From https://css.glass */
background: rgba(255, 255, 255, 0.32);

box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
backdrop-filter: blur(4.8px);
-webkit-backdrop-filter: blur(4.8px);
border: 1px solid rgba(255, 255, 255, 0.22);
            border-bottom: 1px solid #e5e7eb;
            z-index: 10;
        }

        /* Responsive header */
        @media (min-width: 768px) {
            .header {
                border-radius: 0 0 12px 12px;
            }
        }

        .header-content {
            height: 62px;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Responsive header content */
        @media (min-width: 768px) {
            .header-content {
                padding: 0 24px;
                height: 68px;
            }
        }

        @media (min-width: 1024px) {
            .header-content {
                padding: 0 32px;
            }
        }

        .delivery-text {
            font-size: 14px;
        }

        .cart-button {
            border: none;
            background: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        .cart-button:hover {
            background-color: #f3f4f6;
            transform: scale(1.05);
        }

        .main-content {
            padding: 16px;
        }

        /* Responsive main content */
        @media (min-width: 768px) {
            .main-content {
                padding: 24px;
            }
        }

        @media (min-width: 1024px) {
            .main-content {
                padding: 32px;
            }
        }

        /* Menu Grid Layout */
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 24px;
            align-items: start;
        }

        /* Responsive grid columns */
        @media (min-width: 768px) {
            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
                grid-auto-rows: 1fr;
            }
        }

        @media (min-width: 1024px) {
            .menu-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 28px;
                grid-auto-rows: 1fr;
            }
        }

        @media (min-width: 1440px) {
            .menu-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 32px;
                grid-auto-rows: 1fr;
            }
        }

        .title {
            text-align: center;
            font-family: 'Source Serif';
            font-size: 32px;
            font-weight: 500;
            margin-bottom: 0;
        }

        .menu-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: fit-content;
            display: flex;
            flex-direction: column;
        }

        .menu-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .menu-image {
            width: 100%;
            aspect-ratio: 1;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }

        .menu-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .menu-card:hover .menu-image img {
            transform: scale(1.05);
        }

        .menu-content {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Responsive menu content */
        @media (min-width: 768px) {
            .menu-content {
                padding: 14px;
            }
        }

        .menu-title {
            font-weight: 500;
            margin-bottom: 4px;
            font-family: 'source serif';
            font-size: 24px;
            line-height: 1.3;
            min-height: 2.6em;
            display: flex;
            align-items: center;
        }

        /* Responsive menu title */
        @media (min-width: 768px) {
            .menu-title {
                font-size: 20px;
                min-height: 2.6em;
            }
        }

        @media (min-width: 1024px) {
            .menu-title {
                font-size: 22px;
                min-height: 2.6em;
            }
        }

        .menu-price {
            color: #6b7280;
            font-size: 14px;
            font-weight: 600;
        }

        .menu-actions {
            display: flex;
            gap: 8px;
            padding: 0 16px 16px;
            margin-top: auto;
        }

        /* Responsive menu actions */
        @media (min-width: 768px) {
            .menu-actions {
                padding: 0 14px 14px;
                gap: 6px;
            }
        }

        .button {
            flex: 1;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        .button-outline {
            background: white;
            border: 1px solid #e5e7eb;
        }

        .button-outline:hover {
            background: #f9fafb;
            border-color: #567733;
            color: #567733;
        }

        .button-primary {
            background: #567733;
            color: white;
            border: none;
        }

        .button-primary:hover {
            background: #456028;
            transform: translateY(-1px);
        }

        /* Responsive button sizing */
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

        /* Add modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 100;
            justify-content: center;
            align-items: center;
            padding: 16px;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            animation: slideUp 0.3s ease;
            overflow: hidden;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Modal header */
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-close {
            width: 36px;
            height: 36px;
            border: none;
            background: white;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .modal-close:hover {
            background: #f3f4f6;
            transform: scale(1.1);
        }

        .modal-title {
            font-family: 'Source Serif';
            font-size: 24px;
            font-weight: 500;
            margin: 0;
            flex: 1;
            padding-right: 16px;
        }

        .modal-recipe {
            line-height: 1.6;
            color: #374151;
        }

        .text-container {
            width: 100%;
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive modal adjustments */
        @media (max-width: 480px) {
            .modal {
                padding: 0;
                align-items: flex-end;
            }

            .modal-content {
                max-width: 100%;
                max-height: 85vh;
                border-radius: 20px 20px 0 0;
                animation: slideUpMobile 0.3s ease;
            }

            @keyframes slideUpMobile {
                from {
                    transform: translateY(100%);
                }
                to {
                    transform: translateY(0);
                }
            }

            .modal-header {
                padding: 16px 20px;
            }

            .modal-title {
                font-size: 20px;
            }

            .text-container {
                padding: 20px;
            }
        }

        @media (min-width: 768px) {
            .modal-content {
                max-width: 700px;
                max-height: 80vh;
            }

            .modal-header {
                padding: 24px 32px;
            }

            .text-container {
                padding: 32px;
            }
        }

        @media (min-width: 1024px) {
            .modal-content {
                max-width: 800px;
            }
        }

        /* Recipe content styling */
        .modal-recipe h2 {
            font-family: 'Source Serif';
            font-size: 20px;
            font-weight: 500;
            color: #111827;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .modal-recipe h3 {
            font-family: 'Source Serif';
            font-size: 18px;
            font-weight: 500;
            color: #374151;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .modal-recipe h4 {
            font-size: 16px;
            font-weight: 500;
            color: #4b5563;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .modal-recipe p {
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .modal-recipe ul, .modal-recipe ol {
            margin-bottom: 16px;
            padding-left: 24px;
        }

        .modal-recipe li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .modal-recipe ul ul, .modal-recipe ol ul {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .modal-recipe strong {
            font-weight: 600;
            color: #111827;
        }

        .modal-recipe hr {
            margin: 24px 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }

        /* Fix hidden class */
        .hidden {
            display: none !important;
        }

        /* Scrollbar styling for modal */
        .text-container::-webkit-scrollbar {
            width: 8px;
        }

        .text-container::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 4px;
        }

        .text-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .text-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Touch device optimization */
        @media (hover: none) and (pointer: coarse) {
            .modal-close {
                width: 44px;
                height: 44px;
                font-size: 24px;
            }
            
            .recipe-button {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <button class="delivery-text" onclick="window.location.href='address.php'">
                    <span class="">Set delivery address</span>
                    <span class="hidden">
                        Delivery address <br>
                        <span class="address"></span>
                    </span>
                </button>
                <!-- Update the cart button HTML -->
<button class="cart-button" aria-label="View cart" onclick="window.location.href='cart.php'">
    <div style="position: relative;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span id="cart-counter" style="position: absolute; top: -8px; right: -8px; background-color: #567733; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; display: none;">0</span>
    </div>
</button>
            </div>
        </header>

        <div class="modal" id="recipeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"></h2>
                    <button class="modal-close" aria-label="Close modal">&times;</button>
                </div>
                <div class="text-container">
                    <div class="modal-recipe"></div>
                </div>
            </div>
        </div>

        <main class="main-content">
            <h1 class="title">Our Menu</h1>

            

            <div class="menu-grid">
                <div class="menu-card" data-item-id="1">
                    <div class="menu-image">
                        <img src="https://www.giallozafferano.com/images/273-27388/Avocado-toast_1200x800.jpg" alt="Ratatouille">
                    </div>
                    <div class="menu-content">
                        <h3 class="menu-title">Avocado Toast</h3>
                        <p class="menu-price">Rp 25.000</p>
                    </div>
                    <div class="menu-actions">
                        <button class="button button-outline recipe-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                            Recipe
                        </button>
                        <button class="button button-primary">Add to cart</button>
                    </div>
                    <div class="menu-recipe hidden">
    <h2>Nutritional Overview:</h2>
    <p>
        This nutrient-dense breakfast provides healthy monounsaturated fats from avocados, complex carbohydrates from whole grain bread, 
        and complete proteins when topped with an egg. Rich in fiber, potassium, and vitamins E, K, and B-complex. 
        One serving provides approximately 350 calories, 14g protein, 45g carbohydrates, and 18g healthy fats.
    </p>
    <hr>
    <h3>Tools Required:</h3>
    <ul>
        <li>Toaster or toaster oven</li>
        <li>ShaRp chef's knife</li>
        <li>Cutting board</li>
        <li>Small bowl</li>
        <li>Fork for mashing</li>
        <li>Measuring spoons</li>
        <li>Citrus juicer (optional)</li>
    </ul>
<hr>
    <h3>Ingredients:</h3>
    <ul>
        <li>2 slices whole grain bread</li>
        <li>1 ripe medium avocado</li>
        <li>1 tablespoon fresh lemon or lime juice</li>
        <li>¼ teaspoon sea salt</li>
        <li>⅛ teaspoon fresh ground black pepper</li>
        <li>Optional toppings:
            <ul>
                <li>2 poached eggs</li>
                <li>Red pepper flakes</li>
                <li>Microgreens or sprouts</li>
                <li>Cherry tomatoes, halved</li>
                <li>Everything bagel seasoning</li>
            </ul>
        </li>
    </ul>
<hr>
    <h3>Procedure:</h3>
    <ol type="1">
        <li>Check avocado ripeness by gently pressing the skin - it should yield slightly under pressure but not be mushy.</li>
        <li>Toast the bread slices until golden brown and crispy, approximately 2-3 minutes. The bread should be sturdy enough to support the toppings.</li>
        <li>
            While bread is toasting, cut the avocado in half lengthwise and remove the pit:
            <ul>
                <li>Score around the middle of the avocado</li>
                <li>Twist the halves apart</li>
                <li>Carefully remove pit by tapping it with the knife blade and twisting</li>
            </ul>
        </li>
        <li>Scoop avocado flesh into a small bowl using a spoon, getting close to the skin without piercing it.</li>
        <li>
            Add lemon/lime juice, salt, and pepper to the avocado. Mash with a fork until desired consistency - chunky for more texture, smooth for a creamier result.
        </li>
        <li>
            Once toast has cooled slightly (about 1 minute), divide the mashed avocado mixture evenly between both slices, spreading to the edges.
        </li>
        <li>
            Add chosen toppings while the toast is still warm. If using eggs, ensure they're freshly poached with runny yolks.
        </li>
        <li>Serve immediately for optimal texture and flavor.</li>
    </ol>
<hr>
    <p>
        <strong>Storage:</strong> Best consumed immediately after preparation. If needed, mashed avocado mixture can be stored separately for up to 4 hours in an airtight container with plastic wrap pressed directly onto the surface to prevent browning.
    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="2">
                <div class="menu-image">
                    <img src="https://www.eatingwell.com/thmb/lWAiwknQ9yapq6UuXAYrUdrcKbk=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Rotisserie-Chicken-Sandwich-202-2000-485b673fe411460e95b512fbf805a5d9.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Healthy Chicken Sandwich</h3>
                    <p class="menu-price">Rp 23.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
    <p>
        A balanced meal combining lean protein from chicken breast, complex carbohydrates from whole wheat bread, and essential vitamins from fresh vegetables. 
        One serving provides approximately 400 calories, 35g protein, 45g carbohydrates, and 12g healthy fats. High in vitamin B6, selenium, and iron.
    </p>
<hr>
    <h3>Tools Required:</h3>
    <ul>
        <li>Frying pan or grill pan</li>
        <li>Cutting board</li>
        <li>Chef's knife</li>
        <li>Measuring spoons</li>
        <li>Kitchen tongs</li>
        <li>Meat thermometer (recommended)</li>
        <li>Toaster (optional)</li>
    </ul>
<hr>
    <h3>Ingredients:</h3>
    <ul>
        <li>2 slices whole wheat bread</li>
        <li>1 chicken breast (about 6 oz)</li>
        <li>2 leaves fresh lettuce</li>
        <li>2 slices tomato</li>
        <li>2-3 slices cucumber</li>
        <li>1 tablespoon olive oil</li>
        <li>1 teaspoon salt</li>
        <li>½ teaspoon black pepper</li>
        <li>Optional spreads:
            <ul>
                <li>Light mayonnaise</li>
                <li>Mustard</li>
                <li>Mashed avocado</li>
            </ul>
        </li>
    </ul>
<hr>
    <h3>Procedure:</h3>
    <ol type="1">
        <li>
            Prepare the chicken breast:
            <ul>
                <li>Pat dry with paper towels</li>
                <li>Season both sides with ½ teaspoon salt and ¼ teaspoon pepper</li>
                <li>If thick, butterfly or pound to even thickness (about ½ inch)</li>
            </ul>
        </li>
        <li>Heat olive oil in pan over medium-high heat until shimmering (about 1 minute).</li>
        <li>Place chicken in pan, cook for 5-6 minutes on first side until golden brown.</li>
        <li>Flip chicken, cook additional 5-6 minutes until internal temperature reaches 165°F (74°C).</li>
        <li>Remove chicken from heat, let rest for 5 minutes on cutting board.</li>
        <li>
            While chicken rests, prepare vegetables:
            <ul>
                <li>Wash and dry lettuce leaf</li>
                <li>Slice tomato into ¼-inch thick rounds</li>
                <li>Slice cucumber thinly</li>
            </ul>
        </li>
        <li>Optional: Toast bread slices until light golden brown.</li>
        <li>Slice rested chicken against the grain into ½-inch strips.</li>
        <li>
            Assemble sandwich:
            <ul>
                <li>Spread chosen condiments on bread</li>
                <li>Layer lettuce first (prevents bread from getting soggy)</li>
                <li>Add sliced chicken</li>
                <li>Top with tomato and cucumber</li>
                <li>Close sandwich and cut diagonally if desired</li>
            </ul>
        </li>
    </ol>
<hr>
    <p>
        <strong>Storage:</strong> Chicken can be cooked ahead and stored in refrigerator for up to 3 days. Assemble sandwich just before eating for best results.
    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="3">
                <div class="menu-image">
                    <img src="https://zucchinizone.com/wp-content/uploads/2024/01/scrambled-eggs-with-veggies-closeup-500x500.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Scrambled Eggs with Vegetables</h3>
                    <p class="menu-price">Rp 18.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
    <p>
        A protein-rich breakfast that provides essential amino acids, vitamins A, D, E, and B-complex, and minerals including iron and selenium. 
        Adding vegetables increases fiber and micronutrient content. 
        One serving provides approximately 300 calories, 20g protein, 8g carbohydrates, and 22g healthy fats.
    </p>
<hr>
    <h3>Tools Required:</h3>
    <ul>
        <li>Non-stick frying pan</li>
        <li>Whisk or fork</li>
        <li>Cutting board</li>
        <li>Chef's knife</li>
        <li>Medium bowl</li>
        <li>Measuring spoons</li>
        <li>Spatula</li>
    </ul>
<hr>
    <h3>Ingredients:</h3>
    <ul>
        <li>3 large eggs</li>
        <li>2 tablespoons milk</li>
        <li>1 tablespoon butter or olive oil</li>
        <li>½ cup mixed vegetables (choose from):
            <ul>
                <li>Bell peppers, diced</li>
                <li>Onions, diced</li>
                <li>Tomatoes, diced</li>
                <li>Spinach, chopped</li>
            </ul>
        </li>
        <li>Salt and pepper to taste</li>
        <li>Optional: shredded cheese</li>
    </ul>
<hr>
    <h3>Procedure:</h3>
    <ol type="1">
        <li>Dice chosen vegetables into small, uniform pieces (about ¼ inch).</li>
        <li>Crack eggs into medium bowl, add milk, and whisk until well combined and slightly frothy.</li>
        <li>Heat pan over medium heat, add butter or oil.</li>
        <li>Once butter is melted and bubbling (but not brown), add diced vegetables except tomatoes.</li>
        <li>Cook vegetables until softened (about 2-3 minutes).</li>
        <li>Pour in egg mixture, let sit for 30 seconds until edges begin to set.</li>
        <li>
            Using spatula, gently push eggs from edges to center of pan, tilting to allow uncooked egg to flow to edges.
        </li>
        <li>
            When eggs are mostly set but still look slightly wet (about 2 minutes), add tomatoes if using.
        </li>
        <li>
            Remove from heat while eggs are still slightly glossy - they will continue cooking from residual heat.
        </li>
        <li>Season with salt and pepper to taste, add cheese if desired.</li>
    </ol>
<hr>
    <p>
        <strong>Storage:</strong> Best served immediately. Can be refrigerated for up to 24 hours but texture may change.
    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="4">
                <div class="menu-image">
                    <img src="https://thedefineddish.com/wp-content/uploads/2020/06/240201_classic-tuna-salad-20.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Classic Tuna Salad</h3>
                    <p class="menu-price">Rp 20.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        High in lean protein from tuna and omega-3 fatty acids. When made with light mayonnaise and plenty of vegetables, provides a balanced meal rich in protein, 
                        healthy fats, and fiber. One serving contains approximately 250 calories, 28g protein, 12g carbohydrates, and 10g healthy fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Medium mixing bowl</li>
                        <li>Fork or spoon for mixing</li>
                        <li>Cutting board</li>
                        <li>Chef's knife</li>
                        <li>Measuring spoons</li>
                        <li>Can opener</li>
                        <li>Fine mesh strainer</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <ul>
                        <li>2 cans (5 oz each) chunk light tuna in water</li>
                        <li>⅓ cup light mayonnaise</li>
                        <li>¼ cup celery, finely diced</li>
                        <li>¼ cup red onion, finely diced</li>
                        <li>1 tablespoon fresh lemon juice</li>
                        <li>½ teaspoon black pepper</li>
                        <li>¼ teaspoon salt</li>
                        <li>Optional add-ins:
                            <ul>
                                <li>Diced pickles</li>
                                <li>Chopped fresh parsley</li>
                                <li>Diced apple</li>
                                <li>Dijon mustard</li>
                            </ul>
                        </li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>Drain tuna thoroughly in strainer, pressing gently to remove excess water.</li>
                        <li>In medium bowl, break up tuna chunks with fork into desired consistency.</li>
                        <li>Finely dice celery and onion into uniform pieces (about ⅛ inch).</li>
                        <li>Add mayonnaise, diced vegetables, lemon juice, salt, and pepper to tuna.</li>
                        <li>Mix gently but thoroughly until all ingredients are well combined.</li>
                        <li>Add any optional ingredients, adjusting mayonnaise if needed for desired consistency.</li>
                        <li>Taste and adjust seasoning as needed.</li>
                        <li>Cover and refrigerate for at least 30 minutes to allow flavors to meld.</li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Can be stored in airtight container in refrigerator for up to 3 days.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="5">
                <div class="menu-image">
                    <img src="https://simply-delicious-food.com/wp-content/uploads/2019/08/Tomato-soup-with-grilled-cheese-5.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Healthy Grilled Cheese with Tomato Soup</h3>
                    <p class="menu-price">Rp 20.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        A comforting classic made healthier by using whole grain bread, reduced-fat cheese, and homemade tomato soup rich in lycopene and vitamins. 
                        Complete meal provides approximately 450 calories, 20g protein, 55g carbohydrates, and 18g fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <h4>For Grilled Cheese:</h4>
                    <ul>
                        <li>Non-stick skillet</li>
                        <li>Spatula</li>
                        <li>Cutting board</li>
                        <li>Butter knife</li>
                    </ul>
                    <h4>For Tomato Soup:</h4>
                    <ul>
                        <li>Medium pot</li>
                        <li>Immersion blender or regular blender</li>
                        <li>Can opener</li>
                        <li>Wooden spoon</li>
                        <li>Measuring spoons</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <h4>For Grilled Cheese:</h4>
                    <ul>
                        <li>2 slices whole grain bread</li>
                        <li>2 slices reduced-fat cheddar cheese</li>
                        <li>1 tablespoon soft butter or olive oil</li>
                        <li>Optional: sliced tomato, herbs</li>
                    </ul>
                    <h4>For Tomato Soup:</h4>
                    <ul>
                        <li>1 can (14 oz) crushed tomatoes</li>
                        <li>½ cup low-sodium vegetable broth</li>
                        <li>¼ cup milk or cream</li>
                        <li>1 small onion, diced</li>
                        <li>1 tablespoon olive oil</li>
                        <li>1 clove garlic, minced</li>
                        <li>Salt and pepper to taste</li>
                        <li>Optional: fresh basil</li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <h4>For Tomato Soup:</h4>
                    <ol type="1">
                        <li>Heat olive oil in pot over medium heat.</li>
                        <li>Add diced onion, cook until translucent (about 5 minutes).</li>
                        <li>Add minced garlic, cook for 30 seconds until fragrant.</li>
                        <li>Add crushed tomatoes and broth, bring to simmer.</li>
                        <li>Reduce heat to low, simmer for 15 minutes.</li>
                        <li>Add milk or cream, blend until smooth.</li>
                        <li>Season with salt and pepper to taste.</li>
                    </ol>
                    <h4>For Grilled Cheese:</h4>
                    <ol type="1">
                        <li>Butter one side of each bread slice.</li>
                        <li>Heat skillet over medium heat.</li>
                        <li>Place one slice bread butter-side down in skillet.</li>
                        <li>Add cheese slices and optional ingredients.</li>
                        <li>Top with second slice bread, butter-side up.</li>
                        <li>Cook until golden brown (about 3-4 minutes).</li>
                        <li>Flip carefully, cook other side until cheese melts (2-3 minutes).</li>
                        <li>Cut diagonally and serve with hot soup.</li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Soup can be refrigerated for up to 3 days. Grilled cheese best served immediately.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="6">
                <div class="menu-image">
                    <img src="https://canadabeef.ca/wp-content/uploads/2015/05/Canadian-Beef-Best-Ever-Lean-Beef-Burgers.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Lean Beef Burger</h3>
                    <p class="menu-price">Rp 25.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        Using lean ground beef and whole grain bun creates a healthier version of this classic. Rich in protein, iron, and B vitamins. 
                        One serving provides approximately 400 calories, 30g protein, 35g carbohydrates, and 18g fats when prepared with recommended toppings.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Large bowl for mixing</li>
                        <li>Grill or skillet</li>
                        <li>Spatula</li>
                        <li>Measuring spoons</li>
                        <li>Meat thermometer</li>
                        <li>Cutting board</li>
                        <li>Chef's knife</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <h4>For Burger:</h4>
                    <ul>
                        <li>½ pound lean ground beef (90/10)</li>
                        <li>¼ teaspoon salt</li>
                        <li>¼ teaspoon black pepper</li>
                        <li>¼ teaspoon garlic powder</li>
                        <li>2 whole grain burger buns</li>
                    </ul>
                    <h4>For Toppings:</h4>
                    <ul>
                        <li>Lettuce leaves</li>
                        <li>Tomato slices</li>
                        <li>Red onion slices</li>
                        <li>Light mayonnaise or mustard</li>
                        <li>Optional: reduced-fat cheese slice</li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>In large bowl, gently combine ground beef with seasonings, being careful not to overmix.</li>
                        <li>Divide meat into two equal portions, form into patties:
                            <ul>
                                <li>Make them slightly larger than bun</li>
                                <li>Press center to create slight depression</li>
                                <li>Should be about ¾ inch thick</li>
                            </ul>
                        </li>
                        <li>Heat grill or skillet to medium-high heat.</li>
                        <li>Place patties on hot surface:
                            <ul>
                                <li>For medium doneness, cook 4-5 minutes per side</li>
                                <li>Internal temperature should reach 160°F (71°C)</li>
                            </ul>
                        </li>
                        <li>If adding cheese, place on burger during last minute of cooking.</li>
                        <li>While burgers cook, prepare toppings:
                            <ul>
                                <li>Wash and dry lettuce</li>
                                <li>Slice tomato and onion</li>
                                <li>Toast buns if desired</li>
                            </ul>
                        </li>
                        <li>Let burgers rest for 2-3 minutes after cooking.</li>
                        <li>Assemble burgers:
                            <ul>
                                <li>Spread condiments on buns</li>
                                <li>Place lettuce on bottom bun</li>
                                <li>Add burger patty</li>
                                <li>Top with tomato, onion</li>
                                <li>Close with top bun</li>
                            </ul>
                        </li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Cooked patties can be refrigerated for up to 3 days. Assemble just before eating.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="7">
                <div class="menu-image">
                    <img src="https://cdn.apartmenttherapy.info/image/upload/f_jpg,q_auto:eco,c_fill,g_auto,w_1500,ar_1:1/k%2FPhoto%2FRecipe%20Ramp%20Up%2F2021-07-Loaded-Baked-Potato%2FLoaded_Baked_Potato2" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Loaded Baked Potato</h3>
                    <p class="menu-price">Rp 15.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        When topped with healthy ingredients, a baked potato provides complex carbohydrates, fiber, vitamin C, and potassium. 
                        Using Greek yogurt instead of sour cream adds protein while reducing fat. 
                        One serving provides approximately 300 calories, 12g protein, 45g carbohydrates, and 10g fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Oven</li>
                        <li>Baking sheet</li>
                        <li>Fork</li>
                        <li>Knife</li>
                        <li>Cutting board</li>
                        <li>Measuring spoons</li>
                        <li>Small bowls for toppings</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <ul>
                        <li>1 large russet potato</li>
                        <li>1 teaspoon olive oil</li>
                        <li>¼ teaspoon salt</li>
                        <li>Healthy toppings (choose from):
                            <ul>
                                <li>Plain Greek yogurt</li>
                                <li>Reduced-fat shredded cheese</li>
                                <li>Diced tomatoes</li>
                                <li>Chopped green onions</li>
                                <li>Steamed broccoli</li>
                                <li>Black beans</li>
                                <li>Salsa</li>
                            </ul>
                        </li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>Preheat oven to 425°F (220°C).</li>
                        <li>Wash potato thoroughly, dry completely.</li>
                        <li>Pierce potato several times with fork on all sides.</li>
                        <li>Rub potato with olive oil and salt.</li>
                        <li>Place directly on oven rack or on baking sheet.</li>
                        <li>Bake for 45-60 minutes until tender when pierced.</li>
                        <li>While potato bakes, prepare toppings:
                            <ul>
                                <li>Dice tomatoes</li>
                                <li>Chop green onions</li>
                                <li>Steam broccoli if using</li>
                                <li>Heat beans if using</li>
                            </ul>
                        </li>
                        <li>When potato is done:
                            <ul>
                                <li>Cut in half lengthwise</li>
                                <li>Fluff inside with fork</li>
                                <li>Add toppings while hot</li>
                            </ul>
                        </li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Baked potato can be refrigerated for up to 3 days. Reheat in microwave or oven.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="8">
                <div class="menu-image">
                    <img src="https://www.dinneratthezoo.com/wp-content/uploads/2016/10/veggie-fried-rice-6.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Vegetable Stir-Fried Rice</h3>
                    <p class="menu-price">Rp 20.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        Using brown rice and plenty of vegetables creates a fiber-rich, nutritious meal. Adding egg provides protein and essential nutrients. 
                        One serving provides approximately 350 calories, 10g protein, 55g carbohydrates, and 12g healthy fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Large wok or deep skillet</li>
                        <li>Wooden spoon or spatula</li>
                        <li>Measuring cups and spoons</li>
                        <li>Cutting board</li>
                        <li>Chef's knife</li>
                        <li>Small bowl for egg</li>
                        <li>Rice cooker or pot</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <ul>
                        <li>2 cups cooked brown rice (preferably day-old)</li>
                        <li>2 eggs, beaten</li>
                        <li>2 tablespoons vegetable oil</li>
                        <li>2 cups mixed vegetables (choose from):
                            <ul>
                                <li>Carrots, diced</li>
                                <li>Peas</li>
                                <li>Corn</li>
                                <li>Bell peppers, diced</li>
                                <li>Onions, diced</li>
                            </ul>
                        </li>
                        <li>2 tablespoons low-sodium soy sauce</li>
                        <li>1 tablespoon sesame oil</li>
                        <li>2 cloves garlic, minced</li>
                        <li>1-inch piece ginger, minced</li>
                        <li>Salt and pepper to taste</li>
                        <li>Optional: green onions for garnish</li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>If using fresh rice, cook according to package instructions and let cool completely.</li>
                        <li>Prepare all vegetables before starting:
                            <ul>
                                <li>Dice into uniform small pieces</li>
                                <li>Have all ingredients ready by the stove</li>
                            </ul>
                        </li>
                        <li>Heat 1 tablespoon vegetable oil in wok over medium-high heat.</li>
                        <li>Add beaten eggs, scramble until just set, remove to plate.</li>
                        <li>Add remaining oil to wok, heat until shimmering.</li>
                        <li>Add garlic and ginger, stir-fry for 30 seconds until fragrant.</li>
                        <li>Add diced vegetables, stir-fry 3-4 minutes until crisp-tender.</li>
                        <li>Add rice, break up any clumps:
                            <ul>
                                <li>Stir-fry 3-4 minutes</li>
                                <li>Let rice sit occasionally to develop crispy bottom</li>
                            </ul>
                        </li>
                        <li>Add scrambled eggs back to wok.</li>
                        <li>Pour in soy sauce and sesame oil, mix well.</li>
                        <li>Season with salt and pepper to taste.</li>
                        <li>Garnish with green onions if desired.</li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Can be refrigerated in an airtight container for up to 3 days.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="9">
                <div class="menu-image">
                    <img src="https://www.modernhoney.com/wp-content/uploads/2016/10/IMG_1210edit-copycrop.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Fruit and Yogurt Bowl</h3>
                    <p class="menu-price">Rp 22.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        A protein-rich breakfast or snack packed with probiotics, vitamins, and antioxidants from fresh fruits. Using Greek yogurt increases protein content while keeping calories moderate. 
                        One serving provides approximately 250 calories, 15g protein, 40g carbohydrates, and 5g healthy fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Serving bowl</li>
                        <li>Measuring cups</li>
                        <li>Cutting board</li>
                        <li>Paring knife</li>
                        <li>Measuring spoons</li>
                        <li>Can opener (if using canned fruit)</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <ul>
                        <li>1 cup plain Greek yogurt</li>
                        <li>1 cup mixed fresh fruits (choose from):
                            <ul>
                                <li>Berries</li>
                                <li>Sliced banana</li>
                                <li>Diced apple</li>
                                <li>Sliced peaches</li>
                                <li>Mandarin oranges</li>
                            </ul>
                        </li>
                        <li>2 tablespoons honey or maple syrup</li>
                        <li>¼ cup granola</li>
                        <li>Optional toppings:
                            <ul>
                                <li>Chia seeds</li>
                                <li>Flax seeds</li>
                                <li>Chopped nuts</li>
                                <li>Cinnamon</li>
                            </ul>
                        </li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>Wash all fresh fruits thoroughly.</li>
                        <li>Cut larger fruits into bite-sized pieces.</li>
                        <li>In serving bowl, place Greek yogurt as base.</li>
                        <li>Drizzle with half of honey or maple syrup, stir gently.</li>
                        <li>Arrange prepared fruit on top of yogurt.</li>
                        <li>Sprinkle granola around edges.</li>
                        <li>Add optional toppings as desired.</li>
                        <li>Drizzle remaining honey or syrup over top.</li>
                    </ol>
                <hr>
                    <p>
                        <strong>Storage:</strong> Best assembled just before eating. Prepared fruit can be stored separately for up to 3 days.
                    </p>
                </div>
            </div>
            <div class="menu-card" data-item-id="10">
                <div class="menu-image">
                    <img src="https://www.budgetbytes.com/wp-content/uploads/2016/07/Pasta-with-Butter-Tomato-Sauce-and-Toasted-Bread-Crumbs-forkful.jpg" alt="Ratatouille">
                </div>
                <div class="menu-content">
                    <h3 class="menu-title">Simple Pasta with Tomato Sauce</h3>
                    <p class="menu-price">Rp 23.000</p>
                </div>
                <div class="menu-actions">
                    <button class="button button-outline recipe-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
</svg>
                        Recipe
                    </button>
                    <button class="button button-primary">Add to cart</button>
                </div>
                <div class="menu-recipe hidden">
                    <h2>Nutritional Overview:</h2>
                    <p>
                        Using whole grain pasta adds fiber and nutrients, while homemade tomato sauce provides lycopene and vitamins without added sugars. 
                        One serving provides approximately 380 calories, 12g protein, 65g carbohydrates, and 10g healthy fats.
                    </p>
                <hr>
                    <h3>Tools Required:</h3>
                    <ul>
                        <li>Large pot for pasta</li>
                        <li>Medium saucepan</li>
                        <li>Colander</li>
                        <li>Wooden spoon</li>
                        <li>Measuring cups and spoons</li>
                        <li>Can opener</li>
                        <li>Chef's knife</li>
                        <li>Cutting board</li>
                    </ul>
                <hr>
                    <h3>Ingredients:</h3>
                    <h4>For Pasta:</h4>
                    <ul>
                        <li>8 oz whole grain pasta</li>
                        <li>1 teaspoon salt (for pasta water)</li>
                    </ul>
                <hr>
                    <h4>For Sauce:</h4>
                    <ul>
                        <li>1 can (28 oz) crushed tomatoes</li>
                        <li>2 tablespoons olive oil</li>
                        <li>1 onion, finely diced</li>
                        <li>3 cloves garlic, minced</li>
                        <li>1 teaspoon dried basil</li>
                        <li>1 teaspoon dried oregano</li>
                        <li>½ teaspoon salt</li>
                        <li>¼ teaspoon black pepper</li>
                        <li>Optional:
                            <ul>
                                <li>Fresh basil</li>
                                <li>Grated Parmesan cheese</li>
                                <li>Red pepper flakes</li>
                            </ul>
                        </li>
                    </ul>
                <hr>
                    <h3>Procedure:</h3>
                    <ol type="1">
                        <li>Bring large pot of water to boil.</li>
                        <li>While water heats, start sauce:
                            <ul>
                                <li>Heat olive oil in saucepan over medium heat.</li>
                                <li>Add diced onion, cook until soft (5-6 minutes).</li>
                                <li>Add garlic, cook for 30 seconds.</li>
                                <li>Add crushed tomatoes and seasonings, simmer on low heat.</li>
                            </ul>
                        </li>
                        <li>When water boils, add salt and pasta.</li>
                        <li>Cook pasta according to package directions, stirring occasionally.</li>
                        <li>Reserve ½ cup pasta water before draining.</li>
                        <li>Drain pasta in colander.</li>
                        <li>Return pasta to pot, add desired amount of sauce:
                            <ul>
                                <li>Stir gently to combine.</li>
                                <li>Add pasta water if needed for consistency.</li>
                            </ul>
                        </li>
                        <li>Serve hot with optional toppings.</li>
                    </ol>
                <hr>
                    <p><strong>Storage:</strong> Sauce can be refrigerated for up to 5 days or frozen for 3 months. Cooked pasta best stored separately for up to 3 days.</p>
                </div>
            </div>
        </div>
        </main>

        <nav class="bottom-nav">
            <button class="nav-button" aria-label="Store">
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

    <script>
// Initialize cart in localStorage if it doesn't exist
if (!localStorage.getItem('cart')) {
    localStorage.setItem('cart', JSON.stringify([]));
}

// Update cart counter on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCounter();
});

function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const counter = document.getElementById('cart-counter');
    
    if (totalItems > 0) {
        counter.style.display = 'block';
        counter.textContent = totalItems;
    } else {
        counter.style.display = 'none';
    }
}

function addToCart(button) {
    const menuCard = button.closest('.menu-card');
    const itemData = {
        id: menuCard.dataset.itemId || Date.now(), // Fallback to timestamp if no ID
        name: menuCard.querySelector('.menu-title').textContent,
        price: menuCard.querySelector('.menu-price').textContent.replace('Rp ', ''),
        image: menuCard.querySelector('.menu-image img').src,
        quantity: 1
    };

    // Get current cart
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if item already exists
    const existingItemIndex = cart.findIndex(item => item.id === itemData.id);
    
    if (existingItemIndex > -1) {
        cart[existingItemIndex].quantity += 1;
    } else {
        cart.push(itemData);
    }

    // Save updated cart
    localStorage.setItem('cart', JSON.stringify(cart));

    // Show feedback
    button.style.backgroundColor = '#4CAF50';
    button.textContent = 'Added!';
    
    // Update counter
    updateCartCounter();

    // Reset button after 1 second
    setTimeout(() => {
        button.style.backgroundColor = '#567733';
        button.textContent = 'Add to cart';
    }, 1000);
}

// Recipe Modal functionality
const recipeModal = document.getElementById('recipeModal');
const modalTitle = recipeModal.querySelector('.modal-title');
const modalRecipe = recipeModal.querySelector('.modal-recipe');
const closeModalButtons = document.querySelectorAll('.modal-close');

document.querySelectorAll('.recipe-button').forEach(button => {
    button.addEventListener('click', function() {
        const menuCard = this.closest('.menu-card');
        const title = menuCard.querySelector('.menu-title').textContent;
        const recipe = menuCard.querySelector('.menu-recipe').innerHTML;
        
        modalTitle.textContent = title;
        modalRecipe.innerHTML = recipe;
        recipeModal.classList.add('show');
    });
});

// Close modal
closeModalButtons.forEach(button => {
    button.addEventListener('click', () => {
        recipeModal.classList.remove('show');
    });
});

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === recipeModal) {
        closeModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && recipeModal.classList.contains('show')) {
        closeModal();
    }
});

// Function to close modal with animation
function closeModal() {
    const modalContent = recipeModal.querySelector('.modal-content');
    modalContent.style.animation = 'slideDown 0.3s ease';
    recipeModal.style.animation = 'fadeOut 0.3s ease';
    
    setTimeout(() => {
        recipeModal.classList.remove('show');
        modalContent.style.animation = '';
        recipeModal.style.animation = '';
    }, 250);
}

// Add animation keyframes dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(20px);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @media (max-width: 480px) {
        @keyframes slideDown {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(100%);
            }
        }
    }
`;
document.head.appendChild(style);

// Prevent body scroll when modal is open
recipeModal.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Update close button event
closeModalButtons.forEach(button => {
    button.addEventListener('click', closeModal);
});

// Update all "Add to cart" buttons to use the new function
document.querySelectorAll('.menu-card').forEach(card => {
    const addButton = card.querySelector('.button-primary');
    addButton.onclick = () => addToCart(addButton);
});

// Cart Navigation
const cartButton = document.querySelector('.cart-button');
cartButton.addEventListener('click', function() {
    window.location.href = 'cart.php';
});

// Bottom Navigation
const navButtons = document.querySelectorAll('.nav-button');
navButtons.forEach(button => {
    button.addEventListener('click', function() {
        const page = this.getAttribute('aria-label').toLowerCase();
        if (page === 'store') {
            window.location.href = 'menu.php';
        } else if (page === 'settings') {
            window.location.href = 'settings.php';
        }
    });
});

// Format recipe content
function formatRecipeContent(recipe) {
    return `
        <div class="recipe-content">
            <!-- Nutritional Info -->
            <div class="recipe-section nutrition-box">
                <h3 class="section-title">Nutritional Overview</h3>
                <div class="nutrition-info">
                    <p>${recipe.nutritionalInfo.description}</p>
                    <div class="nutrition-facts">
                        <div class="nutrition-fact">
                            <span class="fact-label">Calories</span>
                            <span class="fact-value">${recipe.nutritionalInfo.calories}</span>
                        </div>
                        <div class="nutrition-fact">
                            <span class="fact-label">Protein</span>
                            <span class="fact-value">${recipe.nutritionalInfo.protein}g</span>
                        </div>
                        <div class="nutrition-fact">
                            <span class="fact-label">Carbs</span>
                            <span class="fact-value">${recipe.nutritionalInfo.carbohydrates}g</span>
                        </div>
                        <div class="nutrition-fact">
                            <span class="fact-label">Fats</span>
                            <span class="fact-value">${recipe.nutritionalInfo.fats}g</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tools -->
            <div class="recipe-section">
                <h3 class="section-title">Tools Required</h3>
                <ul class="recipe-list tools-list">
                    ${recipe.tools.map(tool => `<li>${tool}</li>`).join('')}
                </ul>
            </div>

            <!-- Ingredients -->
            <div class="recipe-section">
                <h3 class="section-title">Ingredients</h3>
                <div class="ingredients-container">
                    <div class="main-ingredients">
                        <h4 class="subsection-title">Main Ingredients</h4>
                        <ul class="recipe-list">
                            ${recipe.ingredients.main.map(ing => 
                                `<li>${ing.amount} ${ing.unit} ${ing.item}</li>`
                            ).join('')}
                        </ul>
                    </div>
                    ${recipe.ingredients.optional ? `
                        <div class="optional-ingredients">
                            <h4 class="subsection-title">Optional Toppings</h4>
                            <ul class="recipe-list">
                                ${recipe.ingredients.optional.map(ing => 
                                    `<li>${ing}</li>`
                                ).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>

            <!-- Steps -->
            <div class="recipe-section">
                <h3 class="section-title">Instructions</h3>
                <ol class="steps-list">
                    ${recipe.steps.map(step => `<li>${step}</li>`).join('')}
                </ol>
            </div>

            <!-- Storage -->
            <div class="recipe-section storage-note">
                <h3 class="section-title">Storage</h3>
                <div class="storage-info">
                    <p><strong>Method:</strong> ${recipe.storage.method}</p>
                    <p><strong>Duration:</strong> ${recipe.storage.duration}</p>
                    <p><strong>Note:</strong> ${recipe.storage.instructions}</p>
                </div>
            </div>
        </div>
    `;
}
</script>
</body>
</html>