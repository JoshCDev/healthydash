<?php
// At the start of the file - check before starting session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add security headers
header("Content-Security-Policy: frame-ancestors 'self' https://accounts.google.com");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit;
}

require_once __DIR__ . '/includes/otp_handler.php';
require_once __DIR__ . '/includes/config.php';

// Use the database connection from config
$db = Database::getInstance();

// Rate limiting for Google auth
function checkGoogleAuthRateLimit($ip) {
    global $db;
    try {
        // Clean old attempts
        $stmt = $db->prepare("DELETE FROM google_auth_attempts WHERE timestamp < (NOW() - INTERVAL 1 HOUR)");
        $stmt->execute();

        // Check attempts
        $stmt = $db->prepare("SELECT COUNT(*) FROM google_auth_attempts WHERE ip = ? AND timestamp > (NOW() - INTERVAL 1 HOUR)");
        $stmt->execute([$ip]);
        $attempts = $stmt->fetchColumn();

        if ($attempts >= 5) { // Max 5 attempts per hour
            return false;
        }

        // Log attempt
        $stmt = $db->prepare("INSERT INTO google_auth_attempts (ip, timestamp) VALUES (?, NOW())");
        $stmt->execute([$ip]);

        return true;
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        return false;
    }
}

// Handle Google Sign-In
if (isset($_POST['credential'])) {
    try {
        // Check rate limiting
        if (!checkGoogleAuthRateLimit($_SERVER['REMOTE_ADDR'])) {
            throw new Exception("Too many attempts. Please try again later.");
        }

        require_once __DIR__ . '/../vendor/autoload.php';
        
        $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
        
        // Verify and get payload
        try {
            $payload = $client->verifyIdToken($_POST['credential']);
        } catch (Exception $e) {
            error_log("Token verification failed: " . $e->getMessage());
            throw new Exception("Invalid authentication token.");
        }
        
        if ($payload) {
            // Validate email
            $email = filter_var($payload['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                throw new Exception("Invalid email format from Google authentication.");
            }

            // Validate email is verified with Google
            if (!$payload['email_verified']) {
                throw new Exception("Email not verified with Google.");
            }

            $name = $payload['name'] ?? '';
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
            
            try {
                $db->beginTransaction();

                // Check existing user
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingUser) {
                    // Update existing user
                    if (!$existingUser['google_auth']) {
                        $stmt = $db->prepare("UPDATE users SET google_auth = 1 WHERE user_id = ?");
                        $stmt->execute([$existingUser['user_id']]);
                    }
                    
                    $_SESSION['user_id'] = $existingUser['user_id'];
                    $_SESSION['auth_time'] = time();
                    
                    $db->commit();
                    
                    header('Location: ' . ($existingUser['initial_data_complete'] ? 'menu.php' : 'menu.php'));
                    exit();
                } else {
                    // Create new user with unique username
                    $baseUsername = $username;
                    $counter = 1;
                    do {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                        $stmt->execute([$username]);
                        $exists = $stmt->fetchColumn();
                        
                        if ($exists) {
                            $username = $baseUsername . $counter;
                            $counter++;
                        }
                    } while ($exists && $counter < 100); // Limit attempts
                    
                    if ($counter >= 100) {
                        throw new Exception("Unable to generate unique username.");
                    }
                    
                    // Insert new user
                    $stmt = $db->prepare("
                        INSERT INTO users (
                            email,
                            username,
                            password_hash,
                            google_auth,
                            created_at,
                            is_verified,
                            initial_data_complete
                        ) VALUES (?, ?, NULL, 1, NOW(), 1, 0)
                    ");
                    
                    if (!$stmt->execute([$email, $username])) {
                        throw new Exception("Failed to create user account.");
                    }
                    
                    $userId = $db->lastInsertId();
                    
                    // Log the registration
                    $stmt = $db->prepare("
                        INSERT INTO auth_logs (
                            user_id,
                            action,
                            ip_address,
                            user_agent
                        ) VALUES (?, 'GOOGLE_REGISTRATION', ?, ?)
                    ");
                    $stmt->execute([
                        $userId,
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_USER_AGENT']
                    ]);
                    
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['auth_time'] = time(); // Add this
                    $_SESSION['initial_data_complete'] = 0; // Add this
                    
                    $db->commit();
                    
                    header('Location: menu.php');
                    exit();
                }
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Database error during Google auth: " . $e->getMessage());
                throw new Exception("An error occurred during account creation.");
            }
        } else {
            throw new Exception("Invalid authentication response.");
        }
    } catch (Exception $e) {
        error_log("Google auth error: " . $e->getMessage());
        $errors['google'] = "Authentication failed. Please try again or use email signup.";
        
        // If it's an AJAX request, return JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => $errors['google']]);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email_signup'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $acceptTerms = isset($_POST['terms']);
        
        $errors = [];
        
        // Username validation
        if (empty($username)) {
            $errors['username'] = "Username is required";
        } elseif (strlen($username) < 3) {
            $errors['username'] = "Username must be at least 3 characters";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = "Username can only contain letters, numbers, and underscores";
        }
        
        // Email validation
        if (empty($email)) {
            $errors['email'] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address";
        } else {
            // Check if email already exists
            $stmt = $db->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors['email'] = "Email already registered";
            }
        }
        
        // Password validation
        if (empty($password)) {
            $errors['password'] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = "Password must contain at least one uppercase letter";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = "Password must contain at least one lowercase letter";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = "Password must contain at least one number";
        }
        
        // Confirm password
        if ($password !== $confirmPassword) {
            $errors['confirmPassword'] = "Passwords do not match";
        }
        
        // Terms acceptance
        if (!$acceptTerms) {
            $errors['terms'] = "You must accept the terms";
        }
        
        if (empty($errors)) {
            $_SESSION['signup_data'] = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'google_auth' => false,
                'timestamp' => time()
            ];
            
            $otpHandler = new OTPHandler();
            $otp_id = $otpHandler->sendOTP($email);
            
            if ($otp_id) {
                $_SESSION['otp_id'] = $otp_id;
                $_SESSION['last_otp_time'] = time();
                header('Location: OTP.php');
                exit();
            } else {
                $errors['general'] = "Failed to send verification code. Please try again.";
            }
        }
    }
    
    // Google Sign In
    if (isset($_POST['credential'])) {
        $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
        
        try {
            $payload = $client->verifyIdToken($_POST['credential']);
            
            if ($payload) {
                $email = $payload['email'];
                $name = $payload['name'];
                
                // Check if user exists
                $stmt = $db->prepare("SELECT user_id, initial_data_complete FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['auth_time'] = time();
                    header('Location: menu.php');
                    exit();
                } else {
                    $_SESSION['signup_data'] = [
                        'username' => $name,
                        'email' => $email,
                        'google_auth' => true,
                        'google_payload' => $payload,
                        'timestamp' => time()
                    ];
                    header('Location: menu.php');
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Google auth error: " . $e->getMessage());
            $errors['google'] = "Authentication failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Create Account - FinTuner</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="google-signin-client_id" content="<?php echo htmlspecialchars(GOOGLE_CLIENT_ID); ?>">
    <meta name="google-signin-scope" content="profile email">
    <link rel="stylesheet" href="/assets/font/stylesheet.css">
    <style>
        *{
            font-family: 'mona sans';
        }
        body {
            background-color:rgb(252, 248, 245);
            color: #1f2937;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 380px;
            padding: 20px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #1f2937;
            font-family: 'Source Serif';
            font-weight: 500;
            font-size: 32px;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #1f2937;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #94a3b8;
            outline: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            width: 24px;
            height: 24px;
            z-index: 2;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .password-toggle:hover {
            opacity: 1;
        }

        .password-toggle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Password strength */
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-dots {
            display: flex;
            gap: 4px;
        }

        .strength-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #e2e8f0;
            transition: all 0.3s ease;
        }

        .strength-dot.active.weak {
            background-color: #ff4444;
        }

        .strength-dot.active.fair {
            background-color: #ffbb33;
        }

        .strength-dot.active.good {
            background-color: #00C851;
        }

        .strength-dot.active.strong {
            background-color: #007E33;
        }

        .strength-text {
            color: #666;
            transition: color 0.3s ease;
        }

        .strength-text.weak { color: #ff4444; }
        .strength-text.fair { color: #ffbb33; }
        .strength-text.good { color: #00C851; }
        .strength-text.strong { color: #007E33; }

        /* Error messages */
        .error-message {
            color: #ff3b30;
            font-size: 12px;
            margin-top: 8px;
            margin-left: 4px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            text-align: left;
            line-height: 1.4;
        }

        .error-message.visible {
            display: block;
            opacity: 1;
        }

        /* Special styling for terms error */
        .checkbox-container .error-message {
            margin-left: 28px;
            margin-top: 5px;
            font-size: 11px;
        }

        /* Checkbox styling */
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 20px 0;
            padding: 8px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .checkbox-container:hover {
            background-color:rgb(234, 240, 226);
        }

        .checkbox-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
        }

        .checkbox-container label {
            font-size: 14px;
            color: #1f2937;
            flex: 1;
        }

        .signup-btn {
            width: 100%;
            padding: 14px;
            background-color: #567733;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-weight: bold;
            color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.4;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .signup-btn.active {
            opacity: 1;
            cursor: pointer;
        }

        .signup-btn.loading {
            animation: loading 1.5s infinite;
            pointer-events: none;
        }

        .signup-btn:not(:disabled) {
            opacity: 1;
        }

        .signup-btn:disabled {
            color:rgb(163, 163, 163);
            cursor: not-allowed;
            background-color: white;
            opacity: 0.4;
        }

        .signup-btn:active:not(:disabled) {
            transform: scale(0.98);
        }

        @keyframes loading {
            0% { opacity: 0.6; }
            50% { opacity: 0.8; }
            100% { opacity: 0.6; }
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider:before,
        .divider:after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #e2e8f0;
        }

        .divider:before { left: 0; }
        .divider:after { right: 0; }

        .divider span {
            padding: 0 15px;
            color: #666;
        }

        /* Google button */
        .google-btn {
            width: 100%;
            margin-top: 15px;
            display: flex;
            justify-content: center;
        }

        /* Links */
        a {
            color: #567733;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        a:hover {
            opacity: 0.8;
            text-decoration: none;
            color:rgb(108, 165, 47);
        }

        /* Back button */
        .back-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 16px;
            padding: 5px 0;
            margin-bottom: 20px;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .back-btn:hover {
            opacity: 1;
        }

        .back-btn svg {
            width: 24px;
            height: 24px;
        }

        .signup-prompt {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .signup-prompt a {
            color: #94a3b8;
            text-decoration: none;
            margin-left: 5px;
            font-weight: 500;
        }

        .signup-prompt a:hover {
            opacity: 0.8;
        }

        /* Logo */
        .logo {
            text-align: center;
            margin: 32px 0;
        }

        .logo img {
            width: 160px;
            height: auto;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal {
            background-color: white;
            width: 100%;
            max-width: 100%;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .modal-header {
            text-align: center;
            padding: 1rem;
            font-size: 1.6rem;
            font-weight: 500;
            color: #1f2937;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        .modal-content {
            padding: 1rem;
            background: none !important;
            color: #1f2937;
            overflow-y: auto;
            max-height: 100%;
            flex-grow: 1;
            text-align: left;
            font-size: 0.8rem;
        }

        .modal-close {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 0.75rem;
            background-color: white;
            color: #1f2937;
            text-decoration: none;
            border: none;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .modal-close:hover {
            background-color: #e2e8f0;
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            input {
                font-size: 14px;
            }
            h1 {
                margin-bottom: 25px;
            }
        }
    </style>
</head>
<body>


    <div class="container">

        <h1>Create your Account</h1>
        
        <form id="signupForm" method="POST" action="" novalidate>
            <input type="hidden" name="email_signup" value="1">
            
            <div class="form-group">
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Username*" 
                       value="<?php echo htmlspecialchars($username ?? ''); ?>"
                       required>
                <div class="error-message" id="username-error"></div>
            </div>

            <div class="form-group">
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Email*"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       required>
                <div class="error-message" id="email-error"></div>
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Password*"
                           required>
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword('password')" 
                            aria-label="Toggle password visibility">
                        <img src="/assets/images/eyes_closed.png" 
                             alt="Toggle password" 
                             class="toggle-icon" 
                             id="password-toggle-icon">
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-dots">
                        <div class="strength-dot" id="dot-1"></div>
                        <div class="strength-dot" id="dot-2"></div>
                        <div class="strength-dot" id="dot-3"></div>
                        <div class="strength-dot" id="dot-4"></div>
                    </div>
                    <span class="strength-text" id="strength-text">Kekuatan Password</span>
                </div>
                <div class="error-message" id="password-error"></div>
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" 
                           id="confirmPassword" 
                           name="confirmPassword" 
                           placeholder="Confirm Password*"
                           required>
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword('confirmPassword')" 
                            aria-label="Toggle password visibility">
                        <img src="/assets/images/eyes_closed.png" 
                             alt="Toggle password" 
                             class="toggle-icon" 
                             id="confirmPassword-toggle-icon">
                    </button>
                </div>
                <div class="error-message" id="confirmPassword-error"></div>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" 
                       id="terms" 
                       name="terms" 
                       required>
                <label for="terms">
                    I have read and agree to the 
                    <a href="#" class="open-modal" data-modal-id="modal1">Privacy Policy</a> and 
                    <a href="#" class="open-modal" data-modal-id="modal2">Terms of Service</a>
                </label>
                <div class="error-message" id="terms-error"></div>
            </div>

            <button type="submit" 
                    class="signup-btn" 
                    id="signupButton" 
                    disabled>
                Sign up with Email
            </button>

            <div class="divider">
                <span>or</span>
            </div>

            <div class="google-btn">
    <div id="g_id_onload"
         data-client_id="<?php echo htmlspecialchars(GOOGLE_CLIENT_ID); ?>"
         data-context="signin"
         data-ux_mode="popup"
         data-callback="handleCredentialResponse"
         data-auto_prompt="false"
         data-auto_select="true">
    </div>

    <div class="g_id_signin"
         data-type="standard"
         data-size="large"
         data-theme="outline"
         data-text="continue_with"
         data-shape="rectangular"
         data-logo_alignment="left"
         data-width="300">
    </div>
</div>
<p class="signup-prompt">
    Already have an account? <a href="login.php">Login</a>
</p>
            <div class="logo">
                <img src="/assets/images/healthydashlogo.png" alt="HealthyDash logo">
            </div>
        </form>
    </div>

    <div class="modal-backdrop" id="modal1">
    <div class="modal">
        <div class="modal-header">Privacy Policy</div>
        <div class="modal-content">
            <h2 class="mb-6">Privacy Policy</h2>

            <ol class="space-y-6">
                <li>
                    <strong class="block mb-2">Introduction</strong>
                    <p class="mb-4">
                        This Privacy Policy explains how HealthyDash collects, uses, stores, and protects your personal data. We are committed to protecting your privacy and complying with applicable data protection laws.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Information We Collect</strong>
                    <div class="mb-4">
                        <p class="mb-2"><strong>a. Personal Information</strong>:</p>
                        <ul class="ml-4 mb-4 space-y-1">
                            <li>Full name</li>
                            <li>Email address</li>
                            <li>Phone number</li>
                            <li>Delivery addresses</li>
                            <li>Profile pictures (if provided)</li>
                        </ul>

                        <p class="mb-2"><strong>b. Order Information</strong>:</p>
                        <ul class="ml-4 mb-4 space-y-1">
                            <li>Order history</li>
                            <li>Delivery preferences</li>
                            <li>Payment information</li>
                            <li>Special dietary requirements</li>
                        </ul>

                        <p class="mb-2"><strong>c. Technical Information</strong>:</p>
                        <ul class="ml-4 space-y-1">
                            <li>IP address</li>
                            <li>Device information</li>
                            <li>Browser type</li>
                            <li>Cookies and similar technologies</li>
                        </ul>
                    </div>
                </li>

                <li>
                    <strong class="block mb-2">How We Use Your Information</strong>
                    <p class="mb-2">We use your information for:</p>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li><strong>Essential Operations</strong>: Processing and delivering your orders</li>
                        <li><strong>Service Improvement</strong>: Analyzing usage patterns to enhance our menu and services</li>
                        <li><strong>Communication</strong>: Sending order updates and responding to inquiries</li>
                        <li><strong>Legal Compliance</strong>: Meeting our legal obligations</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Information Sharing</strong>
                    <p class="mb-2">We only share your information with:</p>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Delivery partners (for order fulfillment)</li>
                        <li>Payment processors (for transaction processing)</li>
                        <li>Service providers (for technical support)</li>
                        <li>Legal authorities (when required by law)</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Data Security</strong>
                    <p class="mb-4">
                        We implement appropriate security measures including encryption, regular security assessments, secure data storage, and access controls to protect your personal information.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Your Rights</strong>
                    <p class="mb-2">You have the right to:</p>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Access your personal data</li>
                        <li>Correct inaccurate information</li>
                        <li>Request data deletion</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Export your data</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Data Retention</strong>
                    <p class="mb-4">
                        We retain your data for active accounts as long as necessary, inactive accounts for 12 months, order history for 3 years, and payment information as required by law.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Cookie Policy</strong>
                    <p class="mb-4">
                        We use cookies and similar technologies to improve your browsing experience and analyze service usage. You can control cookie settings through your browser preferences.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Contact Information</strong>
                    <p class="mb-2">For privacy-related inquiries, contact us at:</p>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Email: <strong>support@healtyDash.com</strong></li>
                    </ul>
                </li>
            </ol>
        </div>
        <button class="modal-close">Close</button>
    </div>
</div>

<div class="modal-backdrop" id="modal2">
    <div class="modal">
        <div class="modal-header">Terms of Service</div>
        <div class="modal-content">
            <h2 class="mb-6">Terms of Service</h2>

            <ol class="space-y-6">
                <li>
                    <strong class="block mb-2">Agreement to Terms</strong>
                    <p class="mb-4">
                        Welcome to <strong>HealthyDash</strong>! By accessing or using our services, you agree to these Terms of Service and our Privacy Policy. If you do not agree to these terms, please do not use our services.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Service Description</strong>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li><strong>"Service"</strong>: Our food ordering and delivery platform</li>
                        <li><strong>"User"</strong>: Any person accessing or using our services</li>
                        <li><strong>"Order"</strong>: A request for food items through our platform</li>
                        <li><strong>"Delivery"</strong>: The process of delivering ordered items to users</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">User Accounts</strong>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Must be 18 or older to create an account</li>
                        <li>Provide accurate and complete information</li>
                        <li>Maintain account security</li>
                        <li>Responsible for all account activity</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Ordering and Delivery</strong>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Orders subject to availability</li>
                        <li>Accurate delivery address required</li>
                        <li>Delivery times are estimates only</li>
                        <li>Special instructions must be clear and reasonable</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Pricing and Payment</strong>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>All prices in local currency</li>
                        <li>Delivery fees clearly stated</li>
                        <li>Payment processed securely</li>
                        <li>Refund policy applies to eligible orders</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Prohibited Activities</strong>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Submitting false information</li>
                        <li>Unauthorized use of accounts</li>
                        <li>Abusive behavior towards staff</li>
                        <li>Fraudulent orders or payments</li>
                    </ul>
                </li>

                <li>
                    <strong class="block mb-2">Limitation of Liability</strong>
                    <p class="mb-4">
                        HealthyDash is not liable for delivery delays, food quality issues beyond our control, or service interruptions due to technical issues or force majeure events.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Modifications to Service</strong>
                    <p class="mb-4">
                        We reserve the right to modify or discontinue our service at any time. Changes will be communicated through our platform or via email.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Termination</strong>
                    <p class="mb-4">
                        We may suspend or terminate accounts for violations of these terms. Users may delete their accounts at any time.
                    </p>
                </li>

                <li>
                    <strong class="block mb-2">Contact Information</strong>
                    <p class="mb-2">For service-related inquiries:</p>
                    <ul class="ml-4 mb-4 space-y-2">
                        <li>Email: <strong>support@healthydash.com</strong></li>
                        <li>Customer Support Hours: 24/7</li>
                    </ul>
                </li>
            </ol>
        </div>
        <button class="modal-close">Close</button>
    </div>
</div>
</div>
    
    <script>
        const form = document.getElementById('signupForm');
        const signupButton = document.getElementById('signupButton');
        const requiredFields = ['username', 'email', 'password', 'confirmPassword'];
        
        // Track form state
        let formSubmitted = false;
        const validationState = {
            username: { touched: false, valid: false },
            email: { touched: false, valid: false },
            password: { touched: false, valid: false },
            confirmPassword: { touched: false, valid: false }
        };

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Password toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(`${fieldId}-toggle-icon`);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.src = '/assets/images/eyes_open.png';
            } else {
                field.type = 'password';
                icon.src = '/assets/images/eyes_closed.png';
            }
        }

        // Check availability functions
        function checkUsernameAvailability(username) {
    const formData = new FormData();
    formData.append('type', 'username');
    formData.append('value', username);

    fetch('/check-availability.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (validationState.username.touched) {
            const error = document.getElementById('username-error');
            if (!data.available) {
                showError(error, 'Username already taken');
                validationState.username.valid = false;
            }
            updateSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function checkEmailAvailability(email) {
    const formData = new FormData();
    formData.append('type', 'email');
    formData.append('value', email);

    fetch('/check-availability.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (validationState.email.touched) {
            const error = document.getElementById('email-error');
            if (!data.available) {
                showError(error, 'Email already registered');
                validationState.email.valid = false;
            }
            updateSubmitButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;
            const dots = [
                document.getElementById('dot-1'),
                document.getElementById('dot-2'),
                document.getElementById('dot-3'),
                document.getElementById('dot-4')
            ];
            const strengthText = document.getElementById('strength-text');
            
            // Reset all dots
            dots.forEach(dot => {
                dot.classList.remove('weak', 'fair', 'good', 'strong');
                dot.classList.remove('active');
            });

            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[!@#$%^&*]/.test(password)) score++;

            let strengthClass, strengthMessage;
            switch(score) {
                case 0:
                    strengthClass = 'weak';
                    strengthMessage = 'Sangat Lemah';
                    break;
                case 1:
                    strengthClass = 'weak';
                    strengthMessage = 'Lemah';
                    break;
                case 2:
                    strengthClass = 'fair';
                    strengthMessage = 'Cukup';
                    break;
                case 3:
                    strengthClass = 'good';
                    strengthMessage = 'Kuat';
                    break;
                case 4:
                    strengthClass = 'strong';
                    strengthMessage = 'Sangat Kuat';
                    break;
            }

            // Update dots
            for (let i = 0; i < score; i++) {
                dots[i].classList.add('active', strengthClass);
            }

            strengthText.textContent = strengthMessage;
            strengthText.className = 'strength-text ' + strengthClass;
        }

        // Validation functions
        const validateUsername = debounce((username) => {
            const error = document.getElementById('username-error');
            
            if (username.length < 3) {
                showError(error, 'Username must be at least 3 characters');
                validationState.username.valid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                showError(error, 'Username can only contain letters, numbers, and underscores');
                validationState.username.valid = false;
            } else {
                hideError(error);
                validationState.username.valid = true;
                // Only check availability if basic validation passes
                checkUsernameAvailability(username);
            }
            updateSubmitButton();
        }, 500);

        const validateEmail = debounce((email) => {
            const error = document.getElementById('email-error');
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError(error, 'Please enter a valid email address');
                validationState.email.valid = false;
            } else {
                hideError(error);
                validationState.email.valid = true;
                // Only check availability if email format is valid
                checkEmailAvailability(email);
            }
            updateSubmitButton();
        }, 500);

        const validatePassword = debounce((password) => {
            const error = document.getElementById('password-error');
            
            if (password.length < 8) {
                showError(error, 'Password must be at least 8 characters');
                validationState.password.valid = false;
            } else if (!/[A-Z]/.test(password)) {
                showError(error, 'Password must contain at least one uppercase letter');
                validationState.password.valid = false;
            } else if (!/[a-z]/.test(password)) {
                showError(error, 'Password must contain at least one lowercase letter');
                validationState.password.valid = false;
            } else if (!/[0-9]/.test(password)) {
                showError(error, 'Password must contain at least one number');
                validationState.password.valid = false;
            } else {
                hideError(error);
                validationState.password.valid = true;
            }
            
            checkPasswordStrength(password);
            
            // Validate confirm password if it's been touched
            if (validationState.confirmPassword.touched) {
                validateConfirmPassword();
            }
            
            updateSubmitButton();
        }, 500);

        const validateConfirmPassword = debounce(() => {
            if (!validationState.confirmPassword.touched) return;
            
            const error = document.getElementById('confirmPassword-error');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                showError(error, 'Passwords do not match');
                validationState.confirmPassword.valid = false;
            } else {
                hideError(error);
                validationState.confirmPassword.valid = true;
            }
            
            updateSubmitButton();
        }, 500);

        function showError(element, message) {
            element.textContent = message;
            element.classList.add('visible');
        }

        function hideError(element) {
            element.classList.remove('visible');
            element.textContent = '';
        }

        // Update submit button state
        function updateSubmitButton() {
            const allValid = Object.values(validationState)
                .every(field => !field.touched || field.valid);
            const terms = document.getElementById('terms').checked;
            
            signupButton.disabled = !(allValid && terms);
            
            if (allValid && terms) {
                signupButton.classList.add('active');
            } else {
                signupButton.classList.remove('active');
            }
        }

        // Event listeners
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            
            field.addEventListener('input', () => {
                if (validationState[fieldId].touched) {
                    switch(fieldId) {
                        case 'username':
                            validateUsername(field.value);
                            break;
                        case 'email':
                            validateEmail(field.value);
                            break;
                        case 'password':
                            validatePassword(field.value);
                            break;
                        case 'confirmPassword':
                            validateConfirmPassword();
                            break;
                    }
                }
            });

            field.addEventListener('blur', () => {
                validationState[fieldId].touched = true;
                switch(fieldId) {
                    case 'username':
                        validateUsername(field.value);
                        break;
                    case 'email':
                        validateEmail(field.value);
                        break;
                    case 'password':
                        validatePassword(field.value);
                        break;
                    case 'confirmPassword':
                        validateConfirmPassword();
                        break;
                }
            });

            field.addEventListener('focus', () => {
                hideError(document.getElementById(`${fieldId}-error`));
            });
        });

        document.getElementById('terms').addEventListener('change', function() {
            const termsError = document.getElementById('terms-error');
            if (this.checked) {
                hideError(termsError);
            }
            updateSubmitButton();
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            formSubmitted = true;

            // Mark all fields as touched and validate
            requiredFields.forEach(fieldId => {
                validationState[fieldId].touched = true;
                const field = document.getElementById(fieldId);
                switch(fieldId) {
                    case 'username':
                        validateUsername(field.value);
                        break;
                    case 'email':
                        validateEmail(field.value);
                        break;
                    case 'password':
                        validatePassword(field.value);
                        break;
                    case 'confirmPassword':
                        validateConfirmPassword();
                        break;
                }
            });

            // Final check before submit
            const isValid = Object.values(validationState)
                .every(field => field.valid);

            if (isValid && document.getElementById('terms').checked) {
                signupButton.disabled = true;
                signupButton.classList.add('loading');
                signupButton.textContent = 'Creating account...';
                
                setTimeout(() => {
                    this.submit();
                }, 100);
            }
        });

    // Replace the handleCredentialResponse function with this updated version
    function handleCredentialResponse(response) {
    // Add debug logging
    console.log("Google response received:", response);
    
    const termsCheckbox = document.getElementById('terms');
    
    if (!termsCheckbox.checked) {
        const termsError = document.getElementById('terms-error');
        showError(termsError, 'Please accept the terms and conditions');
        return;
    }

    try {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href; // Use current URL
        form.style.display = 'none';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'credential';
        input.value = response.credential;
        
        form.appendChild(input);
        document.body.appendChild(form);
        
        console.log("Submitting form with credential");
        form.submit();
    } catch (error) {
        console.error("Error in form submission:", error);
        // Create a temporary error message for Google sign-in
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message visible';
        errorDiv.style.textAlign = 'center';
        errorDiv.style.marginBottom = '15px';
        errorDiv.textContent = 'Failed to process Google sign-in';
        
        const googleBtn = document.querySelector('.google-btn');
        if (googleBtn) {
            googleBtn.parentNode.insertBefore(errorDiv, googleBtn);
            setTimeout(() => {
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 300);
            }, 5000);
        }
    }
}

// Add this function
function showError(elementId, message) {
    const errorDiv = document.getElementById(elementId);
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('visible');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.classList.remove('visible');
        }, 5000);
    } else {
        // Create error element if it doesn't exist
        const newError = document.createElement('div');
        newError.id = elementId;
        newError.className = 'error-message visible';
        newError.textContent = message;
        
        // Find appropriate place to insert error
        const googleBtn = document.querySelector('.google-btn');
        if (googleBtn) {
            googleBtn.parentNode.insertBefore(newError, googleBtn);
        }
        
        // Auto-hide
        setTimeout(() => {
            newError.remove();
        }, 5000);
    }
}

window.onload = function() {
    if (typeof google === 'undefined') {
        console.error('Google Sign-In script failed to load');
        handleGoogleInitError();
    }
};

// Improve the error handler
function handleGoogleInitError() {
    const googleBtn = document.querySelector('.google-btn');
    if (googleBtn) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message visible';
        errorMsg.style.textAlign = 'center';
        errorMsg.style.marginTop = '10px';
        errorMsg.textContent = 'Google Sign In is temporarily unavailable. Please use email signup.';
        googleBtn.innerHTML = ''; // Clear the button
        googleBtn.appendChild(errorMsg);
    }
}

// Add error handling for Google Sign In initialization
function handleGoogleInitError() {
    console.error('Google Sign In failed to initialize');
    const googleBtn = document.querySelector('.google-btn');
    if (googleBtn) {
        googleBtn.style.display = 'none';
        const errorMsg = document.createElement('p');
        errorMsg.className = 'error-message visible';
        errorMsg.textContent = 'Google Sign In is temporarily unavailable. Please use email signup.';
        googleBtn.parentNode.insertBefore(errorMsg, googleBtn);
    }
}

function initModal() {
    const modalLinks = document.querySelectorAll('.open-modal');
    modalLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const modalId = this.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex'; // Show modal
            }
        });
    });

    const modals = document.querySelectorAll('.modal-backdrop');
    modals.forEach(modal => {
        // Close modal when clicking outside it
        modal.addEventListener('click', function (event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });

        // Close modal using the close button
        const closeButton = modal.querySelector('.modal-close');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                modal.style.display = 'none';
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', initModal);
    </script>
</body>
</html>