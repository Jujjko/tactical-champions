<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">🏆 Achievements</h1>
                <p class="text-white/60 text-xl mt-2">Complete challenges and earn rewards</p>
            </div>
            <div class="glass rounded-2xl p-4 text-center">
                <div class="text-3xl font-bold text-indigo-400"><?= $completedCount ?>/<?= $totalCount ?></div>
                <div class="text-xs text-white/60 uppercase tracking-widest">Completed</div>
            </div>
        </div>

        <?php foreach ($achievementsByCategory as $category => $achievements): ?>
        <div class="glass rounded-3xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                <?php
                $catIcons = ['battle' => '⚔️', 'champion' => '🛡️', 'social' => '👥', 'progression' => '📈', 'special' => '⭐'];
                echo $catIcons[$category] ?? '🏆';
                ?>
                <?= ucfirst($category) ?> Achievements
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($achievements as $achievement): ?>
                <div class="bg-white/5 rounded-2xl p-4 <?= $achievement['completed'] ? 'border border-emerald-500/30' : '' ?>">
                    <div class="flex items-start gap-3">
                        <div class="text-3xl">
                            <?= $achievement['completed'] ? '✅' : ($achievement['progress'] > 0 ? '🔄' : '🔒') ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold"><?= htmlspecialchars($achievement['name']) ?></span>
                                <span class="tier-<?= $achievement['rarity'] ?> text-xs px-2 py-0.5 rounded-full text-white">
                                    <?= ucfirst($achievement['rarity']) ?>
                                </span>
                            </div>
                            <p class="text-sm text-white/60 mb-2"><?= htmlspecialchars($achievement['description']) ?></p>
                            
                            <?php if ($achievement['completed']): ?>
                            <div class="flex items-center gap-2 text-sm text-emerald-400">
                                <span>✓ Completed</span>
                                <?php if ($achievement['reward_gold'] > 0 || $achievement['reward_gems'] > 0): ?>
                                <span class="text-white/40">|</span>
                                <span>💰<?= $achievement['reward_gold'] ?> 💎<?= $achievement['reward_gems'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="mt-2">
                                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" 
                                         style="width: <?= min(($achievement['progress'] / $achievement['requirement_value']) * 100, 100) ?>%"></div>
                                </div>
                                <div class="text-xs text-white/40 mt-1">
                                    <?= $achievement['progress'] ?>/<?= $achievement['requirement_value'] ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
