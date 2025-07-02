<?php
require_once 'includes/config.php';
require_once 'includes/auth_check.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Location</title>
    <link rel="stylesheet" href="/assets/font/stylesheet.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars(GOOGLE_MAPS_API_KEY); ?>&libraries=places"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Mona Sans";
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color:rgb(252, 248, 245);
            margin: 0;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background-color:rgb(252, 248, 245);
            position: relative;
        }

        /* Responsive container */
        @media (min-width: 768px) {
            .container {
                max-width: 768px;
            }
        }

        @media (min-width: 1024px) {
            .container {
                max-width: 1024px;
            }
        }

        @media (min-width: 1440px) {
            .container {
                max-width: 1200px;
            }
        }

        .header {
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #e5e7eb;
            z-index: 10;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

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
            gap: 16px;
        }

        @media (min-width: 768px) {
            .header-content {
                height: 72px;
                padding: 0 24px;
            }
        }

        .back-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
}

        .back-btn:hover {
            background-color: #f3f4f6;
        }

        .title {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }

        .main-content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .main-content {
                padding: 24px;
                gap: 20px;
            }
        }

        @media (min-width: 1024px) {
            .main-content {
                padding: 32px;
                display: grid;
                grid-template-columns: 1fr 1.5fr;
                gap: 32px;
            }
        }

        /* Left column for controls on desktop */
        .controls-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        @media (min-width: 1024px) {
            .controls-section {
                gap: 20px;
            }
        }

        /* Right column for map on desktop */
        .map-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #567733;
            box-shadow: 0 0 0 3px rgba(86, 119, 51, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            width: 20px;
            height: 20px;
        }

        .current-location-button {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .current-location-button:hover {
            background-color: #f9fafb;
            border-color: #567733;
            color: #567733;
        }

        .current-location-button svg {
            color: #567733;
        }

        .locations-list {
            display: flex;
            flex-direction: column;
            gap: 1px;
            background: none;
        }

        .location-button {
            width: 100%;
            padding: 16px;
            background: none;
            border: none;
            border-bottom: 1px solid #c2bbb8;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .location-button:hover {
            background-color: #f9fafb;
        }

        .location-button.selected {
            background-color: #f3f4f6;
        }

        .hidden {
            display: none;
        }

        h3 {
            font-family: 'Source Serif';
            font-weight: 500;
            font-size: 24px;
        }

        @media (min-width: 768px) {
            h3 {
                font-size: 28px;
            }
        }

        /* Add new styles for location suggestions */
        .suggestions-container {
            margin-top: 8px;
        }

        .suggestion-item {
            width: 100%;
            padding: 12px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .suggestion-item:hover {
            background-color: #f9fafb;
            border-color: #567733;
        }

        .suggestion-main {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .suggestion-secondary {
            font-size: 12px;
            color: #6b7280;
        }

        .loading-indicator {
    text-align: center;
    color: #6b7280;
    padding: 8px;
    display: none;
}

        #map {
            width: 100%;
            height: 300px;
            margin: 16px 0;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        @media (min-width: 768px) {
            #map {
                height: 400px;
            }
        }

        @media (min-width: 1024px) {
            #map {
                height: 500px;
                margin: 0;
            }
        }

        /* Info window for pin */
        .pin-info {
            padding: 12px;
            text-align: center;
        }

        .confirm-location-btn {
            width: 100%;
            padding: 16px;
            background-color: #567733;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.3s ease;
            position: relative;
        }

        .confirm-location-btn:hover:not(:disabled) {
            background-color: #456028;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(86, 119, 51, 0.2);
        }

        .confirm-location-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

        .address-preview {
            margin-top: 16px;
            padding: 16px;
            background-color: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .address-preview.visible {
            display: block;
        }

        .address-preview h4 {
            margin: 0 0 8px 0;
            color: #567733;
            font-size: 14px;
            font-weight: 600;
        }

        .address-preview p {
            margin: 0;
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
        }

        .pin-instructions {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin: 8px 0;
            padding: 12px;
            background-color: #f3f4f6;
            border-radius: 8px;
        }

        @media (min-width: 1024px) {
            .pin-instructions {
                margin-bottom: 16px;
            }
        }

        .address-type-selector {
    margin-top: 16px;
            padding: 20px;
            background-color: white;
            border-radius: 16px;
    border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.type-title {
    font-family: 'Source Serif';
            font-size: 18px;
    font-weight: 500;
            margin-bottom: 16px;
    color: #1f2937;
}

.type-options {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

        @media (min-width: 768px) {
            .type-options {
                gap: 16px;
            }
        }

.type-option {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.type-option:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .type-option:has(input:checked) {
            background-color: #f0fdf4;
            border-color: #567733;
}

.type-option input[type="radio"] {
    width: 16px;
    height: 16px;
    margin: 0;
            accent-color: #567733;
}

.type-option span {
    font-size: 14px;
    color: #1f2937;
            font-weight: 500;
}

.custom-type-input {
    margin-top: 12px;
}

.custom-type-input input {
    width: 100%;
            padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
            background-color: #f9fafb;
            transition: all 0.3s ease;
}

.custom-type-input input:focus {
    outline: none;
            border-color: #567733;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(86, 119, 51, 0.1);
}

.default-address {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.default-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.default-checkbox input {
            width: 18px;
            height: 18px;
    margin: 0;
            accent-color: #567733;
}

.default-checkbox span {
    font-size: 14px;
    color: #1f2937;
            font-weight: 500;
}

.hidden {
    display: none;
}

/* Update error message styling */
.error-message {
    position: fixed;
            top: 80px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #fee2e2;
    color: #991b1b;
    padding: 12px 24px;
            border-radius: 12px;
    font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    animation: slideDown 0.3s ease-out;
    max-width: 90%;
    text-align: center;
}

        @media (min-width: 768px) {
            .error-message {
                top: 90px;
            }
        }

.error-message.show-error {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

@keyframes slideDown {
    from {
                transform: translateX(-50%) translateY(-20px);
        opacity: 0;
    }
    to {
                transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
            background-color: rgba(252, 248, 245, 0.9);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #567733;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Update the confirm button style to show loading state */
.confirm-location-btn.loading {
    background-color: #cbd5e1;
    pointer-events: none;
}

.confirm-location-btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: button-spin 0.8s linear infinite;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@keyframes button-spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

#errorMessage {
    background-color: #fee2e2;
    color: #dc2626;
    padding: 12px 16px;
            border-radius: 12px;
    font-size: 14px;
    margin: 8px 0;
    display: none;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
}

#errorMessage.visible {
    display: block;
}

        @keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

        /* Responsive layout adjustments */
        @media (min-width: 1024px) {
            .desktop-layout {
                display: contents;
            }
            
            .controls-wrapper {
                grid-column: 1;
            }
            
            .map-wrapper {
                grid-column: 2;
    position: sticky;
                top: 100px;
                height: fit-content;
            }
        }

        /* Success message styling */
        .success-message {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideDown 0.3s ease-out;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        /* Button press effects */
        button:active {
            transform: scale(0.98);
        }

        /* Remove tap highlight on mobile */
        button, input, select, textarea {
            -webkit-tap-highlight-color: transparent;
}

        /* Improve form elements appearance */
        input, select, textarea {
            font-family: inherit;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Print styles */
        @media print {
            .header, .confirm-location-btn {
                display: none;
            }
}
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <button class="back-btn" onclick="window.history.back()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
                    </svg>
                </button>
                <h3>Choose Location</h3>
            </div>
        </header>

        <main class="main-content">
            <div class="desktop-layout">
                <div class="controls-wrapper">
                    <div class="controls-section">
    <div class="search-container">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
        </svg>
        <input type="search" 
               class="search-input" 
               id="locationSearch"
               placeholder="Search for your address" 
               aria-label="Search locations">       
        <div class="loading-indicator" id="loadingIndicator">
            Searching...
    </div>
            </div>
                        
            <div id="errorMessage"></div>
                        
            <button class="current-location-button" id="getCurrentLocation">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 2v20M2 12h20" />
                </svg>
                Use current location
            </button>

                        <!-- Address Preview (moved to controls on desktop) -->
            <div class="address-preview" id="addressPreview">
                <h4>Delivery Address</h4>
                <p id="addressText"></p>
            </div>

                        <!-- Address Type Selector -->
<div class="address-type-selector" id="addressTypeSelector">
    <h4 class="type-title">Address Type</h4>
    <div class="type-options">
        <label class="type-option">
            <input type="radio" name="addressType" value="Home" checked>
            <span>🏠 Home</span>
        </label>
        <label class="type-option">
            <input type="radio" name="addressType" value="Office">
            <span>💼 Office</span>
        </label>
        <label class="type-option">
            <input type="radio" name="addressType" value="Other">
            <span>✨ Other</span>
        </label>
    </div>
    
    <!-- Add custom category input -->
    <div class="custom-type-input hidden" id="customTypeInput">
        <input type="text" 
               id="customType" 
               placeholder="Enter custom category"
               maxlength="20">
    </div>

    <div class="default-address">
        <label class="default-checkbox">
            <input type="checkbox" id="isDefault">
            <span>Set as default address</span>
        </label>
    </div>
</div>

            <button class="confirm-location-btn" id="confirmLocation" disabled>
                Confirm Location
            </button>
                    </div>
                </div>

                <div class="map-wrapper">
                    <div class="map-section">
                        <div class="pin-instructions">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display: inline; margin-right: 4px;">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            Drag the pin or tap on the map to set your location
                        </div>

                        <!-- Map Container -->
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;
        let currentInfoWindow = null;
        const defaultLocation = { lat: -6.2088, lng: 106.8456 }; // Default to Jakarta

        document.addEventListener('DOMContentLoaded', function() {
    const addressTypeRadios = document.querySelectorAll('input[name="addressType"]');
    const customTypeInput = document.getElementById('customTypeInput');
    const customType = document.getElementById('customType');

    // Handle address type selection
    addressTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Other') {
                customTypeInput.classList.remove('hidden');
                customType.focus();
            } else {
                customTypeInput.classList.add('hidden');
                customType.value = '';
            }
        });
    });
});

        // Initialize Google Maps and Autocomplete
        function initMap() {
            // Initialize map
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultLocation,
                zoom: 15,
                disableDefaultUI: true,
                zoomControl: true
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Initialize marker
            marker = new google.maps.Marker({
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            // Initialize autocomplete
            autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('locationSearch'),
                { componentRestrictions: { country: "id" } }
            );

            // Event listeners
            map.addListener('click', (e) => {
                placeMarkerAndPanTo(e.latLng);
            });

            marker.addListener('dragend', () => {
                const position = marker.getPosition();
                reverseGeocode(position.lat(), position.lng());
            });

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    showError('Please select a location from the dropdown');
                    return;
                }
                handleSelectedPlace(place);
            });
        }

        // Handle place selection from autocomplete
        function handleSelectedPlace(place) {
            const location = place.geometry.location;
            map.setCenter(location);
            placeMarkerAndPanTo(location);
            updateAddressPreview(place.formatted_address);
        }

        // Place marker and pan map
        function placeMarkerAndPanTo(latLng) {
            marker.setPosition(latLng);
            map.panTo(latLng);
            reverseGeocode(latLng.lat(), latLng.lng());
        }

        // Get current location
        document.getElementById('getCurrentLocation').addEventListener('click', () => {
            if (navigator.geolocation) {
                showLoading(true);
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        map.setCenter(pos);
                        placeMarkerAndPanTo(new google.maps.LatLng(pos.lat, pos.lng));
                        showLoading(false);
                    },
                    (error) => {
                        showLoading(false);
                        showError('Error getting your location: ' + error.message);
                    }
                );
            } else {
                showError('Geolocation is not supported by your browser');
            }
        });

        // Reverse geocode coordinates to address
        function reverseGeocode(lat, lng) {
            showLoading(true);
            geocoder.geocode({
                location: { lat, lng }
            }, (results, status) => {
                showLoading(false);
                if (status === 'OK' && results[0]) {
                    updateAddressPreview(results[0].formatted_address);
                } else {
                    showError('Could not find address for this location');
                }
            });
        }

        // Update address preview
        function updateAddressPreview(address) {
            const preview = document.getElementById('addressPreview');
            const addressText = document.getElementById('addressText');
            const confirmBtn = document.getElementById('confirmLocation');
            
            addressText.textContent = address;
            preview.classList.add('visible');
            confirmBtn.disabled = false;
        }

        // Confirm location
        document.getElementById('confirmLocation').addEventListener('click', () => {
    const position = marker.getPosition();
    const address = document.getElementById('addressText').textContent;

    if (!address) {
        showError('Please select a valid address');
        return;
    }

    const addressData = {
        address: address,
        lat: position.lat(),
        lng: position.lng()
    };

    saveAddress(addressData);
});

        // Save address to database
        function saveAddress(addressData) {
    const selectedType = document.querySelector('input[name="addressType"]:checked').value;
    const customTypeValue = document.getElementById('customType').value.trim();
    const isDefault = document.getElementById('isDefault').checked;
    
    // Show loading state
    showLoading(true);
    
    // Get final address type
    let finalAddressType = selectedType;
    if (selectedType === 'Other' && customTypeValue) {
        finalAddressType = customTypeValue;
    }

    const completeAddressData = {
        ...addressData,
        address_type: finalAddressType,
        is_default: isDefault
    };

    fetch('/save-address.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(completeAddressData)
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            window.history.back();
        } else {
            showError(data.error || 'Failed to save address');
        }
    })
    .catch(error => {
        showLoading(false);
        showError('An error occurred while saving the address');
        console.error('Error:', error);
    });
}

        // UI Helpers
        function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    const confirmButton = document.getElementById('confirmLocation');
    
    if (overlay) {
        overlay.style.display = show ? 'flex' : 'none';
    }
    
    if (confirmButton) {
        if (show) {
            confirmButton.classList.add('loading');
            confirmButton.disabled = true;
        } else {
            confirmButton.classList.remove('loading');
            confirmButton.disabled = false;
        }
    }
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (!errorDiv) return;

    // Show error message
    errorDiv.textContent = message;
    errorDiv.classList.add('visible');

    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorDiv.classList.remove('visible');
    }, 5000);
}


        // Initialize map
        initMap();
    </script>
</body>
</html>
