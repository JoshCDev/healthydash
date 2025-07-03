<?php
require_once __DIR__ . '/includes/config.php';

// Session already started by api/index.php
// Only start session if not already started (for direct access)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Security-Policy: frame-ancestors 'self' https://accounts.google.com");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /menu.php');
    exit;
}

// Use the database connection from config
$db = Database::getInstance();

// Handle email login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_login'])) {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    // Basic validation
    if (empty($identifier)) {
        $errors['identifier'] = "Email or username is required";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
                // Log successful login
                $stmt = $db->prepare("
                    INSERT INTO auth_logs (
                        user_id,
                        action,
                        ip_address,
                        user_agent
                    ) VALUES (?, 'EMAIL_LOGIN', ?, ?)
                ");
                $stmt->execute([
                    $user['user_id'],
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                ]);
                
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['auth_time'] = time();
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $stmt = $db->prepare("
                        INSERT INTO remember_tokens (
                            user_id,
                            token,
                            expires_at,
                            created_at
                        ) VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['user_id'],
                        password_hash($token, PASSWORD_DEFAULT),
                        $expires
                    ]);
                    
                    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
                    setcookie('user_id', $user['user_id'], strtotime('+30 days'), '/', '', true, false);
                }
                
                header('Location: /menu.php');
                exit();
            } else {
                if (!$user) {
                    $errors['login'] = "Account not found. Please check your email/username or sign up";
                } else if (!$user['password_hash']) {
                    $errors['login'] = "This account uses Google Sign-In. Please use the Google button below";
                } else {
                    $errors['login'] = "Invalid password";
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors['login'] = "An error occurred. Please try again.";
        }
    }
}

// Handle Google Sign-In (same as before)
if (isset($_POST['credential'])) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID]);
        
        try {
            $payload = $client->verifyIdToken($_POST['credential']);
        } catch (Exception $e) {
            error_log("Token verification failed: " . $e->getMessage());
            throw new Exception("Invalid authentication token.");
        }
        
        if ($payload) {
            $email = filter_var($payload['email'], FILTER_VALIDATE_EMAIL);
            if (!$email || !$payload['email_verified']) {
                throw new Exception("Invalid or unverified email from Google authentication.");
            }

            try {
                $db->beginTransaction();

                // Check existing user
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Log the login
                    $stmt = $db->prepare("
                        INSERT INTO auth_logs (
                            user_id,
                            action,
                            ip_address,
                            user_agent
                        ) VALUES (?, 'GOOGLE_LOGIN', ?, ?)
                    ");
                    $stmt->execute([
                        $user['user_id'],
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_USER_AGENT']
                    ]);
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['auth_time'] = time();
                    
                    $db->commit();
                    
                    header('Location: /menu.php');
                    exit();
                } else {
                    // Redirect to signup for new users
                    header('Location: /signup.php');
                    exit();
                }
            } catch (Exception $e) {
                $db->rollBack();
                throw new Exception("Database error during login.");
            }
        } else {
            throw new Exception("Invalid authentication response.");
        }
    } catch (Exception $e) {
        error_log("Google auth error: " . $e->getMessage());
        $errors['google'] = "Authentication failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Login - HealthyDash</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
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
            align-self: flex-start;
        }
        .back-btn:hover {
            opacity: 1;
        }
        .back-btn svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #1f2937;
            font-size: 32px;
            font-family: 'Souce Serif';
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
            width: 100%;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #1f2937;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #94a3b8;
            outline: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            width: 20px;
            height: 20px;
            opacity: 0.6;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover {
            opacity: 1;
        }
        .password-toggle img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
        .error-message {
            color: #ff3b30;
            font-size: 12px;
            margin-top: 8px;
            margin-left: 4px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            line-height: 1.4;
        }
        .error-message.visible {
            display: block;
            opacity: 1;
        }
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0 25px;
            padding: 0 4px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            background-color: white;
        }
        .remember-me span {
            color: #1f2937;
            font-size: 14px;
        }
        .forgot-password {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        .forgot-password:hover {
            opacity: 0.8;
            text-decoration: none;
            color: #94a3b8;
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            background-color: #567733;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 600;
            color:rgb(243, 239, 236);
            font-size: 16px;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.3s ease;
            margin: 10px 0 20px;
        }
        .login-btn:not(:disabled) {
            opacity: 1;
            cursor: pointer;
        }
        .login-btn:disabled {
            cursor: not-allowed;
        }
        .login-btn.active {
            opacity: 1;
        }
        .login-btn.loading {
            position: relative;
            padding-left: 40px;
            opacity: 0.8;
            pointer-events: none;
        }
        .login-btn.loading::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-top-color: #1f2937;
            border-radius: 50%;
            animation: spinner 0.8s linear infinite;
        }
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #e2e8f0;
            margin: 0 15px;
        }
        .divider span {
            color: #666;
            font-size: 14px;
            padding: 0 10px;
            background-color: white;
        }
        .google-btn {
            width: 100%;
            margin-top: 15px;
            display: flex;
            justify-content: center;
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

        <h1>Welcome Back</h1>
        
        <?php if (isset($errors['login'])): ?>
            <div class="error-message visible" style="text-align: center; margin-bottom: 20px;">
                <?php echo htmlspecialchars($errors['login']); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="" novalidate>
            <input type="hidden" name="email_login" value="1">
            
            <div class="form-group">
                <input type="text" 
                       id="identifier" 
                       name="identifier" 
                       placeholder="Email or Username*" 
                       value="<?php echo htmlspecialchars($identifier ?? ''); ?>"
                       autocomplete="off"
                       required>
                <div class="error-message" id="identifier-error"></div>
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
                <div class="error-message" id="password-error"></div>
            </div>

            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Remember me</span>
                </label>
                <a href="forgot.php" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn" id="loginButton" disabled>
                Login with Email
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
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="outline"
                     data-text="continue_with"
                     data-size="large"
                     data-width="300"
                     data-logo_alignment="left">
                </div>
            </div>

            <p class="signup-prompt">
                Don't have an account? <a href="signup.php">Sign up</a>
            </p>

            <div class="logo">
                <img src="/assets/images/healthydashlogo.png" alt="HealthyDash logo">
            </div>
        </form>
    </div>

    <script>
    let isCheckingUser = false;
    let checkUserTimeout = null;

    // Validation state
    const validationState = {
        identifier: { touched: false, valid: false, checked: false },
        password: { touched: false, valid: false }
    };

    // Elements
    const form = document.getElementById('loginForm');
    const identifierInput = document.getElementById('identifier');
    const passwordInput = document.getElementById('password');
    const loginButton = document.getElementById('loginButton');

    // User existence checker
    async function checkUserExists(identifier) {
        if (!identifier.trim()) return;
        
        isCheckingUser = true;
        try {
            const formData = new FormData();
            formData.append('type', 'user_check');
            formData.append('value', identifier);

            const response = await fetch('/check-availability.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.exists) {
                showError(identifierInput, 'Account not found. Please check your email/username or sign up');
                validationState.identifier.valid = false;
            } else {
                hideError(identifierInput);
                validationState.identifier.valid = true;
            }
        } catch (error) {
            console.error('Error:', error);
            validationState.identifier.valid = false;
        } finally {
            isCheckingUser = false;
            validationState.identifier.checked = true;
            validateForm();
        }
    }

    // Password toggle functionality (unchanged)
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

    // Error handling for individual fields
    function showError(element, message) {
        const errorDiv = document.getElementById(`${element.id}-error`);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.add('visible');
        }
    }

