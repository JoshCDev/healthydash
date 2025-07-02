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
    <title>Your Cart</title>
    <link rel="stylesheet" href="/assets/font/stylesheet.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'mona sans';
        }

        body {
            background-color: #fff;
            color: #333;
        }

        .title {
            font-family: 'Source Serif';
            font-weight: 500;
            font-size: 24px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            background-color: rgb(252, 248, 245);
        }

        /* Header */
        header {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #fff;
            padding: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        /* Main Content */
        main {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            transition: opacity 0.3s ease;
        }

        /* Cart Item */
        .cart-item {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            position: relative;
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: transform 0.3s;
            border-radius: 16px;
        }

        .remove-btn {
            position: absolute;
            right: 1rem;
            top: 1rem;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .item-content {
            display: flex;
            gap: 1rem;
        }

        .item-image {
            width: 64px;
            height: 64px;
            background: #eee;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex: 1;
        }

        .price {
            color: #666;
            font-size: 0.9rem;
            margin: 0.25rem 0 0.5rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 24px;
            height: 24px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            margin-top: 1rem;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: none;
            min-height: 80px;
        }

        /* Sections */
        .section {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* Select */
        .select-wrapper {
            position: relative;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            appearance: none;
            background: #fff;
        }

        .select-wrapper::after {
            content: "▼";
            font-size: 0.8rem;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Payment Options */
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
        }

        .payment-option:has(input:checked) {
            border-color: #000;
        }

        /* Continue Button */
        .continue-btn {
            background: #567733;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 1rem;
            width: 100%;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .continue-btn:hover {
            opacity: 0.9;
        }

        /* Empty Cart Message */
        .empty-cart-message {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 1rem;
            padding: 2rem;
            height: calc(100vh - 60px);
        }

        .empty-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            opacity: 0.8;
        }

        /* Modal */
        .modal {
            display: none;
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

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 320px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .cancel-btn {
            background: #f3f4f6;
        }

        .confirm-btn {
            background: #567733;
            color: white;
        }

        #new-address-input {
            margin-top: 12px;
        }

        #new-address-input input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <button class="back-btn" onclick="window.location.href='menu.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
                </svg>
            </button>
            <h3 class="title">Your Cart</h3>
        </header>

        <div class="empty-cart-message">
            <img src="/assets/images/empty-box.png" alt="Empty cart" class="empty-image">
            <p>You haven't added something to cart</p>
        </div>

        <main>
            <div class="section total-section">
                <h3>Total Price</h3>
                <p id="total-price">Rp 0</p>
            </div>

            <div class="section">
                <h3>Delivery Address</h3>
                <div class="select-wrapper">
                    <select id="address-select"></select>
                </div>
                <div id="new-address-input" style="display: none;">
                    <input type="text" id="new-address" placeholder="Enter your address">
                    <input type="hidden" id="address-lat">
                    <input type="hidden" id="address-lng">
                </div>
            </div>

            <div class="section">
                <h3>Payment method</h3>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment" value="gopay" checked>
                        <span>
                            <img height="32" src="https://imgop.itemku.com/?url=https%3A%2F%2Fitemku-assets.s3-ap-southeast-1.amazonaws.com%2Flogo%2Fpayment%2Fgopay.png&w=375&q=75" alt="Gopay">
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="shopeepay">
                        <span>
                            <img height="32" src="https://imgop.itemku.com/?url=https%3A%2F%2Fitemku-assets.s3-ap-southeast-1.amazonaws.com%2Flogo%2Fpayment%2Fshopeepay.png&w=375&q=75" alt="ShopeePay">
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="ovo">
                        <span>
                            <img height="32" src="https://imgop.itemku.com/?url=https%3A%2F%2Fitemku-assets.s3.ap-southeast-1.amazonaws.com%2Flogo%2Fpayment%2Fovo-purple-logogram.png&w=375&q=75" alt="OVO">
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="dana">
                        <span>
                            <img height="32" src="https://imgop.itemku.com/?url=https%3A%2F%2Fitemku-assets.s3-ap-southeast-1.amazonaws.com%2Flogo%2Fpayment%2Fdana.png&w=375&q=75" alt="DANA">
                        </span>
                    </label>
                </div>
            </div>

            <button class="continue-btn">Continue</button>
        </main>
    </div>

    <div class="modal" id="confirmationModal">
        <div class="modal-content">
            <h3>Confirm Order</h3>
            <p>Are you sure you want to place this order?</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-btn" onclick="document.getElementById('confirmationModal').classList.remove('active')">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="confirmOrder()">Confirm Order</button>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars(GOOGLE_MAPS_API_KEY); ?>&libraries=places"></script>
<script>
// Cart Management Functions
function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const mainContent = document.querySelector('main');
    const emptyMessage = document.querySelector('.empty-cart-message');
    const totalSection = document.querySelector('.total-section');
    
    // Handle empty cart
    if (cart.length === 0) {
        mainContent.style.display = 'none';
        emptyMessage.style.display = 'flex';
        return;
    }

    mainContent.style.display = 'flex';
    emptyMessage.style.display = 'none';

    // Remove existing cart items
    const existingItems = document.querySelectorAll('.cart-item');
    existingItems.forEach(item => item.remove());

    // Add cart items
    cart.forEach(item => {
        const itemElement = createCartItemElement(item);
        mainContent.insertBefore(itemElement, totalSection);
    });

    updateTotalPrice();
}

function createCartItemElement(item) {
    const element = document.createElement('div');
    element.className = 'cart-item';
    element.dataset.itemId = item.id;
    
    // Parse the price correctly
    const price = parseInt(item.price.replace(/\./g, ''));
    
    element.innerHTML = `
        <button class="remove-btn" onclick="removeItem('${item.id}')">×</button>
        <div class="item-content">
            <div class="item-image">
                <img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="item-details">
                <h3>${item.name}</h3>
                <p class="price">Rp ${price.toLocaleString()}</p>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
                    <span id="quantity-${item.id}">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
                </div>
            </div>
        </div>
        <textarea placeholder="Add notes (optional)" id="notes-${item.id}">${item.notes || ''}</textarea>
    `;
    
    return element;
}


function updateQuantity(itemId, change) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const itemIndex = cart.findIndex(item => item.id === itemId);
    
    if (itemIndex > -1) {
        const newQuantity = cart[itemIndex].quantity + change;
        if (newQuantity > 0) {
            cart[itemIndex].quantity = newQuantity;
            document.getElementById(`quantity-${itemId}`).textContent = newQuantity;
        } else {
            cart = cart.filter(item => item.id !== itemId);
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            itemElement.remove();
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateTotalPrice();
        
        if (cart.length === 0) {
            document.querySelector('main').style.display = 'none';
            document.querySelector('.empty-cart-message').style.display = 'flex';
        }
    }
}

function removeItem(itemId) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart = cart.filter(item => item.id !== itemId);
    localStorage.setItem('cart', JSON.stringify(cart));
    
    const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
    itemElement.remove();
    updateTotalPrice();
    
    if (cart.length === 0) {
        document.querySelector('main').style.display = 'none';
        document.querySelector('.empty-cart-message').style.display = 'flex';
    }
}

function updateTotalPrice() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const total = cart.reduce((sum, item) => {
        // Remove non-numeric characters and convert to number
        const price = parseInt(item.price.replace(/[^\d]/g, ''));
        return sum + (price * item.quantity);
    }, 0);
    
    document.getElementById('total-price').textContent = `Rp ${total.toLocaleString()}`;
}

