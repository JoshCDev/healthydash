<?php
session_start();
require_once '../includes/otp_handler.php';

// Redirect if no signup data
if (!isset($_SESSION['signup_data']) || !isset($_SESSION['otp_id'])) {
    header('Location: signup.php');
    exit;
}

// Security check - expire signup session before 3 minutes
if (time() - $_SESSION['signup_data']['timestamp'] > 170) {
    session_unset();
    header('Location: signup.php?expired=1');
    exit;
}

// Database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=healthydash", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$otpHandler = new OTPHandler();
$message = '';
$error = '';
$email = $_SESSION['signup_data']['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp_code = $_POST['otp_code'];
        
        if (isset($_SESSION['otp_id'])) {
            if ($otpHandler->verifyOTP($_SESSION['otp_id'], $otp_code)) {
                try {
                    $db->beginTransaction();
                    
                    // Hash password with Argon2id
                    $password_hash = password_hash($_SESSION['signup_data']['password'], PASSWORD_ARGON2ID, [
                        'memory_cost' => 1024 * 64,
                        'time_cost'   => 4,
                        'threads'     => 2
                    ]);
                
                    if ($password_hash === false) {
                        throw new Exception("Password hashing failed");
                    }
                    
                    // Create user with username - removed initial_data_complete
                    $stmt = $db->prepare("INSERT INTO users (
                        email, 
                        username,
                        password_hash, 
                        google_auth, 
                        created_at, 
                        is_verified
                    ) VALUES (?, ?, ?, 0, NOW(), 1)");
                
                    $stmt->execute([
                        $_SESSION['signup_data']['email'],
                        $_SESSION['signup_data']['username'],
                        $password_hash
                    ]);
                    
                    $userId = $db->lastInsertId();
                    
                    // Set user session
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['auth_time'] = time();
                    
                    // Clear signup data
                    unset($_SESSION['signup_data']);
                    unset($_SESSION['otp_id']);
                    
                    $db->commit();
                    
                    // Redirect to menu instead of questions
                    header('Location: menu.php');
                    exit();
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log("Account creation error: " . $e->getMessage());  // Added for debugging
                    $error = "An error occurred. Please try again.";
                }
            } else {
                $error = "Invalid verification code. Please try again.";
            }
        } else {
            $error = "Session expired. Please restart signup process.";
        }
    }
    
    if (isset($_POST['resend_otp'])) {
        if ($otpHandler->canResendOTP()) {
            $otp_id = $otpHandler->sendOTP($email);
            if ($otp_id) {
                $_SESSION['otp_id'] = $otp_id;
                $_SESSION['last_otp_time'] = time();
                $message = "New verification code sent!";
            } else {
                $error = "Failed to send new code. Please try again.";
            }
        } else {
            $error = "Please wait before requesting a new code.";
        }
    }
}

