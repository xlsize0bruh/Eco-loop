<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrade — Sign in</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .grid-fade {
            background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, #000 30%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, #000 30%, transparent 100%);
        }
    </style>
</head>
<body class="bg-neutral-950 text-neutral-100 font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-white selection:text-neutral-950">

    <div class="absolute inset-0 grid-fade"></div>

    <div class="relative w-full max-w-sm mx-5">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-2 text-neutral-500 hover:text-neutral-300 mb-8 text-sm transition-colors">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to home
            </a>
            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mx-auto mb-5">
                <i class="fa-solid fa-leaf text-neutral-950"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight" id="form-title">Welcome back</h2>
            <p class="text-neutral-400 mt-2 text-sm" id="form-subtitle">Sign in to continue to EcoTrade.</p>
        </div>

        <div class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-7">
            <form id="auth-form" class="space-y-4">
                <div>
                    <label for="username" class="block text-xs font-medium text-neutral-400 mb-1.5">Username</label>
                    <input type="text" id="username" required class="block w-full px-3.5 py-2.5 bg-neutral-950 border border-neutral-800 rounded-lg text-white text-sm placeholder-neutral-600 focus:outline-none focus:border-neutral-700">
                </div>

                <div id="pincode-group" class="hidden">
                    <label for="pincode" class="block text-xs font-medium text-neutral-400 mb-1.5">Pincode (ZIP)</label>
                    <input type="text" id="pincode" class="block w-full px-3.5 py-2.5 bg-neutral-950 border border-neutral-800 rounded-lg text-white text-sm placeholder-neutral-600 focus:outline-none focus:border-neutral-700">
                    <p class="text-[11px] text-neutral-600 mt-1.5">This ensures you only see local trades.</p>
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-neutral-400 mb-1.5">Password</label>
                    <input type="password" id="password" required class="block w-full px-3.5 py-2.5 bg-neutral-950 border border-neutral-800 rounded-lg text-white text-sm placeholder-neutral-600 focus:outline-none focus:border-neutral-700">
                </div>

                <div id="error-msg" class="text-neutral-300 text-sm hidden bg-neutral-800/60 border border-neutral-700 p-3 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-neutral-400"></i>
                    <span id="error-text"></span>
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-2.5 px-4 rounded-lg text-sm font-semibold text-neutral-950 bg-white hover:bg-neutral-200 transition-colors">
                    <span id="btn-text">Sign in</span>
                </button>
            </form>
        </div>

        <div class="mt-6 text-center text-sm text-neutral-500">
            <span id="toggle-text">Don't have an account?</span>
            <button type="button" id="toggle-btn" class="font-medium text-white hover:text-neutral-300 transition-colors ml-1">Sign up</button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        let isSignup = urlParams.get('signup') === '1';

        const formTitle = document.getElementById('form-title');
        const formSubtitle = document.getElementById('form-subtitle');
        const pincodeGroup = document.getElementById('pincode-group');
        const btnText = document.getElementById('btn-text');
        const toggleText = document.getElementById('toggle-text');
        const toggleBtn = document.getElementById('toggle-btn');
        const pincodeInput = document.getElementById('pincode');
        const errorMsg = document.getElementById('error-msg');
        const errorText = document.getElementById('error-text');
        const authForm = document.getElementById('auth-form');

        function updateUI() {
            if (isSignup) {
                formTitle.textContent = 'Create account';
                formSubtitle.textContent = 'Join EcoTrade and start trading locally.';
                pincodeGroup.classList.remove('hidden');
                pincodeInput.required = true;
                btnText.textContent = 'Sign up';
                toggleText.textContent = 'Already have an account?';
                toggleBtn.textContent = 'Sign in';
            } else {
                formTitle.textContent = 'Welcome back';
                formSubtitle.textContent = 'Sign in to continue to EcoTrade.';
                pincodeGroup.classList.add('hidden');
                pincodeInput.required = false;
                btnText.textContent = 'Sign in';
                toggleText.textContent = "Don't have an account?";
                toggleBtn.textContent = 'Sign up';
            }
            errorMsg.classList.add('hidden');
        }

        toggleBtn.addEventListener('click', () => {
            isSignup = !isSignup;
            updateUI();
        });

        authForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMsg.classList.add('hidden');

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const pincode = document.getElementById('pincode').value;

            const action = isSignup ? 'register' : 'login';
            const payload = { username, password };
            if (isSignup) payload.pincode = pincode;

            try {
                const res = await fetch(`api/auth.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    errorText.textContent = data.error || 'Authentication failed.';
                    errorMsg.classList.remove('hidden');
                }
            } catch (err) {
                errorText.textContent = 'Network error. Please try again.';
                errorMsg.classList.remove('hidden');
            }
        });

        updateUI();
    </script>
</body>
</html>