// Address Management
function loadAddresses() {
    fetch('/get-addresses.php')
        .then(response => response.json())
        .then(addresses => {
            const select = document.getElementById('address-select');
            select.innerHTML = '<option value="" disabled selected>Select delivery address</option>';
            
            addresses.forEach(address => {
                const option = document.createElement('option');
                option.value = address.address_id;
                option.textContent = `${address.address_type}: ${address.address_line}`;
                select.appendChild(option);
            });
            
            const newAddressOption = document.createElement('option');
            newAddressOption.value = 'new';
            newAddressOption.textContent = '+ Add new address';
            select.appendChild(newAddressOption);
        })
        .catch(error => {
            console.error('Error loading addresses:', error);
            // Add fallback for when address loading fails
            const select = document.getElementById('address-select');
            select.innerHTML = '<option value="new">+ Add new address</option>';
        });
}

function handleAddressSelection() {
    const select = document.getElementById('address-select');
    const newAddressInput = document.getElementById('new-address-input');
    
    if (select.value === 'new') {
        newAddressInput.style.display = 'block';
        initializeAddressAutocomplete();
    } else {
        newAddressInput.style.display = 'none';
    }
}

function initializeAddressAutocomplete() {
    const input = document.getElementById('new-address');
    const autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: 'id' }
    });
    
    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            document.getElementById('address-lat').value = place.geometry.location.lat();
            document.getElementById('address-lng').value = place.geometry.location.lng();
        }
    });
}

function showConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const addressSelect = document.getElementById('address-select');
    const paymentMethod = document.querySelector('input[name="payment"]:checked');
    
    if (cart.length === 0) {
        alert('Your cart is empty');
        return;
    }
    
    if (!addressSelect.value || addressSelect.value === '') {
        alert('Please select a delivery address');
        return;
    }
    
    if (!paymentMethod) {
        alert('Please select a payment method');
        return;
    }
    
    // Save notes before showing modal
    cart.forEach(item => {
        const notesElement = document.getElementById(`notes-${item.id}`);
        if (notesElement) {
            item.notes = notesElement.value;
        }
    });
    localStorage.setItem('cart', JSON.stringify(cart));
    
    modal.classList.add('active');
}

function confirmOrder() {
    const addressSelect = document.getElementById('address-select');
    const paymentMethod = document.querySelector('input[name="payment"]:checked');
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    const orderData = {
        address_id: addressSelect.value === 'new' ? null : addressSelect.value,
        new_address: addressSelect.value === 'new' ? {
            address: document.getElementById('new-address').value,
            lat: document.getElementById('address-lat').value,
            lng: document.getElementById('address-lng').value
        } : null,
        payment_method: paymentMethod.value,
        items: cart.map(item => ({
            ...item,
            price: item.price.replace(/[^\d]/g, '') // Clean price format
        })),
        total_amount: cart.reduce((sum, item) => {
            const price = parseInt(item.price.replace(/[^\d]/g, ''));
            return sum + (price * item.quantity);
        }, 0)
    };

    console.log('Sending order data:', orderData); // Debug log

    // Show loading state
    const confirmBtn = document.querySelector('.confirm-btn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Processing...';
    confirmBtn.disabled = true;
    
    fetch('/place-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data); // Debug log
        if (data.success) {
            localStorage.removeItem('cart');
            window.location.href = 'success.php';
        } else {
            alert(data.error || 'Failed to place order');
            confirmBtn.textContent = originalText;
            confirmBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while placing your order: ' + error.message);
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    loadAddresses();
    
    // Address selection handling
    document.getElementById('address-select').addEventListener('change', handleAddressSelection);
    
    // Continue button handling
    document.querySelector('.continue-btn').addEventListener('click', showConfirmationModal);
});
</script>
</body>
</html>