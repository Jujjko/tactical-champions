<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <a href="/champions/<?= $upgradeInfo['champion']['id'] ?>" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Champion
        </a>

        <!-- Champion Header -->
        <div class="glass rounded-3xl p-8 mb-8 neon-glow">
            <div class="flex items-center gap-6 flex-wrap">
                <div class="w-24 h-24 bg-gradient-to-br from-violet-900 to-fuchsia-900 rounded-2xl flex items-center justify-center text-6xl">
                    🛡️
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="title-font text-4xl font-bold"><?= htmlspecialchars($upgradeInfo['champion']['name']) ?></h1>
                        <span class="tier-<?= $upgradeInfo['champion']['tier'] ?> text-lg px-4 py-1 rounded-full text-white">
                            <?= ucfirst($upgradeInfo['champion']['tier']) ?>
                        </span>
                    </div>
                    <div class="text-3xl text-indigo-400 font-bold mt-1">Level <?= $upgradeInfo['champion']['level'] ?></div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-white/60">Power Rating</div>
                    <div class="text-3xl font-bold text-purple-400">
                        <?= $upgradeInfo['champion']['health'] + ($upgradeInfo['champion']['attack'] * 10) + ($upgradeInfo['champion']['defense'] * 5) + ($upgradeInfo['champion']['speed'] * 2) ?>
                    </div>
                </div>
            </div>
            
            <!-- Experience Bar -->
            <div class="mt-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-white/60">Experience</span>
                    <span><?= $upgradeInfo['current_exp'] ?> / <?= $upgradeInfo['exp_to_next_level'] ?> XP</span>
                </div>
                <div class="h-4 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all" 
                         style="width: <?= $upgradeInfo['exp_progress'] ?>%"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Current Stats -->
            <div class="glass rounded-3xl p-8">
                <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
                    <span class="text-3xl">📊</span> Current Stats
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">❤️</span>
                            <span class="text-white/80">Health</span>
                        </div>
                        <span class="text-2xl font-bold text-emerald-400"><?= $upgradeInfo['champion']['health'] ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚔️</span>
                            <span class="text-white/80">Attack</span>
                        </div>
                        <span class="text-2xl font-bold text-rose-400"><?= $upgradeInfo['champion']['attack'] ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🛡️</span>
                            <span class="text-white/80">Defense</span>
                        </div>
                        <span class="text-2xl font-bold text-amber-400"><?= $upgradeInfo['champion']['defense'] ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚡</span>
                            <span class="text-white/80">Speed</span>
                        </div>
                        <span class="text-2xl font-bold text-cyan-400"><?= $upgradeInfo['champion']['speed'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Next Level Preview -->
            <div class="glass rounded-3xl p-8 border border-emerald-500/30">
                <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
                    <span class="text-3xl">⬆️</span> Level <?= $upgradeInfo['champion']['level'] + 1 ?> Preview
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-emerald-500/10 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">❤️</span>
                            <span class="text-white/80">Health</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-emerald-400"><?= $upgradeInfo['next_level_stats']['health'] ?></span>
                            <span class="text-sm text-emerald-400 ml-2">+<?= $upgradeInfo['next_level_stats']['health'] - $upgradeInfo['champion']['health'] ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-rose-500/10 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚔️</span>
                            <span class="text-white/80">Attack</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-rose-400"><?= $upgradeInfo['next_level_stats']['attack'] ?></span>
                            <span class="text-sm text-rose-400 ml-2">+<?= $upgradeInfo['next_level_stats']['attack'] - $upgradeInfo['champion']['attack'] ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-amber-500/10 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🛡️</span>
                            <span class="text-white/80">Defense</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-amber-400"><?= $upgradeInfo['next_level_stats']['defense'] ?></span>
                            <span class="text-sm text-amber-400 ml-2">+<?= $upgradeInfo['next_level_stats']['defense'] - $upgradeInfo['champion']['defense'] ?></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-cyan-500/10 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚡</span>
                            <span class="text-white/80">Speed</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-cyan-400"><?= $upgradeInfo['next_level_stats']['speed'] ?></span>
                            <span class="text-sm text-cyan-400 ml-2">+<?= $upgradeInfo['next_level_stats']['speed'] - $upgradeInfo['champion']['speed'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upgrade Button -->
        <div class="glass rounded-3xl p-8 mt-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-xl font-semibold">Instant Level Up</h3>
                    <p class="text-white/60 text-sm mt-1">Upgrade your champion instantly using gold</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-sm text-white/60">Cost</div>
                        <div class="text-2xl font-bold text-yellow-400">💰 <?= number_format($upgradeInfo['gold_upgrade_cost']) ?></div>
                    </div>
                    <div class="text-sm text-white/60">Your Gold: <span class="text-yellow-400 font-semibold"><?= number_format($resources['gold']) ?></span></div>
                </div>
            </div>
            
            <?php if ($upgradeInfo['can_upgrade_with_gold']): ?>
            <button id="upgrade-btn" 
                    class="mt-6 w-full py-5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-xl font-bold rounded-2xl transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                <span class="text-2xl">⬆️</span> Upgrade to Level <?= $upgradeInfo['champion']['level'] + 1 ?>
            </button>
            <?php else: ?>
            <button disabled class="mt-6 w-full py-5 bg-white/10 text-white/40 text-xl font-bold rounded-2xl cursor-not-allowed flex items-center justify-center gap-3">
                <span class="text-2xl">🔒</span> Not Enough Gold
            </button>
            <?php endif; ?>
        </div>

        <!-- Stat Growth Info -->
        <div class="glass rounded-3xl p-8 mt-8">
            <h3 class="text-xl font-semibold mb-4 flex items-center gap-3">
                <i data-lucide="info" class="w-5 h-5"></i> Stat Growth per Level
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-emerald-400 font-semibold">Health</div>
                    <div class="text-white/60">+10% per level</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-rose-400 font-semibold">Attack</div>
                    <div class="text-white/60">+10% per level</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-amber-400 font-semibold">Defense</div>
                    <div class="text-white/60">+10% per level</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-cyan-400 font-semibold">Speed</div>
                    <div class="text-white/60">+5% per level</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const championId = <?= $upgradeInfo['champion']['id'] ?>;
const csrfToken = '<?= \Core\Session::csrfToken() ?>';

document.getElementById('upgrade-btn')?.addEventListener('click', async function() {
    if (!confirm('Spend <?= number_format($upgradeInfo['gold_upgrade_cost']) ?> gold to upgrade?')) return;
    
    this.disabled = true;
    this.innerHTML = '<span class="animate-spin">⏳</span> Upgrading...';
    
    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        
        const res = await fetch(`/champions/${championId}/upgrade`, {
            method: 'POST',
            body: formData
        });
        
        const data = await res.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Upgrade failed');
            this.disabled = false;
            this.innerHTML = '<span class="text-2xl">⬆️</span> Upgrade to Level <?= $upgradeInfo['champion']['level'] + 1 ?>';
        }
    } catch (err) {
        alert('Error: ' + err.message);
        this.disabled = false;
        this.innerHTML = '<span class="text-2xl">⬆️</span> Upgrade to Level <?= $upgradeInfo['champion']['level'] + 1 ?>';
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>