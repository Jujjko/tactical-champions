<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold flex items-center gap-4">
                    <span class="text-5xl">⚔️</span> Battle History
                </h1>
                <p class="text-white/60 text-xl mt-2">Your complete battle record</p>
            </div>
            <a href="/missions" class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl font-semibold flex items-center gap-3 hover:scale-105 transition">
                <span class="text-2xl">🎯</span> New Battle
            </a>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-4xl font-bold"><?= $stats['total_battles'] ?? 0 ?></div>
                <div class="text-sm text-white/60 uppercase tracking-wider mt-1">Total Battles</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-4xl font-bold text-emerald-400"><?= $stats['victories'] ?? 0 ?></div>
                <div class="text-sm text-white/60 uppercase tracking-wider mt-1">Victories</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-4xl font-bold text-rose-400"><?= $stats['defeats'] ?? 0 ?></div>
                <div class="text-sm text-white/60 uppercase tracking-wider mt-1">Defeats</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-4xl font-bold text-indigo-400">
                    <?= $stats['total_battles'] > 0 ? round(($stats['victories'] / $stats['total_battles']) * 100, 1) : 0 ?>%
                </div>
                <div class="text-sm text-white/60 uppercase tracking-wider mt-1">Win Rate</div>
            </div>
        </div>

        <?php if (empty($battles)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">⚔️</div>
            <h3 class="text-xl font-semibold mb-2">No Battles Yet!</h3>
            <p class="text-white/60 mb-6">Start your first mission to begin your battle journey!</p>
            <a href="/missions" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition">
                Start a Mission
            </a>
        </div>
        <?php else: ?>
        <div class="glass rounded-3xl overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-white/60 text-sm border-b border-white/10">
                        <th class="p-5 font-medium">Mission</th>
                        <th class="p-5 font-medium">Result</th>
                        <th class="p-5 font-medium">Duration</th>
                        <th class="p-5 font-medium">Rewards</th>
                        <th class="p-5 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($battles as $battle): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($battle['mission_name'] ?? 'Unknown Mission') ?></div>
                                    <?php if ($battle['difficulty']): ?>
                                    <span class="difficulty-<?= $battle['difficulty'] ?> text-xs px-2 py-0.5 rounded-full text-white mt-1 inline-block">
                                        <?= ucfirst($battle['difficulty']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="p-5">
                            <?php if ($battle['result'] === 'victory'): ?>
                            <div class="flex items-center gap-2 text-emerald-400">
                                <span class="text-xl">🏆</span>
                                <span class="font-semibold">Victory</span>
                            </div>
                            <?php else: ?>
                            <div class="flex items-center gap-2 text-rose-400">
                                <span class="text-xl">💀</span>
                                <span class="font-semibold">Defeat</span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 text-white/60">
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <?= gmdate('i:s', (int)$battle['duration_seconds']) ?>
                            </div>
                        </td>
                        <td class="p-5">
                            <?php 
                            $rewards = json_decode($battle['rewards_earned'] ?? '{}', true);
                            if (!empty($rewards)): 
                            ?>
                            <div class="flex items-center gap-3 text-sm">
                                <?php if (!empty($rewards['gold'])): ?>
                                <span class="text-yellow-400">💰 <?= number_format($rewards['gold']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($rewards['experience'])): ?>
                                <span class="text-indigo-400">✨ <?= $rewards['experience'] ?> XP</span>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-white/40">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 text-white/40 text-sm">
                            <?= date('M j, Y', strtotime($battle['created_at'])) ?>
                            <div class="text-xs"><?= date('H:i', strtotime($battle['created_at'])) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($battles) >= 20): ?>
        <div class="text-center mt-8 text-white/60">
            Showing last 20 battles
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>