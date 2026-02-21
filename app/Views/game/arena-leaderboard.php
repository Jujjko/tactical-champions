<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">🏆 Arena Leaderboard</h1>
                <p class="text-white/60 text-xl mt-2">Top PvP players</p>
            </div>
            <a href="/arena" class="glass px-6 py-3 rounded-xl text-indigo-400 hover:text-indigo-300">Back to Arena</a>
        </div>

        <div class="glass rounded-3xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white/60 uppercase tracking-widest">Rank</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white/60 uppercase tracking-widest">Player</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-white/60 uppercase tracking-widest">Rating</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-white/60 uppercase tracking-widest">W/L</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-white/60 uppercase tracking-widest">Win Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $i => $player): 
                        $totalGames = $player['wins'] + $player['losses'];
                        $winRate = $totalGames > 0 ? round(($player['wins'] / $totalGames) * 100, 1) : 0;
                    ?>
                    <tr class="border-t border-white/5 hover:bg-white/5 transition">
                        <td class="px-6 py-4">
                            <span class="text-2xl">
                                <?= $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                            <div class="text-sm text-white/60">Level <?= $player['level'] ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-2xl font-bold text-indigo-400"><?= $player['rating'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-emerald-400"><?= $player['wins'] ?></span>
                            <span class="text-white/40">/</span>
                            <span class="text-red-400"><?= $player['losses'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="<?= $winRate >= 50 ? 'text-emerald-400' : 'text-red-400' ?>"><?= $winRate ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
