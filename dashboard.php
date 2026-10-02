<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLoop Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        .toast-enter { animation: toastIn .2s ease-out; }
        .toast-exit { animation: toastOut .2s ease-in; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(8px); }
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-neutral-950 text-neutral-100 min-h-screen antialiased font-sans selection:bg-white selection:text-neutral-950">
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>

    <nav class="border-b border-neutral-900 bg-neutral-950/80 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-neutral-950"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-[0.2em] text-neutral-500">EcoLoop</div>
                        <div class="text-sm font-semibold text-white">Neighborhood trading</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-900 px-3 py-1.5 text-xs text-neutral-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Pincode: <span class="font-medium text-white"><?php echo htmlspecialchars($user['pincode'] ?? 'Unknown'); ?></span>
                    </div>
                    <button id="logout-btn" class="rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-sm text-neutral-200 hover:bg-neutral-800 transition-colors">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="mb-8 rounded-3xl border border-neutral-900 bg-gradient-to-br from-neutral-900 via-neutral-900 to-neutral-950 p-6">
            <div class="flex flex-col lg:flex-row justify-between gap-5">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-neutral-500 mb-3">Dashboard</p>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Welcome back, <?php echo htmlspecialchars($user['username'] ?? 'Trader'); ?></h1>
                </div>

                <div class="grid grid-cols-3 gap-3 min-w-[280px]">
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-950/60 p-3">
                        <div class="text-[11px] uppercase tracking-[0.2em] text-neutral-500">Positive</div>
                        <div id="header-pos" class="mt-2 text-xl font-bold text-emerald-400">0 <i class="fa-solid fa-thumbs-up text-sm"></i></div>
                    </div>
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-950/60 p-3">
                        <div class="text-[11px] uppercase tracking-[0.2em] text-neutral-500">Negative</div>
                        <div id="header-neg" class="mt-2 text-xl font-bold text-red-400">0 <i class="fa-solid fa-thumbs-down text-sm"></i></div>
                    </div>
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-950/60 p-3">
                        <div class="text-[11px] uppercase tracking-[0.2em] text-neutral-500">Trades</div>
                        <div id="prof-trades" class="mt-2 text-xl font-bold text-white">0</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mb-8 flex flex-wrap gap-2">
            <button data-tab="market" class="tab-btn rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm font-medium text-white">Marketplace</button>
            <button data-tab="my-items" class="tab-btn rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm font-medium text-neutral-400">My items</button>
            <button data-tab="requests" class="tab-btn rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm font-medium text-neutral-400">Requests</button>
            <button data-tab="champion" class="tab-btn rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm font-medium text-neutral-400">Champion</button>
            <button data-tab="profile" class="tab-btn rounded-full border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm font-medium text-neutral-400">Profile</button>
        </div>

        <div id="market-tab" class="tab-content space-y-6">
            <section class="rounded-3xl border border-neutral-900 bg-neutral-900/60 p-5">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex-1 w-full md:max-w-xl">
                        <label class="block text-xs uppercase tracking-[0.2em] text-neutral-500 mb-2">Search</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500"></i>
                            <input id="search-tags" type="text" placeholder="Search by title or tag" class="w-full rounded-xl border border-neutral-800 bg-neutral-950 px-10 py-2.5 text-sm text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>
                    </div>

                    <button class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-neutral-950 hover:bg-neutral-200 transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>List item
                    </button>
                </div>
            </section>

            <section>
                <div id="market-grid" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"></div>
            </section>
        </div>

        <div id="my-items-tab" class="tab-content hidden space-y-6">
            <section class="rounded-3xl border border-neutral-900 bg-neutral-900/60 p-5">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-white">Your listings</h2>
                    <button class="rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-neutral-200 hover:bg-neutral-800 transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>New item
                    </button>
                </div>
                <div id="my-items-list" class="space-y-4"></div>
            </section>
        </div>

        <div id="requests-tab" class="tab-content hidden space-y-6">
            <section class="rounded-3xl border border-neutral-900 bg-neutral-900/60 p-5">
                <h2 class="text-xl font-semibold text-white mb-4">Trade requests</h2>
                <div id="requests-list" class="space-y-4"></div>
            </section>
        </div>

        <div id="champion-tab" class="tab-content hidden space-y-6">
            <section class="rounded-3xl border border-neutral-900 bg-neutral-900/60 p-5">
                <h2 class="text-xl font-semibold text-white mb-4">Local champions</h2>
                <div id="champion-list" class="space-y-3"></div>
            </section>
        </div>

        <div id="profile-tab" class="tab-content hidden space-y-6">
            <section class="rounded-3xl border border-neutral-900 bg-neutral-900/60 p-5">
                <h2 class="text-xl font-semibold text-white mb-4">Profile</h2>
                <div id="profile-card" class="rounded-2xl border border-neutral-800 bg-neutral-950/60 p-5 text-sm text-neutral-300">
                    Loading profile...
                </div>
            </section>
        </div>
    </main>

    <script src="assets/app.js"></script>
</body>
</html>
