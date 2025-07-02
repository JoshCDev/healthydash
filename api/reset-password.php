<?php
session_start();
require_once '../includes/config.php';

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$validToken = false;
$tokenData = null;

// Validate token presence
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: forgot.php');
    exit;
}

$token = trim($_GET['token']);
error_log("Processing reset token: " . $token); // Debug log

try {
    $db = Database::getInstance();
    
    // Check if token exists and is valid
    $stmt = $db->prepare("
        SELECT t.*, u.email, u.username 
        FROM password_reset_tokens t
        JOIN users u ON t.user_id = u.user_id
        WHERE t.token = ? 
        AND t.used = 0 
        AND t.expires_at > NOW()
        AND u.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tokenData) {
        error_log("Token found: " . print_r($tokenData, true));
        error_log("Token expires at: " . $tokenData['expires_at']);
        error_log("Current time: " . date('Y-m-d H:i:s'));
        $validToken = true;
    } else {
        error_log("No valid token found");
        
        // Check why token is invalid
        $stmt = $db->prepare("SELECT * FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $invalidToken = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($invalidToken) {
            if ($invalidToken['used'] == 1) {
                $error = "This reset link has already been used. Please request a new one.";
            } else if (strtotime($invalidToken['expires_at']) < time()) {
                $error = "This reset link has expired. Please request a new one.";
            }
        } else {
            $error = "Invalid reset link. Please request a new one.";
        }
    }

    // Handle password reset submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password)) {
            $error = "Password is required";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = "Password must contain at least one uppercase letter";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = "Password must contain at least one lowercase letter";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = "Password must contain at least one number";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match";
        }
        
        if (!$error) {
            try {
                $db->beginTransaction();

                // Double-check token validity before proceeding
                $stmt = $db->prepare("
                    SELECT id FROM password_reset_tokens 
                    WHERE token = ? AND used = 0 AND expires_at > NOW()
                ");
                $stmt->execute([$token]);
                if (!$stmt->fetch()) {
                    throw new Exception("Token has expired or already been used");
                }

                // Update password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$password_hash, $tokenData['user_id']]);

                // Mark token as used
                $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);

                // Invalidate all remember me tokens
                $stmt = $db->prepare("UPDATE remember_tokens SET used = 1 WHERE user_id = ?");
                $stmt->execute([$tokenData['user_id']]);

                $db->commit();
                $success = "Your password has been reset successfully!";
                header("refresh:3;url=login.php");
                
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again.";
            }
        }
    }
} catch (Exception $e) {
    error_log("Token validation error: " . $e->getMessage());
    $error = "An error occurred. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Set New Password - HealthyDash</title>
    <style>
        body {
            background-color: rgb(252, 248, 245);
            color: #1f2937;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
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
            font-size: 24px;
            font-weight: 500;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
        }

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
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
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

        .strength-dot.active.weak { background-color: #ff4444; }
        .strength-dot.active.fair { background-color: #ffbb33; }
        .strength-dot.active.good { background-color: #00C851; }
        .strength-dot.active.strong { background-color: #007E33; }

        .strength-text {
            color: #666;
            transition: color 0.3s ease;
        }

        .strength-text.weak { color: #ff4444; }
        .strength-text.fair { color: #ffbb33; }
        .strength-text.good { color: #00C851; }
        .strength-text.strong { color: #007E33; }

        .error-message {
            color: #ff3b30;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .error-message.visible {
            display: block;
            opacity: 1;
            margin-left: 4px;
        }

        .success-message {
            background-color: rgba(52, 199, 89, 0.1);
            color: #34c759;
            border-radius: 12px;
            padding: 12px 16px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background-color: #567733;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.3s ease;
            margin: 10px 0 20px;
        }

        .submit-btn:not(:disabled) {
            opacity: 1;
        }

        .submit-btn:disabled {
            cursor: not-allowed;
        }

        .submit-btn.active {
            opacity: 1;
        }

        .submit-btn.loading {
            position: relative;
            padding-left: 40px;
            opacity: 0.8;
            pointer-events: none;
        }

        .submit-btn.loading::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top-color: #1f2937;
            border-radius: 50%;
            animation: spinner 0.8s linear infinite;
        }

        @keyframes spinner {
            to { transform: rotate(360deg); }
        }

        .logo {
            text-align: center;
            margin-top: 30px;
        }

        .logo img {
            max-width: 70%;
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
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$error && !$success): ?>
            <button class="back-btn" onclick="window.history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <h1>Set New Password</h1>
            <p class="subtitle">Please create a strong password<br>for your account.</p>

            <form id="resetForm" method="POST" action="?token=<?php echo htmlspecialchars($token); ?>" novalidate>
                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="New Password*" 
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
                        <span class="strength-text" id="strength-text">Password Strength</span>
                    </div>
                    <div class="error-message" id="password-error"></div>
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                    <input type="password" 
                               id="confirm-password" 
                               name="confirm_password" 
                               placeholder="Confirm New Password*" 
                               required>
                        <button type="button" 
                                class="password-toggle" 
                                onclick="togglePassword('confirm-password')" 
                                aria-label="Toggle password visibility">
                            <img src="/assets/images/eyes_closed.png" 
                                 alt="Toggle password" 
                                 class="toggle-icon" 
                                 id="confirm-password-toggle-icon">
                        </button>
                    </div>
                    <div class="error-message" id="confirm-password-error"></div>
                </div>

                <button type="submit" class="submit-btn" id="submitButton" disabled>
                    Reset Password
                </button>
            </form>

        <?php elseif ($error): ?>
            <h1>Link Invalid</h1>
            <p class="subtitle">
                <?php echo htmlspecialchars($error); ?><br>
                Please <a href="forgot.php" style="color: #ffd700; text-decoration: none;">request a new one</a>.
            </p>
        <?php else: ?>
            <h1>Password Reset</h1>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?><br>
                <small style="display: block; margin-top: 8px; opacity: 0.8;">
                    Redirecting you to login...
                </small>
            </div>
        <?php endif; ?>

        <div class="logo">
            <img src="/assets/images/healthydashlogo.png" alt="HealthyDash logo">
        </div>
    </div>

    <script>
        // Elements
        const form = document.getElementById('resetForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const submitButton = document.getElementById('submitButton');
        const passwordError = document.getElementById('password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');

        // Password toggle functionality
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
            
            return score >= 2;
        }

        // Form validation
        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const hasErrors = document.querySelector('.error-message.visible');
            const isPasswordValid = checkPasswordStrength(password);
            const doPasswordsMatch = password === confirmPassword && confirmPassword;

            submitButton.disabled = !isPasswordValid || !doPasswordsMatch || hasErrors;
            if (!submitButton.disabled) {
                submitButton.classList.add('active');
            } else {
                submitButton.classList.remove('active');
            }
        }

        // Event listeners
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                hideError(passwordError);
                checkPasswordStrength(this.value);
                validateForm();
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                hideError(confirmPasswordError);
                if (this.value && this.value !== passwordInput.value) {
                    showError(confirmPasswordError, 'Passwords do not match');
                }
                validateForm();
            });
        }

        function showError(element, message) {
            element.textContent = message;
            element.classList.add('visible');
        }

        function hideError(element) {
            element.classList.remove('visible');
        }

        // Form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (!password || !confirmPassword) {
                    e.preventDefault();
                    return;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    showError(confirmPasswordError, 'Passwords do not match');
                    return;
                }

                if (!checkPasswordStrength(password)) {
                    e.preventDefault();
                    return;
                }

                submitButton.classList.add('loading');
                submitButton.textContent = 'Resetting Password...';
            });

            // Initialize validation
            validateForm();
        }
    </script>
</body>
</html>