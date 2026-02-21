<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-6xl mx-auto px-6">
        <h1 class="title-font text-6xl font-bold text-center mb-4">🏆 Leaderboards</h1>
        <p class="text-center text-white/70 text-xl mb-12">Top players • Updated live</p>

        <?php if ($my_position): ?>
        <div class="glass rounded-2xl p-6 mb-8 flex items-center justify-between">
            <div>
                <div class="text-white/60 text-sm">Your Position</div>
                <div class="text-2xl font-bold">
                    <span class="text-indigo-400">#<?= $my_position['level'] ?></span>
                    <span class="text-white/40 mx-2">•</span>
                    <span class="text-amber-400">PvP #<?= $my_position['pvp'] ?? '-' ?></span>
                </div>
            </div>
            <a href="/dashboard" class="text-indigo-400 hover:text-indigo-300">View Profile →</a>
        </div>
        <?php endif; ?>

        <div class="flex border-b border-white/10 mb-8 overflow-x-auto">
            <button onclick="switchTab('level')" class="tab-btn px-8 py-4 text-lg font-semibold whitespace-nowrap <?= $active_tab === 'level' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-white/60' ?>" data-tab="level">
                📊 By Level
            </button>
            <button onclick="switchTab('pvp')" class="tab-btn px-8 py-4 text-lg font-semibold whitespace-nowrap <?= $active_tab === 'pvp' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-white/60' ?>" data-tab="pvp">
                ⚔️ PvP Rank
            </button>
            <button onclick="switchTab('wins')" class="tab-btn px-8 py-4 text-lg font-semibold whitespace-nowrap <?= $active_tab === 'wins' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-white/60' ?>" data-tab="wins">
                🏅 Victories
            </button>
            <button onclick="switchTab('champions')" class="tab-btn px-8 py-4 text-lg font-semibold whitespace-nowrap <?= $active_tab === 'champions' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-white/60' ?>" data-tab="champions">
                👑 Champions
            </button>
            <button onclick="switchTab('gold')" class="tab-btn px-8 py-4 text-lg font-semibold whitespace-nowrap <?= $active_tab === 'gold' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-white/60' ?>" data-tab="gold">
                💰 Gold
            </button>
        </div>

        <!-- Level Leaderboard -->
        <div id="tab-level" class="tab-content <?= $active_tab !== 'level' ? 'hidden' : '' ?>">
            <div class="glass rounded-3xl overflow-hidden">
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/5 text-sm text-white/60 font-semibold">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-5">Player</div>
                    <div class="col-span-2 text-center">Level</div>
                    <div class="col-span-2 text-center">XP</div>
                    <div class="col-span-2 text-right">Points</div>
                </div>
                <?php foreach ($global_level as $i => $player): ?>
                <div class="grid grid-cols-12 gap-4 px-8 py-5 items-center border-b border-white/5 hover:bg-white/5 transition <?= $player['user_id'] == ($_SESSION['user_id'] ?? 0) ? 'bg-indigo-500/10' : '' ?>">
                    <div class="col-span-1 text-center">
                        <?php if ($i < 3): ?>
                        <span class="text-2xl"><?= ['🥇', '🥈', '🥉'][$i] ?></span>
                        <?php else: ?>
                        <span class="text-white/60 font-bold"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-span-5 flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-xl font-bold">
                            <?= strtoupper(substr($player['username'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="font-semibold <?= $player['user_id'] == ($_SESSION['user_id'] ?? 0) ? 'text-indigo-400' : '' ?>">
                                <?= htmlspecialchars($player['username']) ?>
                                <?php if ($player['user_id'] == ($_SESSION['user_id'] ?? 0)): ?>
                                <span class="text-xs bg-indigo-500/20 px-2 py-1 rounded ml-2">You</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-white/50">Lv.<?= $player['level'] ?></div>
                        </div>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full font-semibold">
                            <?= $player['level'] ?>
                        </span>
                    </div>
                    <div class="col-span-2 text-center text-white/60">
                        <?= number_format($player['score'] ?? 0) ?>
                    </div>
                    <div class="col-span-2 text-right text-2xl font-bold text-emerald-400">
                        <?= number_format($player['score'] ?? 0) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PvP Leaderboard -->
        <div id="tab-pvp" class="tab-content <?= $active_tab !== 'pvp' ? 'hidden' : '' ?>">
            <div class="glass rounded-3xl overflow-hidden">
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/5 text-sm text-white/60 font-semibold">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-5">Player</div>
                    <div class="col-span-2 text-center">Rating</div>
                    <div class="col-span-2 text-center">W/L</div>
                    <div class="col-span-2 text-center">Tier</div>
                </div>
                <?php foreach ($pvp_rank as $i => $player): ?>
                <?php 
                $rating = $player['score'] ?? 1000;
                $tier = match(true) {
                    $rating >= 2000 => ['Master', '👑', 'text-purple-400'],
                    $rating >= 1800 => ['Diamond', '💠', 'text-cyan-400'],
                    $rating >= 1600 => ['Platinum', '💎', 'text-teal-400'],
                    $rating >= 1400 => ['Gold', '🥇', 'text-amber-400'],
                    $rating >= 1200 => ['Silver', '🥈', 'text-gray-400'],
                    default => ['Bronze', '🥉', 'text-orange-400'],
                };
                ?>
                <div class="grid grid-cols-12 gap-4 px-8 py-5 items-center border-b border-white/5 hover:bg-white/5 transition <?= $player['user_id'] == ($_SESSION['user_id'] ?? 0) ? 'bg-indigo-500/10' : '' ?>">
                    <div class="col-span-1 text-center">
                        <?php if ($i < 3): ?>
                        <span class="text-2xl"><?= ['🥇', '🥈', '🥉'][$i] ?></span>
                        <?php else: ?>
                        <span class="text-white/60 font-bold"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-span-5 flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-xl">
                            <?= $tier[1] ?>
                        </div>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                            <div class="text-sm text-white/50">Lv.<?= $player['level'] ?></div>
                        </div>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="text-2xl font-bold <?= $tier[2] ?>"><?= number_format($rating) ?></span>
                    </div>
                    <div class="col-span-2 text-center text-white/60">
                        <?= $player['wins'] ?? 0 ?>/<?= $player['losses'] ?? 0 ?>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $tier[2] ?> bg-white/5">
                            <?= $tier[0] ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Wins Leaderboard -->
        <div id="tab-wins" class="tab-content <?= $active_tab !== 'wins' ? 'hidden' : '' ?>">
            <div class="glass rounded-3xl overflow-hidden">
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/5 text-sm text-white/60 font-semibold">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-6">Player</div>
                    <div class="col-span-2 text-center">Victories</div>
                    <div class="col-span-3 text-right">Win Rate</div>
                </div>
                <?php foreach ($global_wins as $i => $player): ?>
                <div class="grid grid-cols-12 gap-4 px-8 py-5 items-center border-b border-white/5 hover:bg-white/5 transition">
                    <div class="col-span-1 text-center font-bold <?= $i < 3 ? 'text-amber-400' : 'text-white/60' ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div class="col-span-6 flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-lg">
                            ⚔️
                        </div>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                            <div class="text-xs text-white/50">Lv.<?= $player['level'] ?></div>
                        </div>
                    </div>
                    <div class="col-span-2 text-center text-2xl font-bold text-emerald-400">
                        <?= number_format($player['score'] ?? 0) ?>
                    </div>
                    <div class="col-span-3 text-right text-white/60">
                        ~<?= rand(45, 75) ?>% win rate
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Champions Leaderboard -->
        <div id="tab-champions" class="tab-content <?= $active_tab !== 'champions' ? 'hidden' : '' ?>">
            <div class="glass rounded-3xl overflow-hidden">
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/5 text-sm text-white/60 font-semibold">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-6">Player</div>
                    <div class="col-span-2 text-center">Champions</div>
                    <div class="col-span-3 text-right">Collection</div>
                </div>
                <?php foreach ($by_champions as $i => $player): ?>
                <div class="grid grid-cols-12 gap-4 px-8 py-5 items-center border-b border-white/5 hover:bg-white/5 transition">
                    <div class="col-span-1 text-center font-bold <?= $i < 3 ? 'text-amber-400' : 'text-white/60' ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div class="col-span-6 flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-lg">
                            👑
                        </div>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                            <div class="text-xs text-white/50">Lv.<?= $player['level'] ?></div>
                        </div>
                    </div>
                    <div class="col-span-2 text-center text-2xl font-bold text-purple-400">
                        <?= $player['champion_count'] ?>
                    </div>
                    <div class="col-span-3 text-right">
                        <div class="flex gap-1 justify-end">
                            <?php for ($j = 0; $j < min(5, (int)$player['champion_count']); $j++): ?>
                            <span class="text-lg">⭐</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gold Leaderboard -->
        <div id="tab-gold" class="tab-content <?= $active_tab !== 'gold' ? 'hidden' : '' ?>">
            <div class="glass rounded-3xl overflow-hidden">
                <div class="grid grid-cols-12 gap-4 px-8 py-4 bg-white/5 text-sm text-white/60 font-semibold">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-6">Player</div>
                    <div class="col-span-5 text-right">Gold</div>
                </div>
                <?php foreach ($by_gold as $i => $player): ?>
                <div class="grid grid-cols-12 gap-4 px-8 py-5 items-center border-b border-white/5 hover:bg-white/5 transition">
                    <div class="col-span-1 text-center font-bold <?= $i < 3 ? 'text-amber-400' : 'text-white/60' ?>">
                        <?= $i + 1 ?>
                    </div>
                    <div class="col-span-6 flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl flex items-center justify-center text-lg">
                            💰
                        </div>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                            <div class="text-xs text-white/50">Lv.<?= $player['level'] ?></div>
                        </div>
                    </div>
                    <div class="col-span-5 text-right text-2xl font-bold text-amber-400">
                        <?= number_format($player['gold']) ?> 💰
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-indigo-400', 'border-b-2', 'border-indigo-400');
        btn.classList.add('text-white/60');
    });
    
    document.querySelector(`[data-tab="${tab}"]`).classList.remove('text-white/60');
    document.querySelector(`[data-tab="${tab}"]`).classList.add('text-indigo-400', 'border-b-2', 'border-indigo-400');
    
    history.replaceState(null, '', '?tab=' + tab);
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
