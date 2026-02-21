<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-10 flex-wrap gap-4">
            <div>
                <h1 class="title-font text-5xl font-bold flex items-center gap-4">
                    <span class="text-5xl">⚔️</span> Your Champions
                </h1>
                <p class="text-white/60 text-xl mt-2"><?= count($champions) ?> warriors ready for battle</p>
            </div>
            <a href="/lootbox" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-3xl font-semibold flex items-center gap-3 hover:scale-105 transition">
                <span class="text-2xl">📦</span> Open Lootbox
            </a>
        </div>

        <?php if (empty($champions)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">⚔️</div>
            <h3 class="text-xl font-semibold mb-2">No Champions Yet!</h3>
            <p class="text-white/60 mb-6">Open lootboxes to discover powerful champions!</p>
            <a href="/lootbox" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition">
                Open Lootboxes
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($champions as $champion): ?>
            <a href="/champions/<?= $champion['id'] ?>" class="glass rounded-3xl overflow-hidden card-hover block group">
                <div class="h-48 bg-gradient-to-br from-violet-900/50 to-fuchsia-900/50 flex items-center justify-center text-8xl group-hover:scale-110 transition">
                    🛡️
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-xl font-semibold"><?= htmlspecialchars($champion['name']) ?></h3>
                            <span class="tier-<?= $champion['tier'] ?> text-xs px-3 py-1 rounded-full text-white mt-1 inline-block">
                                <?= ucfirst($champion['tier']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-400">Lv.<?= $champion['level'] ?></div>
                        </div>
                    </div>
                    
                    <!-- Stars -->
                    <div class="flex gap-1 mb-3">
                        <?= \App\Services\FusionService::getStarsHtml((int)($champion['stars'] ?? 1)) ?>
                    </div>
                    
                    <p class="text-white/50 text-sm mt-3 mb-4 line-clamp-2">
                        <?= htmlspecialchars($champion['description'] ?? 'A powerful champion ready for battle') ?>
                    </p>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-white/5 rounded-lg p-2 text-center">
                            <div class="text-emerald-400">❤️ <?= $champion['health'] ?></div>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2 text-center">
                            <div class="text-rose-400">⚔️ <?= $champion['attack'] ?></div>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2 text-center">
                            <div class="text-amber-400">🛡️ <?= $champion['defense'] ?></div>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2 text-center">
                            <div class="text-cyan-400">⚡ <?= $champion['speed'] ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($champion['special_ability'])): ?>
                    <div class="mt-4 bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-2 text-center">
                        <div class="text-xs text-indigo-400">✨ <?= htmlspecialchars($champion['special_ability']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>