window.onload = function() {
    if (typeof google === 'undefined') {
        console.error('Google Sign-In script failed to load');
        const googleBtn = document.querySelector('.google-btn');
        if (googleBtn) {
            googleBtn.innerHTML = '<div class="error-message visible">Google Sign In is temporarily unavailable. Please use email login.</div>';
        }}}

    function hideError(element) {
        const errorDiv = document.getElementById(`${element.id}-error`);
        if (errorDiv) {
            errorDiv.classList.remove('visible');
            errorDiv.textContent = '';
        }
    }

    // Form validation
    function validateForm() {
        const identifier = identifierInput.value.trim();
        const password = passwordInput.value;

        // Always update password validation state
        validationState.password.valid = password.length >= 1;

        // Enable button if fields are filled
        const isValid = identifier && password;
        loginButton.disabled = !isValid;
        
        if (isValid) {
            loginButton.classList.add('active');
        } else {
            loginButton.classList.remove('active');
        }
    }

    // Input event handlers
    identifierInput.addEventListener('input', () => {
        const value = identifierInput.value.trim();
        hideError(identifierInput);
        validationState.identifier.checked = false;
        
        if (checkUserTimeout) {
            clearTimeout(checkUserTimeout);
        }
        
        if (value) {
            checkUserTimeout = setTimeout(() => {
                checkUserExists(value);
            }, 500);
        }
        
        validateForm();
    });

    identifierInput.addEventListener('blur', () => {
        validationState.identifier.touched = true;
        const value = identifierInput.value.trim();
        
        if (!value) {
            showError(identifierInput, 'Email or username is required');
            validationState.identifier.valid = false;
        } else if (!validationState.identifier.checked) {
            checkUserExists(value);
        }
    });

    passwordInput.addEventListener('input', () => {
        hideError(passwordInput);
        validateForm();
    });

    passwordInput.addEventListener('blur', () => {
        validationState.password.touched = true;
        if (!passwordInput.value) {
            showError(passwordInput, 'Password is required');
            validationState.password.valid = false;
        }
        validateForm();
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        let isValid = true;

        if (!identifierInput.value.trim()) {
            showError(identifierInput, 'Email or username is required');
            isValid = false;
        }

        if (!passwordInput.value) {
            showError(passwordInput, 'Password is required');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        } else {
            loginButton.classList.add('loading');
            loginButton.disabled = true;
            loginButton.textContent = 'Logging in...';
        }
    });

    // Google Sign In callback (unchanged)
    function handleCredentialResponse(response) {
    console.log("Google response received:", response);
    
    try {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
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

    // Initialize form validation
    validateForm();

    // Handle error messages fade out
    const messages = document.querySelectorAll('.error-message.visible');
    if (messages.length > 0) {
        setTimeout(() => {
            messages.forEach(msg => {
                msg.style.opacity = '0';
                setTimeout(() => msg.classList.remove('visible'), 300);
            });
        }, 5000);
    }
</script>
</body>
</html>