<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/PasswordReset.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $passwordReset = new PasswordReset();
        $identifier = trim($_POST['identifier'] ?? '');

        if (empty($identifier)) {
            $error = "Please enter your email or username";
        } else {
            // Find user by email or username
            $user = $passwordReset->findUser($identifier);

            if ($user) {
                // Generate and send reset token
                $token = $passwordReset->createToken($user['user_id']);
                
                if ($token && $passwordReset->sendResetEmail($user['email'], $token, $user['username'])) {
                    $success = "Password reset instructions have been sent to your email";
                } else {
                    $error = "Failed to send reset email. Please try again.";
                }
            } else {
                // For security, don't reveal if email/username exists
                $success = "If an account exists, you will receive password reset instructions";
            }
        }
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        $error = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Reset Password - HealthyDash</title>
    <link rel="stylesheet" href="../assets/font/stylesheet.css">
    <style>
        *{
            font-family: 'Mona Sans';
        }
        body {
            background-color:rgb(252, 248, 245);
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
            font-family: "Source Serif";
            text-align: center;
            margin-bottom: 15px;
            color: #1f2937;
            font-size: 32px;
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

        input {
            width: 100%;
            padding: 12px;
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

        .error-message {
            color: #ff3b30;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            margin-left: 4px;
        }

        .error-message.visible {
            display: block;
            opacity: 1;
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
            cursor: pointer;
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

        .login-prompt {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-prompt a {
            color: #567733;
            text-decoration: none;
            margin-left: 5px;
            font-weight: 500;
        }

        .login-prompt a:hover {
            opacity: 0.8;
        }

        .logo {
            text-align: center;
            margin-top: 40px;
        }

        .logo img {
            width: 150px;
            height: auto;
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            input {
                font-size: 14px;
            }
            .subtitle {
                font-size: 13px;
            }
            .logo img {
                width: 130px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="back-btn" onclick="window.location.href='login.php'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <h1>Reset Password</h1>
        <p class="subtitle">Enter your email or username and we'll send you<br>instructions to reset your password.</p>

        <?php if ($error): ?>
            <div class="error-message visible"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form id="resetForm" method="POST" action="" novalidate>
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

            <button type="submit" 
                    class="submit-btn" 
                    id="submitButton" 
                    disabled>
                Send Reset Link
            </button>
        </form>

        <p class="login-prompt">
            Remember your password? <a href="login.php">Login</a>
        </p>

        <div class="logo">
            <img src="../assets/images/healthydashlogo.png" alt="HealthyDash logo">
        </div>
    </div>

    <script>
        // Elements
        const form = document.getElementById('resetForm');
        const identifierInput = document.getElementById('identifier');
        const submitButton = document.getElementById('submitButton');
        const identifierError = document.getElementById('identifier-error');

        // Validation with debounce
        let timeout = null;
        function validateInput() {
            const identifier = identifierInput.value.trim();

            if (timeout) {
                clearTimeout(timeout);
            }

            timeout = setTimeout(() => {
                if (!identifier) {
                    showError('Email or username is required');
                    submitButton.disabled = true;
                } else {
                    hideError();
                    submitButton.disabled = false;
                    submitButton.classList.add('active');
                }
            }, 300);
        }

        // Error handling
        function showError(message) {
            identifierError.textContent = message;
            identifierError.classList.add('visible');
        }

        function hideError() {
            identifierError.classList.remove('visible');
        }

        // Event listeners
        identifierInput.addEventListener('input', validateInput);
        identifierInput.addEventListener('blur', validateInput);

        // Form submission
        form.addEventListener('submit', function(e) {
            const identifier = identifierInput.value.trim();
            
            if (!identifier) {
                e.preventDefault();
                showError('Email or username is required');
                return;
            }

            // Show loading state
            submitButton.disabled = true;
            submitButton.classList.add('loading');
            submitButton.textContent = 'Sending...';
        });

        // Handle success/error message fade out
        const messages = document.querySelectorAll('.success-message, .error-message.visible');
        if (messages.length > 0) {
            setTimeout(() => {
                messages.forEach(msg => {
                    msg.style.opacity = '0';
                    setTimeout(() => {
                        if (msg.classList.contains('error-message')) {
                            msg.classList.remove('visible');
                        } else {
                            msg.remove();
                        }
                    }, 300);
                });
            }, 5000);
        }
    </script>
</body>
</html>