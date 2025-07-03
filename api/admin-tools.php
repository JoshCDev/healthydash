<?php
// Admin Tools Page - For debugging and maintenance
// This file should be DELETED after fixing issues

// Session already started by api/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';

// Only allow authenticated users
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools - HealthyDash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .loading { display: none; }
        .loading.show { display: block; }
        .result-box { 
            max-height: 400px; 
            overflow-y: auto; 
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">🔧 Admin Tools - HealthyDash</h1>
                <a href="/menu.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ← Back to Menu
                </a>
            </div>
            <p class="text-gray-600 mt-2">Tools untuk debugging dan maintenance aplikasi</p>
        </div>

        <!-- Database Checker -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🗄️ Database Checker</h2>
            <p class="text-gray-600 mb-4">Periksa status database dan perbaiki masalah order history</p>
            
            <button onclick="checkDatabase()" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                Check Database
            </button>
            
            <div id="db-loading" class="loading mt-4">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
                    <span class="ml-2 text-gray-600">Checking database...</span>
                </div>
            </div>
            
            <div id="db-result" class="mt-4 hidden">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Result:</h3>
                <div class="result-box bg-gray-100 p-4 rounded border text-sm">
                    <pre id="db-result-content"></pre>
                </div>
            </div>
        </div>

        <!-- Email Configuration Checker -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📧 Email Configuration Checker</h2>
            <p class="text-gray-600 mb-4">Test konfigurasi Mailgun untuk reset password</p>
            
            <div class="space-x-2 space-y-2">
                <button onclick="checkEmail()" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Check Email Config
                </button>
                <button onclick="debugEnv()" class="bg-purple-500 text-white px-6 py-2 rounded hover:bg-purple-600">
                    Debug Environment
                </button>
                <br>
                <button onclick="testMailgun()" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">
                    Test Mailgun Domains
                </button>
            </div>
            
            <div id="email-loading" class="loading mt-4">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                    <span class="ml-2 text-gray-600">Testing email configuration...</span>
                </div>
            </div>
            
            <div id="email-result" class="mt-4 hidden">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Result:</h3>
                <div class="result-box bg-gray-100 p-4 rounded border text-sm">
                    <pre id="email-result-content"></pre>
                </div>
            </div>
        </div>

        <!-- Quick Fixes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">⚡ Quick Fixes</h2>
            
            <!-- Common Issues -->
            <div class="grid md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">🍽️ Order History Issues</h3>
                    <p class="text-sm text-gray-600 mb-3">Items showing as "Unknown Item"</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Tabel menu_items kosong (akan populate 10 menu items)</li>
                        <li>• Missing relasi order_items → menu_items</li>
                        <li>• Data tidak sinkron dengan menu.php</li>
                    </ul>
                    <div class="mt-2 text-xs text-blue-600">
                        <strong>Auto-fix:</strong> Populate dengan data asli dari menu.php
                    </div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">📨 Email Issues</h3>
                    <p class="text-sm text-gray-600 mb-3">Domain not found error (404)</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Domain tidak terdaftar di Mailgun</li>
                        <li>• Environment variables salah</li>
                        <li>• DNS tidak ter-setup</li>
                    </ul>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-medium text-gray-800 mb-2">🔐 Signup Issues</h3>
                    <p class="text-sm text-gray-600 mb-3">403 Forbidden untuk check_availability.php</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Missing public endpoint</li>
                        <li>• Route tidak terdaftar di index.php</li>
                        <li>• Permissions issue</li>
                    </ul>
                    <div class="mt-2 text-xs text-green-600">
                        <strong>Fixed:</strong> Endpoint public sudah dibuat
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-sm text-gray-500">
                💡 <strong>Tip:</strong> Jalankan database checker terlebih dahulu untuk mengidentifikasi masalah
            </div>
        </div>
    </div>

    <script>
        async function checkDatabase() {
            const loading = document.getElementById('db-loading');
            const result = document.getElementById('db-result');
            const content = document.getElementById('db-result-content');
            
            loading.classList.add('show');
            result.classList.add('hidden');
            
            try {
                const response = await fetch('/check-database.php');
                const data = await response.json();
                
                content.textContent = JSON.stringify(data, null, 2);
                result.classList.remove('hidden');
                
                // Show success/error styling
                if (data.issue_found) {
                    result.classList.add('border-orange-300', 'bg-orange-50');
                    result.classList.remove('border-green-300', 'bg-green-50');
                } else {
                    result.classList.add('border-green-300', 'bg-green-50');
                    result.classList.remove('border-orange-300', 'bg-orange-50');
                }
                
            } catch (error) {
                content.textContent = 'Error: ' + error.message;
                result.classList.remove('hidden');
                result.classList.add('border-red-300', 'bg-red-50');
            } finally {
                loading.classList.remove('show');
            }
        }
        
        async function checkEmail() {
            const loading = document.getElementById('email-loading');
            const result = document.getElementById('email-result');
            const content = document.getElementById('email-result-content');
            
            loading.classList.add('show');
            result.classList.add('hidden');
            
            try {
                const response = await fetch('/test-email-config.php');
                const data = await response.json();
                
                content.textContent = JSON.stringify(data, null, 2);
                result.classList.remove('hidden');
                
                // Show success/error styling based on API test
                if (data.api_test && data.api_test.success) {
                    result.classList.add('border-green-300', 'bg-green-50');
                    result.classList.remove('border-red-300', 'bg-red-50');
                } else {
                    result.classList.add('border-red-300', 'bg-red-50');
                    result.classList.remove('border-green-300', 'bg-green-50');
                }
                
            } catch (error) {
                content.textContent = 'Error: ' + error.message;
                result.classList.remove('hidden');
                result.classList.add('border-red-300', 'bg-red-50');
            } finally {
                loading.classList.remove('show');
            }
        }
        
        async function debugEnv() {
            const loading = document.getElementById('email-loading');
            const result = document.getElementById('email-result');
            const content = document.getElementById('email-result-content');
            
            loading.classList.add('show');
            result.classList.add('hidden');
            
            try {
                const response = await fetch('/debug-env.php');
                const data = await response.json();
                
                content.textContent = JSON.stringify(data, null, 2);
                result.classList.remove('hidden');
                
                // Show success/error styling based on environment loading
                if (data.defined_constants && data.defined_constants.MAILGUN_DOMAIN !== 'NOT_DEFINED') {
                    result.classList.add('border-green-300', 'bg-green-50');
                    result.classList.remove('border-red-300', 'bg-red-50');
                } else {
                    result.classList.add('border-red-300', 'bg-red-50');
                    result.classList.remove('border-green-300', 'bg-green-50');
                }
                
            } catch (error) {
                content.textContent = 'Error: ' + error.message;
                result.classList.remove('hidden');
                result.classList.add('border-red-300', 'bg-red-50');
            } finally {
                loading.classList.remove('show');
            }
        }
        
        async function testMailgun() {
            const loading = document.getElementById('email-loading');
            const result = document.getElementById('email-result');
            const content = document.getElementById('email-result-content');
            
            loading.classList.add('show');
            result.classList.add('hidden');
            
            try {
                const response = await fetch('/test-mailgun-simple.php');
                const data = await response.json();
                
                content.textContent = JSON.stringify(data, null, 2);
                result.classList.remove('hidden');
                
                // Show success/error styling based on working domains
                if (data.recommendation && data.recommendation.action === 'Use working domain') {
                    result.classList.add('border-green-300', 'bg-green-50');
                    result.classList.remove('border-red-300', 'bg-red-50');
                } else {
                    result.classList.add('border-orange-300', 'bg-orange-50');
                    result.classList.remove('border-green-300', 'bg-green-50');
                }
                
            } catch (error) {
                content.textContent = 'Error: ' + error.message;
                result.classList.remove('hidden');
                result.classList.add('border-red-300', 'bg-red-50');
            } finally {
                loading.classList.remove('show');
            }
        }
        
        // Auto-load on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto check database on load
            setTimeout(() => {
                checkDatabase();
            }, 1000);
        });
    </script>
</body>
</html> 