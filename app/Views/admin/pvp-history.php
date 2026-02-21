<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">PvP Match History</h1>
                <p class="text-white/60 mt-2">View all PvP battles</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Back to Admin</a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-3xl font-bold text-indigo-400"><?= $stats['total_battles'] ?? 0 ?></div>
                <div class="text-sm text-white/60 mt-1">Total Battles (7d)</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-3xl font-bold text-cyan-400"><?= round($stats['avg_duration'] ?? 0) ?>s</div>
                <div class="text-sm text-white/60 mt-1">Avg Duration</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-3xl font-bold text-emerald-400"><?= $stats['unique_attackers'] ?? 0 ?></div>
                <div class="text-sm text-white/60 mt-1">Unique Attackers</div>
            </div>
            <div class="glass rounded-2xl p-6 text-center">
                <div class="text-3xl font-bold text-purple-400"><?= $stats['unique_defenders'] ?? 0 ?></div>
                <div class="text-sm text-white/60 mt-1">Unique Defenders</div>
            </div>
        </div>

        <!-- Top Winners -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Top Winners (7 days)</h2>
            <div class="flex gap-4">
                <?php foreach ($topWinners as $i => $winner): ?>
                <div class="flex-1 bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold"><?= $i + 1 ?></div>
                    <div class="font-semibold mt-2"><?= htmlspecialchars($winner['username']) ?></div>
                    <div class="text-emerald-400 font-bold"><?= $winner['wins'] ?> wins</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Battle List -->
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-white/5 text-left">
                        <th class="p-4">Date</th>
                        <th class="p-4">Attacker</th>
                        <th class="p-4">Defender</th>
                        <th class="p-4">Winner</th>
                        <th class="p-4">Rating Change</th>
                        <th class="p-4">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($battles as $battle): ?>
                    <tr class="border-t border-white/5 hover:bg-white/5">
                        <td class="p-4 text-white/60"><?= date('M j, H:i', strtotime($battle['created_at'])) ?></td>
                        <td class="p-4">
                            <span class="<?= $battle['winner_id'] === $battle['attacker_id'] ? 'text-emerald-400' : 'text-white/60' ?>">
                                <?= htmlspecialchars($battle['attacker_name']) ?>
                            </span>
                            <span class="text-xs text-white/40 ml-2">
                                <?= $battle['attacker_rating_change'] >= 0 ? '+' : '' ?><?= $battle['attacker_rating_change'] ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="<?= $battle['winner_id'] === $battle['defender_id'] ? 'text-emerald-400' : 'text-white/60' ?>">
                                <?= htmlspecialchars($battle['defender_name']) ?>
                            </span>
                            <span class="text-xs text-white/40 ml-2">
                                <?= $battle['defender_rating_change'] >= 0 ? '+' : '' ?><?= $battle['defender_rating_change'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-emerald-400 font-semibold"><?= htmlspecialchars($battle['winner_name']) ?></td>
                        <td class="p-4">
                            <span class="text-emerald-400">+<?= max($battle['attacker_rating_change'], $battle['defender_rating_change']) ?></span>
                            <span class="text-white/40 mx-1">/</span>
                            <span class="text-red-400"><?= min($battle['attacker_rating_change'], $battle['defender_rating_change']) ?></span>
                        </td>
                        <td class="p-4 text-white/60"><?= $battle['duration_seconds'] ?? '-' ?>s</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($page > 1): ?>
        <div class="mt-6 text-center">
            <a href="?page=<?= $page - 1 ?>" class="px-6 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Previous</a>
            <span class="mx-4 text-white/60">Page <?= $page ?></span>
            <a href="?page=<?= $page + 1 ?>" class="px-6 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Next</a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';