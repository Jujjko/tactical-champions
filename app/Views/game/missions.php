<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-10 flex-wrap gap-4">
            <div>
                <h1 class="title-font text-5xl font-bold flex items-center gap-4">
                    <span class="text-5xl">⚔️</span> Available Missions
                </h1>
                <p class="text-white/60 text-xl mt-2">Choose your battlefield</p>
            </div>
            
            <div class="glass px-8 py-4 rounded-3xl flex items-center gap-4 neon-glow">
                <span class="text-3xl">⚡</span>
                <div>
                    <div class="text-3xl font-bold text-emerald-400"><?= $resources['energy'] ?></div>
                    <div class="text-xs tracking-widest uppercase text-white/60">Energy</div>
                </div>
            </div>
        </div>

        <?php if (empty($missions)): ?>
        <div class="min-h-[60vh] flex items-center justify-center">
            <div class="glass rounded-3xl p-16 max-w-lg text-center neon-glow">
                <div class="mx-auto w-32 h-32 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-7xl mb-8 shadow-2xl">
                    ⚔️
                </div>
                
                <h2 class="text-4xl font-bold title-font mb-3">The battlefield is quiet...</h2>
                <p class="text-white/70 text-xl mb-10">
                    No missions available right now.<br>
                    New battles will appear soon!
                </p>
                
                <div class="inline-flex items-center gap-3 bg-white/5 px-8 py-4 rounded-3xl text-sm">
                    <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span>Checking for new missions every 5 minutes...</span>
                </div>
                
                <a href="/dashboard" 
                   class="mt-12 block text-center bg-gradient-to-r from-indigo-600 to-purple-600 py-5 rounded-3xl font-semibold text-lg hover:scale-105 transition">
                    Back to Dashboard
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($missions as $mission): ?>
            <div class="glass rounded-3xl overflow-hidden card-hover">
                <div class="h-2 bg-gradient-to-r from-<?= $mission['difficulty'] === 'easy' ? 'emerald' : ($mission['difficulty'] === 'medium' ? 'amber' : ($mission['difficulty'] === 'hard' ? 'rose' : 'purple')) ?>-500 to-<?= $mission['difficulty'] === 'easy' ? 'teal' : ($mission['difficulty'] === 'medium' ? 'orange' : ($mission['difficulty'] === 'hard' ? 'red' : 'violet')) ?>-500"></div>
                
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-semibold"><?= htmlspecialchars($mission['name']) ?></h3>
                        <span class="difficulty-<?= $mission['difficulty'] ?> text-xs px-3 py-1 rounded-full text-white font-medium">
                            <?= strtoupper($mission['difficulty']) ?>
                        </span>
                    </div>
                    
                    <p class="text-white/60 text-sm mb-6 line-clamp-2">
                        <?= htmlspecialchars($mission['description']) ?>
                    </p>
                    
                    <div class="grid grid-cols-3 gap-3 text-center mb-6">
                        <div class="bg-white/5 rounded-xl p-3">
                            <div class="text-xs text-white/50 uppercase">Level</div>
                            <div class="text-xl font-bold"><?= $mission['required_level'] ?></div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3">
                            <div class="text-xs text-white/50 uppercase">Energy</div>
                            <div class="text-xl font-bold text-emerald-400"><?= $mission['energy_cost'] ?></div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3">
                            <div class="text-xs text-white/50 uppercase">Reward</div>
                            <div class="text-xl font-bold text-yellow-400">💰 <?= $mission['gold_reward'] ?></div>
                        </div>
                    </div>
                    
                    <?php if ($userLevel >= $mission['required_level'] && $resources['energy'] >= $mission['energy_cost']): ?>
                        <a href="/battle/prepare/<?= $mission['id'] ?>" 
                           class="block text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 py-4 rounded-2xl font-semibold text-lg transition transform hover:scale-[1.02]">
                            START MISSION →
                        </a>
                    <?php else: ?>
                        <button disabled class="w-full py-4 rounded-2xl bg-white/10 text-white/40 cursor-not-allowed text-lg">
                            <?= $userLevel < $mission['required_level'] ? '🔒 Level Too Low' : '⚡ Not Enough Energy' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
