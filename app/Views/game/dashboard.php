<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] pb-12 pt-20">
    <div class="max-w-7xl mx-auto px-6">

        <?php if (!$tutorialCompleted): ?>
        <div class="glass rounded-3xl p-6 mb-8 border-l-4 border-indigo-500 neon-glow">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-4xl">🎮</span>
                    <div>
                        <h3 class="text-xl font-bold">Welcome to Tactical Champions!</h3>
                        <p class="text-white/60 text-sm">Complete the tutorial to learn the basics and earn rewards.</p>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="h-2 w-32 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" style="width: <?= $tutorialPercent ?>%"></div>
                            </div>
                            <span class="text-xs text-white/60"><?= $tutorialPercent ?>% complete</span>
                        </div>
                    </div>
                </div>
                <a href="/tutorial" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-600 transition">
                    Continue Tutorial
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login Streak -->
        <div class="glass rounded-3xl p-8 mb-10 neon-glow">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🔥</span>
                    <div>
                        <div class="text-4xl font-bold title-font">Login Streak</div>
                        <div class="text-3xl text-orange-400"><?= $loginStatus['streak'] ?? 0 ?> days</div>
                    </div>
                </div>
                <div class="text-emerald-400 text-lg font-medium flex items-center gap-2">
                    <span class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></span>
                    Reward claimed today
                </div>
            </div>

            <!-- Animated Streak Days -->
            <div class="grid grid-cols-7 gap-4" id="streak-grid">
                <?php if (!empty($loginStatus['weekly_progress'])): ?>
                <?php foreach ($loginStatus['weekly_progress'] as $day => $data): ?>
                <div class="streak-day glass rounded-2xl p-6 text-center transition-all duration-500 <?= $data['current'] ?? false ? 'scale-110 ring-4 ring-orange-400' : '' ?>" 
                     style="animation-delay: <?= $day * 80 ?>ms">
                    <div class="text-5xl mb-3 transition-transform"><?= ($data['claimed'] ?? false) ? '🔥' : '📅' ?></div>
                    <div class="font-bold text-lg">Day <?= $day ?></div>
                    <div class="text-xs text-white/70">+<?= $data['rewards']['gold'] ?? 0 ?>g</div>
                    <?php if (($data['rewards']['gems'] ?? 0) > 0): ?>
                    <div class="text-xs text-cyan-400">+<?= $data['rewards']['gems'] ?>💎</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resources -->
        <div class="grid grid-cols-3 gap-6 mb-12">
            <div class="resource-card glass rounded-3xl p-8 hover-lift" data-count="<?= $resources['gold'] ?>">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center text-5xl">💰</div>
                    <div>
                        <div class="text-5xl font-bold count-up" id="gold-count">0</div>
                        <div class="uppercase tracking-widest text-amber-400 text-sm">Gold</div>
                    </div>
                </div>
            </div>
            
            <div class="resource-card glass rounded-3xl p-8 hover-lift" data-count="<?= $resources['gems'] ?>">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl flex items-center justify-center text-5xl">💎</div>
                    <div>
                        <div class="text-5xl font-bold count-up" id="gems-count">0</div>
                        <div class="uppercase tracking-widest text-cyan-400 text-sm">Gems</div>
                    </div>
                </div>
            </div>
            
            <div class="resource-card glass rounded-3xl p-8 hover-lift">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center text-5xl">⚡</div>
                    <div>
                        <div class="text-5xl font-bold"><?= $resources['energy'] ?> <span class="text-2xl text-white/60">/ <?= $resources['max_energy'] ?></span></div>
                        <div class="uppercase tracking-widest text-emerald-400 text-sm">Energy</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-6 mb-12">
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-indigo-400"><?= $user['level'] ?></div>
                <div class="uppercase text-sm tracking-widest mt-2">Level</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold"><?= count($champions) ?></div>
                <div class="uppercase text-sm tracking-widest mt-2">Champions</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold"><?= $stats['total_battles'] ?? 0 ?></div>
                <div class="uppercase text-sm tracking-widest mt-2">Battles</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-emerald-400"><?= $stats['victories'] ?? 0 ?></div>
                <div class="uppercase text-sm tracking-widest mt-2">Victories</div>
            </div>
        </div>

        <!-- Experience -->
        <div class="glass rounded-3xl p-8 mb-12">
            <div class="flex justify-between mb-4">
                <h3 class="font-semibold text-xl">Experience Progress</h3>
                <span class="text-white/70"><?= $user['experience'] ?> / <?= $user['level'] * 100 ?> XP</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-fill" style="width: <?= min(($user['experience'] / ($user['level'] * 100)) * 100, 100) ?>%"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-3 gap-6 mb-12">
            <a href="/missions" class="quick-card glass rounded-3xl p-8 hover-lift group">
                <div class="text-6xl mb-6 transition group-hover:rotate-12">🎯</div>
                <h3 class="text-2xl font-semibold">Missions</h3>
                <p class="text-white/60 text-sm mt-2">Earn rewards & level up</p>
                <div class="mt-10 text-indigo-400 group-hover:translate-x-3 transition">Start Mission →</div>
            </a>
            
            <a href="/champions" class="quick-card glass rounded-3xl p-8 hover-lift group">
                <div class="text-6xl mb-6 transition group-hover:rotate-12">⚔️</div>
                <h3 class="text-2xl font-semibold">Champions</h3>
                <p class="text-white/60 text-sm mt-2">Manage your roster</p>
                <div class="mt-10 text-indigo-400 group-hover:translate-x-3 transition">View Champions →</div>
            </a>
            
            <a href="/lootbox" class="quick-card glass rounded-3xl p-8 hover-lift group">
                <div class="text-6xl mb-6 transition group-hover:rotate-12">📦</div>
                <h3 class="text-2xl font-semibold">Lootboxes</h3>
                <p class="text-white/60 text-sm mt-2">Discover new champions</p>
                <div class="mt-10 text-indigo-400 group-hover:translate-x-3 transition">Open Lootbox →</div>
            </a>
        </div>

        <!-- Champion Preview -->
        <?php if (!empty($champions)): ?>
        <div class="glass rounded-3xl p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold">Your Champions</h3>
                <a href="/champions" class="text-sm text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                    View All <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach (array_slice($champions, 0, 4) as $champion): ?>
                <a href="/champions/<?= $champion['id'] ?>" class="glass rounded-xl p-4 hover-lift">
                    <div class="w-full h-24 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-lg flex items-center justify-center text-4xl mb-3">🛡️</div>
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-semibold truncate"><?= htmlspecialchars($champion['name']) ?></h4>
                        <span class="tier-<?= $champion['tier'] ?> text-xs px-2 py-0.5 rounded-full text-white"><?= ucfirst($champion['tier']) ?></span>
                    </div>
                    <div class="text-sm text-white/60">Level <?= $champion['level'] ?></div>
                    <div class="flex gap-2 mt-2 text-xs">
                        <span class="text-emerald-400">❤️<?= $champion['health'] ?></span>
                        <span class="text-red-400">⚔️<?= $champion['attack'] ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Battles -->
        <?php if (!empty($recentBattles)): ?>
        <div class="glass rounded-3xl p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold">⚔️ Recent Battles</h3>
                <span class="text-sm text-white/60">
                    Win Rate: <?= round(($stats['victories'] / max($stats['total_battles'], 1)) * 100, 1) ?>%
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-white/60 border-b border-white/10">
                            <th class="pb-3 font-medium">Mission</th>
                            <th class="pb-3 font-medium">Result</th>
                            <th class="pb-3 font-medium">Duration</th>
                            <th class="pb-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBattles as $battle): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <?= htmlspecialchars($battle['mission_name'] ?? 'Unknown') ?>
                                    <?php if ($battle['difficulty']): ?>
                                    <span class="difficulty-<?= $battle['difficulty'] ?> text-xs px-2 py-0.5 rounded-full text-white">
                                        <?= $battle['difficulty'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3">
                                <?php if ($battle['result'] === 'victory'): ?>
                                <span class="text-emerald-400 flex items-center gap-1">
                                    <i data-lucide="trophy" class="w-4 h-4"></i> Victory
                                </span>
                                <?php else: ?>
                                <span class="text-red-400 flex items-center gap-1">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Defeat
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-white/60"><?= gmdate('i:s', (int)$battle['duration_seconds']) ?></td>
                            <td class="py-3 text-white/40"><?= date('M j, H:i', strtotime($battle['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function animateCount(id, target, duration = 1200) {
    let start = 0;
    const step = target / (duration / 16);
    const el = document.getElementById(id);
    const timer = setInterval(() => {
        start += step;
        if (start >= target) {
            el.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            el.textContent = Math.floor(start).toLocaleString();
        }
    }, 16);
}

window.onload = () => {
    animateCount('gold-count', <?= $resources['gold'] ?>);
    animateCount('gems-count', <?= $resources['gems'] ?>);
    
    document.querySelectorAll('.streak-day').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        setTimeout(() => {
            el.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, i * 80);
    });
};
</script>

<style>
.hover-lift {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s;
}
.hover-lift:hover {
    transform: translateY(-12px) scale(1.03);
    box-shadow: 0 30px 60px -15px rgb(99 102 241 / 0.4);
}

.progress-bar-container {
    height: 12px;
    background: rgba(255,255,255,0.1);
    border-radius: 9999px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
    transition: width 2s cubic-bezier(0.34, 1.56, 0.64, 1);
    border-radius: 9999px;
}

.quick-card .text-6xl {
    transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}
</style>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
