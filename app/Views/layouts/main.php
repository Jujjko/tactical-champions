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
        <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-2xl">⚔️</div>
                <a href="/dashboard" class="title-font text-xl font-bold tracking-tight hover:text-indigo-400 transition hidden sm:block">Tactical Champions</a>
            </div>
            
            <div class="flex items-center gap-1">
                <a href="/dashboard" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Dashboard">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-white/70 group-hover:text-indigo-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Dashboard</span>
                </a>
                <a href="/quests" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Quests">
                    <i data-lucide="scroll-text" class="w-5 h-5 text-white/70 group-hover:text-amber-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Quests</span>
                </a>
                <a href="/champions" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Champions">
                    <i data-lucide="swords" class="w-5 h-5 text-white/70 group-hover:text-purple-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Champions</span>
                </a>
                <a href="/equipment" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Equipment">
                    <i data-lucide="shield" class="w-5 h-5 text-white/70 group-hover:text-cyan-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Equipment</span>
                </a>
                <a href="/missions" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Missions">
                    <i data-lucide="map" class="w-5 h-5 text-white/70 group-hover:text-emerald-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Missions</span>
                </a>
                <a href="/arena" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Arena">
                    <i data-lucide="swords" class="w-5 h-5 text-white/70 group-hover:text-red-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Arena</span>
                </a>
                <a href="/guilds" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Guilds">
                    <i data-lucide="users" class="w-5 h-5 text-white/70 group-hover:text-blue-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Guilds</span>
                </a>
                <a href="/shop" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Shop">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-white/70 group-hover:text-yellow-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Shop</span>
                </a>
                <a href="/achievements" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Achievements">
                    <i data-lucide="trophy" class="w-5 h-5 text-white/70 group-hover:text-orange-400 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Achievements</span>
                </a>
                <?php if (\Core\Session::isModerator()): ?>
                <a href="/admin" class="nav-item group relative px-3 py-2 rounded-xl hover:bg-white/10 transition-all duration-200" title="Admin">
                    <i data-lucide="settings" class="w-5 h-5 text-purple-400 group-hover:text-purple-300 transition"></i>
                    <span class="nav-tooltip absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-black/90 text-xs rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap">Admin</span>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-xl border border-white/10">
                    <i data-lucide="user" class="w-4 h-4 text-indigo-400"></i>
                    <span class="font-medium text-sm"><?= htmlspecialchars(\Core\Session::get('username') ?? 'Hero') ?></span>
                </div>
                <a href="/logout" class="p-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 rounded-xl transition flex items-center gap-2 group" title="Logout">
                    <i data-lucide="log-out" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <?php if ($flash = \Core\Session::getFlash('success')): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast('<?= htmlspecialchars($flash, ENT_QUOTES) ?>', 'success'); });</script>
    <?php endif; ?>

    <?php if ($flash = \Core\Session::getFlash('error')): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast('<?= htmlspecialchars($flash, ENT_QUOTES) ?>', 'error'); });</script>
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
        
        // Global Toast System
        window.toastQueue = [];
        window.toastContainer = null;
        
        function showToast(message, type = 'info', duration = 4000) {
            if (!window.toastContainer) {
                window.toastContainer = document.createElement('div');
                window.toastContainer.id = 'toast-container';
                window.toastContainer.className = 'fixed top-24 right-6 z-50 flex flex-col gap-3';
                document.body.appendChild(window.toastContainer);
            }
            
            const toast = document.createElement('div');
            const icons = {
                success: 'check-circle',
                error: 'alert-circle',
                warning: 'alert-triangle',
                info: 'info'
            };
            const colors = {
                success: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
                error: 'border-red-500/30 bg-red-500/10 text-red-400',
                warning: 'border-amber-500/30 bg-amber-500/10 text-amber-400',
                info: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400'
            };
            
            toast.className = `glass rounded-2xl px-6 py-4 border ${colors[type] || colors.info} flex items-center gap-3 shadow-2xl transform translate-x-full opacity-0 transition-all duration-300`;
            toast.innerHTML = `
                <i data-lucide="${icons[type] || icons.info}" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-2 opacity-60 hover:opacity-100 transition">×</button>
            `;
            
            window.toastContainer.appendChild(toast);
            lucide.createIcons();
            
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            });
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        // Flash messages to toast
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($flash = \Core\Session::getFlash('achievement_unlocked')): ?>
            showToast('🏆 Achievement Unlocked: <?= htmlspecialchars($flash['name']) ?>!', 'success', 5000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
