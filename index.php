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
    <title>EcoTrade — Local Item Trading</title>
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
        html { scroll-behavior: smooth; }
        .grid-fade {
            background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 40%, transparent 100%);
        }
    </style>
</head>
<body class="bg-neutral-950 text-neutral-100 font-sans antialiased selection:bg-white selection:text-neutral-950">
    <nav class="border-b border-neutral-900 bg-neutral-950/70 backdrop-blur-xl fixed w-full z-50">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-neutral-950 text-sm"></i>
                    </div>
                    <span class="font-semibold text-[15px] tracking-tight">EcoTrade</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="login.php" class="text-neutral-400 hover:text-white px-3.5 py-2 rounded-lg text-sm font-medium transition-colors">Sign in</a>
                    <a href="login.php?signup=1" class="bg-white hover:bg-neutral-200 text-neutral-950 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Get started</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="relative pt-40 pb-28 overflow-hidden">
        <div class="absolute inset-0 grid-fade"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-neutral-800/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="relative max-w-3xl mx-auto px-5 sm:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-8 rounded-full border border-neutral-800 bg-neutral-900/50 text-xs text-neutral-400">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                Hyper-local, waste-free trading
            </div>
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.05] mb-6">
                Trade local.<br>
                <span class="text-neutral-500">Save global.</span>
            </h1>
            <p class="max-w-xl mx-auto text-lg text-neutral-400 mb-10 leading-relaxed">
                Turn your unused items into something you actually need. Connect with neighbors in your pincode, trade securely, and build a trusted reputation.
            </p>
            <div class="flex justify-center gap-3">
                <a href="login.php?signup=1" class="bg-white hover:bg-neutral-200 text-neutral-950 px-6 py-3 rounded-xl text-[15px] font-semibold transition-all">
                    Start trading
                </a>
                <a href="#features" class="bg-neutral-900 hover:bg-neutral-800 border border-neutral-800 text-neutral-200 px-6 py-3 rounded-xl text-[15px] font-medium transition-all">
                    Learn more
                </a>
            </div>
        </div>
    </header>

    <section id="features" class="py-24 border-t border-neutral-900">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-xl mb-16">
                <h2 class="text-3xl font-bold tracking-tight mb-3">Why EcoTrade?</h2>
                <p class="text-neutral-400 text-lg">A simple, minimal way to reduce waste and get what you want.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="group bg-neutral-900/60 p-7 rounded-2xl border border-neutral-800 hover:border-neutral-700 transition-colors">
                    <div class="w-11 h-11 bg-neutral-800 rounded-xl flex items-center justify-center mb-5 group-hover:bg-white transition-colors">
                        <i class="fa-solid fa-location-dot text-neutral-300 group-hover:text-neutral-950 transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Hyper-local matching</h3>
                    <p class="text-neutral-400 text-sm leading-relaxed">Sign up with your pincode. We only show items from people in your immediate neighborhood, so physical trades are effortless.</p>
                </div>
                <div class="group bg-neutral-900/60 p-7 rounded-2xl border border-neutral-800 hover:border-neutral-700 transition-colors">
                    <div class="w-11 h-11 bg-neutral-800 rounded-xl flex items-center justify-center mb-5 group-hover:bg-white transition-colors">
                        <i class="fa-solid fa-comments text-neutral-300 group-hover:text-neutral-950 transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Secure trading &amp; chat</h3>
                    <p class="text-neutral-400 text-sm leading-relaxed">Propose trades, chat in real time, and agree on a meeting spot to exchange items. Every trade builds your reputation.</p>
                </div>
                <div class="group bg-neutral-900/60 p-7 rounded-2xl border border-neutral-800 hover:border-neutral-700 transition-colors">
                    <div class="w-11 h-11 bg-neutral-800 rounded-xl flex items-center justify-center mb-5 group-hover:bg-white transition-colors">
                        <i class="fa-solid fa-trophy text-neutral-300 group-hover:text-neutral-950 transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Monthly champions</h3>
                    <p class="text-neutral-400 text-sm leading-relaxed">The trader with the best reputation in each pincode tops the leaderboard and wins a monthly eco-gift.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 border-t border-neutral-900">
        <div class="max-w-3xl mx-auto px-5 sm:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight mb-5">Ready to trade?</h2>
            <p class="text-neutral-400 text-lg mb-9">Join your neighborhood marketplace in under a minute.</p>
            <a href="login.php?signup=1" class="inline-block bg-white hover:bg-neutral-200 text-neutral-950 px-7 py-3.5 rounded-xl text-[15px] font-semibold transition-all">
                Create your account
            </a>
        </div>
    </section>

    <footer class="border-t border-neutral-900 py-10">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 flex items-center justify-between text-sm text-neutral-500">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-leaf text-neutral-400"></i>
                <span>EcoTrade</span>
            </div>
            <span>Trade local, save global.</span>
        </div>
    </footer>
</body>
</html>
