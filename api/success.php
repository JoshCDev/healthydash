<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/font/stylesheet.css">
    <title>Order Success</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Mona Sans';
    }

    @keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

body {
    font-family: 'Mona Sans';
    background-color: rgb(252, 248, 245);
    color: #1f2937;
    min-height: 100vh;
    margin: 0;
}

.container {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: rgb(252, 248, 245);
    padding: 20px;
    opacity: 0;
    animation: fadeIn 0.6s ease-out forwards;
}

/* Header */
header {
    padding: 1rem;
}

header h1 {
    font-size: 32px;
    font-weight: 500;
    color: #1f2937;
    font-family: 'Source Serif';
    letter-spacing: 0.05em;
    text-align: center;
    margin-bottom: 30px;
}

/* Main Content */
main {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    margin-top: -4rem;
}

/* Success Icon */
.success-icon {
    width: 80px;
    height: 80px;
    border: 2px solid #1f2937;
    border-radius: 50%;
    position: relative;
    margin-bottom: 1rem;
}

.checkmark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg);
    width: 24px;
    height: 12px;
    border-bottom: 2px solid #1f2937;
    border-right: 2px solid #1f2937;
    margin-top: -4px;
}

/* Success Text */
.success-text {
    font-size: 14px;
    color: #1f2937;
    opacity: 0;
    animation: fadeIn 0.6s ease-out 1s forwards;
}

/* Dashboard Button */
.dashboard-btn {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    color: #1f2937;
    transition: all 0.3s ease;
}

.dashboard-btn:hover {
    background: rgb(234, 240, 226);
}

.continue-btn {
    background: #567733;
    color: #e2e8f0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px;
    width: 100%;
    cursor: pointer;
    font-size: 16px;
    margin-top: 1rem;
    font-weight: bold;
    transition: all 0.3s ease;
    opacity: 0;
    animation: fadeIn 0.6s ease-out 1.2s forwards;
}

.continue-btn:hover {
    opacity: 0.9;
}

.continue-btn:active:not(:disabled) {
    transform: scale(0.98);
}

/* Responsive styles */
@media (max-width: 480px) {
    .container { 
        padding: 15px; 
    }
    .success-text { 
        font-size: 14px; 
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    .container { 
        max-width: 80%; 
    }
}

h3{
    font-size: 32px;
    opacity: 0;
    animation: fadeIn 0.6s ease-out 0.8s forwards;
}

.order-success{
    margin: 32px 0;
    text-align: center;
}

.success-animation {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fa;
    opacity: 0;
    animation: fadeIn 0.6s ease-out 0.3s forwards;
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    position: relative;
    background-color: #4CAF50;
    border-radius: 50%;
    animation: scale-up 0.3s ease-in-out;
}

.checkmark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg) scale(0);
    display: block;
    width: 24px;
    height: 48px;
    border-bottom: 8px solid white;
    border-right: 8px solid white;
    animation: check 0.4s ease-in-out 0.3s forwards;
}

@keyframes scale-up {
    0% {
        transform: scale(0);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes check {
    0% {
        transform: translate(-50%, -50%) rotate(45deg) scale(0);
    }
    100% {
        transform: translate(-50%, -50%) rotate(45deg) scale(1);
    }
}
    </style>
</head>
<body>
    <div class="container">
        <main>
        <div class="success-animation">
        <div class="checkmark-circle">
            <div class="checkmark"></div>
        </div>
    </div>
            <div class="order-success">
            <h3>Order Success!</h3>
            <p class="success-text">Thanks for ordering</p>
        </div>
            <a href="menu.php"><button class="continue-btn">Continue</button></a>
        </main>
    </div>
    <script>
        function goToDashboard() {
    // Add your navigation logic here
    console.log('Navigating to dashboard...');
    // Example: window.location.href = '/dashboard';
}

// Optional: Add animation when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const successIcon = document.querySelector('.success-icon');
    const successText = document.querySelector('.success-text');
    
    // Add fade-in effect
    successIcon.style.opacity = '0';
    successText.style.opacity = '0';
    
    setTimeout(() => {
        successIcon.style.transition = 'opacity 0.5s ease-in-out';
        successText.style.transition = 'opacity 0.5s ease-in-out';
        successIcon.style.opacity = '1';
        successText.style.opacity = '1';
    }, 100);
});
    </script>
</body>
</html>