$remainingDelay = $otpHandler->getRemainingDelay();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Verify Your Email - HealthyDash</title>
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

        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
            font-family: 'source serif';
            font-weight: 500;
            color: #1f2937;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
        }

        .subtitle strong {
            color: #1f2937;
            font-weight: 500;
        }

        .otp-input {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .otp-digit {
            width: 45px;
            height: 55px;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #1f2937;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s ease;
            -webkit-appearance: none;
            -moz-appearance: textfield;
            caret-color: #94a3b8;
        }

        .otp-digit::-webkit-outer-spin-button,
        .otp-digit::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .otp-digit:focus {
            border-color: #94a3b8;
            outline: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        .otp-digit.filled {
            border-color: #94a3b8;
            background-color: white;
            animation: pulse 0.2s ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .verify-btn {
            width: 100%;
            padding: 16px;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-weight: bold;
            color: #1f2937;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .verify-btn:not(:disabled) {
            opacity: 1;
        }

        .verify-btn:disabled {
            cursor: not-allowed;
        }

        .verify-btn.loading {
            padding-left: 40px;
        }

        .verify-btn.loading::before {
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

        .resend-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 25px;
        }

        a{
            color: #567733;
            font-weight: 500;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 4px 8px;
            margin-left: 4px;
            border-radius: 4px;
            display: inline-block;
        }

        .resend-link:not(.disabled):hover {
            background-color: rgba(148, 163, 184, 0.1);
            text-decoration: none !important;
        }

        .resend-link.disabled {
            text-decoration: none !important;
            opacity: 0.5;
            cursor: not-allowed;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success {
            background-color: rgba(52, 199, 89, 0.1);
            color: #34c759;
        }

        .error {
            background-color: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }

        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .otp-digit {
                width: 40px;
                height: 50px;
                font-size: 20px;
            }


            .subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="back-btn" onclick="window.location.href='signup.php'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <h1>Verify Your Email</h1>
        <p class="subtitle">We've sent a verification code to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" id="otpForm">
            <div class="otp-input">
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="number" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>
            <input type="hidden" name="verify_otp" value="1">
            <input type="hidden" name="otp_code" id="otp_code">
            <button type="submit" class="verify-btn" disabled>Verify Code</button>
        </form>

        <p class="resend-text">
            Didn't receive the code? 
            <a href="#" class="resend-link<?php echo $remainingDelay > 0 ? ' disabled' : ''; ?>" 
               id="resendLink" 
               onclick="return handleResend(event)">
                Resend<?php echo $remainingDelay > 0 ? " in {$remainingDelay}s" : ''; ?>
            </a>
        </p>

        <form id="resendForm" method="POST" style="display: none;">
            <input type="hidden" name="resend_otp" value="1">
        </form>
    </div>
    <script>
        // Main elements
        const otpForm = document.getElementById('otpForm');
        const otpInputs = document.querySelectorAll('.otp-digit');
        const verifyButton = document.querySelector('.verify-btn');
        const hiddenInput = document.getElementById('otp_code');
        let resendTimer = null;

        // Initialize focus on first input
        window.addEventListener('load', () => {
            otpInputs[0].focus();
        });

        // OTP input handling
        otpInputs.forEach((input, index) => {
            // Handle input
            input.addEventListener('input', function(e) {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');

                // Ensure only one digit
                if (this.value.length > 1) {
                    this.value = this.value[0];
                }

                // Add filled class for styling
                if (this.value.length === 1) {
                    this.classList.add('filled');
                    // Auto focus next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }

                updateVerifyButton();
            });

            // Handle keydown
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    // If current input is empty and backspace is pressed, go to previous input
                    if (this.value === '' && index > 0 && e.key === 'Backspace') {
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].value = '';
                        otpInputs[index - 1].classList.remove('filled');
                    } else {
                        this.value = '';
                        this.classList.remove('filled');
                    }
                    e.preventDefault();
                    updateVerifyButton();
                }
                
                // Arrow key navigation
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData)
                    .getData('text')
                    .replace(/[^0-9]/g, '')
                    .split('');

                // Reset all inputs
                otpInputs.forEach(input => {
                    input.value = '';
                    input.classList.remove('filled');
                });

                // Fill inputs with pasted data
                pastedData.forEach((digit, i) => {
                    if (i < otpInputs.length) {
                        otpInputs[i].value = digit;
                        otpInputs[i].classList.add('filled');
                    }
                });

                // Focus last filled input or next empty one
                const lastFilledIndex = Math.min(pastedData.length, otpInputs.length) - 1;
                if (lastFilledIndex >= 0) {
                    otpInputs[lastFilledIndex].focus();
                }

                updateVerifyButton();
            });

            // Select all on focus
            input.addEventListener('focus', function() {
                this.select();
            });
        });

        // Update verify button state
        function updateVerifyButton() {
            const otp = Array.from(otpInputs)
                .map(input => input.value)
                .join('');
            
            verifyButton.disabled = otp.length !== 6;
            hiddenInput.value = otp;

            // Add/remove active class for styling
            if (otp.length === 6) {
                verifyButton.classList.add('active');
            } else {
                verifyButton.classList.remove('active');
            }
        }

        // Resend timer handling
        let remainingDelay = <?php echo $remainingDelay; ?>;
        const resendLink = document.getElementById('resendLink');

        function updateResendTimer() {
            if (remainingDelay > 0) {
                resendLink.classList.add('disabled');
                resendLink.textContent = `Resend in ${remainingDelay}s`;
                remainingDelay--;
                resendTimer = setTimeout(updateResendTimer, 1000);
            } else {
                resendLink.classList.remove('disabled');
                resendLink.textContent = 'Resend';
            }
        }

        function handleResend(event) {
            event.preventDefault();
            if (remainingDelay === 0) {
                // Clear all inputs
                otpInputs.forEach(input => {
                    input.value = '';
                    input.classList.remove('filled');
                });
                otpInputs[0].focus();

                // Reset verify button
                verifyButton.disabled = true;
                verifyButton.classList.remove('active');

                // Submit resend form
                document.getElementById('resendForm').submit();
                remainingDelay = 60;
                updateResendTimer();
            }
            return false;
        }

        if (remainingDelay > 0) {
            updateResendTimer();
        }

        // Form submission
        otpForm.addEventListener('submit', function(e) {
            const otp = hiddenInput.value;
            
            if (otp.length !== 6) {
                e.preventDefault();
                return;
            }

            // Show loading state
            verifyButton.disabled = true;
            verifyButton.classList.add('loading');
            verifyButton.textContent = 'Verifying...';

            // Add shake animation if there's an error message
            const errorMessage = document.querySelector('.message.error');
            if (errorMessage) {
                errorMessage.classList.add('shake');
            }
        });

        // Handle error messages fade out
        const messages = document.querySelectorAll('.message');
        if (messages.length > 0) {
            setTimeout(() => {
                messages.forEach(msg => {
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 300);
                });
            }, 5000);
        }

        // Mobile keyboard optimization
        if ('virtualKeyboard' in navigator) {
            navigator.virtualKeyboard.overlaysContent = true;
        }

        // Clean up
        window.addEventListener('unload', () => {
            if (resendTimer) {
                clearTimeout(resendTimer);
            }
        });
    </script>
</body>
</html>