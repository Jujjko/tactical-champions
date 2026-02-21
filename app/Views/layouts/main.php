<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tactical Champions' ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');
        
        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --accent: #f472b6;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --glass: rgba(255,255,255,0.08);
        }
        
        .glass {
            background: var(--glass);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .neon-glow {
            box-shadow: 0 0 25px -5px rgb(99 102 241 / 0.5),
                        0 0 10px -5px rgb(168 85 247 / 0.5);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.4);
        }
        
        .streak-day {
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .streak-day:hover {
            transform: scale(1.15) rotate(3deg);
        }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
        .title-font {
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .tier-common { background: linear-gradient(135deg, #6b7280, #4b5563); }
        .tier-rare { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .tier-epic { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .tier-legendary { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .tier-mythic { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .difficulty-easy { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .difficulty-medium { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .difficulty-hard { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .difficulty-expert { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.4); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.8); }
        }
        .pulse-glow { animation: pulse-glow 2s infinite; }
        
        /* Modal styles */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal.active {
            display: flex;
        }
        .modal::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
        }
        .modal-content {
            position: relative;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            padding: 1.5rem;
            max-width: 28rem;
            width: 100%;
            animation: modalIn 0.3s ease-out;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .modal-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
            transition: background 0.2s;
        }
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="bg-[#0a0818] text-white min-h-screen">
    <?php if (\Core\Session::isLoggedIn()): ?>
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/10 bg-black/70 backdrop-blur-2xl">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-2xl">⚔️</div>
                <a href="/dashboard" class="title-font text-2xl font-bold tracking-tight hover:text-indigo-400 transition">Tactical Champions</a>
            </div>
            
            <div class="flex items-center gap-8 text-sm font-medium">
                <a href="/dashboard" class="text-white hover:text-indigo-400 transition">Dashboard</a>
                <a href="/quests" class="text-white/70 hover:text-white transition">Quests</a>
                <a href="/champions" class="text-white/70 hover:text-white transition">Champions</a>
                <a href="/equipment" class="text-white/70 hover:text-white transition">Equipment</a>
                <a href="/missions" class="text-white/70 hover:text-white transition">Missions</a>
                <a href="/arena" class="text-white/70 hover:text-white transition">Arena</a>
                <a href="/guilds" class="text-white/70 hover:text-white transition">Guilds</a>
                <a href="/shop" class="text-white/70 hover:text-white transition">Shop</a>
                <a href="/tutorial" class="text-white/70 hover:text-white transition">Tutorial</a>
                <?php if (\Core\Session::isModerator()): ?>
                <a href="/admin" class="text-purple-400 hover:text-purple-300 transition">Admin</a>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-xl">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span class="font-medium text-sm"><?= htmlspecialchars(\Core\Session::get('username') ?? 'Hero') ?></span>
                </div>
                <a href="/logout" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <?php if ($flash = \Core\Session::getFlash('success')): ?>
    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 glass rounded-2xl px-6 py-4 border border-emerald-500/30 bg-emerald-500/10 fade-in">
        <div class="flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            <span class="text-emerald-400 font-medium"><?= htmlspecialchars($flash) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($flash = \Core\Session::getFlash('error')): ?>
    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 glass rounded-2xl px-6 py-4 border border-red-500/30 bg-red-500/10 fade-in">
        <div class="flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-400"></i>
            <span class="text-red-400 font-medium"><?= htmlspecialchars($flash) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?= $content ?? '' ?>

    <script>
        tailwind.config = {
            content: ["**/*.{php,html,js}"],
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#a855f7',
                        dark: '#0a0818'
                    }
                }
            }
        }
        lucide.createIcons();
    </script>
</body>
</html>
