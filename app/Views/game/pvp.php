<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-12">
            <h1 class="title-font text-6xl font-bold mb-3">PvP Arena</h1>
            <p class="text-2xl text-white/70">Battle against real players • Climb the ranks</p>
        </div>

        <?php if (!$season): ?>
        <div class="glass rounded-3xl p-10 text-center mb-10 border border-yellow-500/30">
            <div class="text-6xl mb-4">⚠️</div>
            <h2 class="text-2xl font-bold mb-2">No Active Season</h2>
            <p class="text-white/60">Check back later for the next competitive season.</p>
        </div>
        <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl mb-4"><?= $matchmaking->getRankIcon($stats['rank_name']) ?></div>
                <div class="text-5xl font-bold mb-2"><?= number_format($stats['rating']) ?></div>
                <div class="text-2xl font-bold text-orange-400"><?= $stats['rank_name'] ?></div>
                <div class="text-white/50 text-sm mt-2">Rank #<?= number_format($stats['rank']) ?></div>
            </div>

            <div class="glass rounded-3xl p-8">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-emerald-400"><?= $stats['wins'] ?></div>
                        <div class="text-white/60 text-sm">Wins</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-red-400"><?= $stats['losses'] ?></div>
                        <div class="text-white/60 text-sm">Losses</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-indigo-400"><?= $stats['win_rate'] ?>%</div>
                        <div class="text-white/60 text-sm">Win Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-orange-400"><?= $stats['current_streak'] ?></div>
                        <div class="text-white/60 text-sm">Streak</div>
                    </div>
                </div>
                <div class="mt-6 text-center text-white/50 text-sm">
                    Highest: <?= number_format($stats['highest_rating']) ?> • Best Streak: <?= $stats['best_streak'] ?>
                </div>
            </div>

            <div class="glass rounded-3xl p-8">
                <div class="text-center mb-4">
                    <div class="text-2xl font-bold"><?= htmlspecialchars($season['name']) ?></div>
                    <div class="text-white/60 text-sm">Season ends in</div>
                </div>
                <div id="season-timer" class="text-center">
                    <div class="text-4xl font-bold text-indigo-400"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <div class="glass rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-6 text-center">Select Champion</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="champion-select">
                    <?php foreach ($champions as $champion): ?>
                    <button onclick="selectChampion(<?= $champion['id'] ?>)" 
                            data-champion-id="<?= $champion['id'] ?>"
                            class="champion-card glass rounded-2xl p-4 text-center hover-lift transition-all <?= $champion === reset($champions) ? 'ring-2 ring-indigo-500' : '' ?>">
                        <div class="text-3xl mb-2">
                            <?php 
                            $icons = ['common' => '⚔️', 'rare' => '🛡️', 'epic' => '🔮', 'legendary' => '🐉', 'mythic' => '👑'];
                            echo $icons[$champion['tier']] ?? '⚔️';
                            ?>
                        </div>
                        <div class="font-semibold text-sm truncate"><?= htmlspecialchars($champion['name']) ?></div>
                        <div class="text-xs text-white/60">Lv.<?= $champion['level'] ?></div>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected-champion" value="<?= $champions[0]['id'] ?? 0 ?>">
            </div>

            <div class="glass rounded-3xl p-8 flex flex-col items-center justify-center">
                <button onclick="findMatch()" 
                        id="find-match-btn"
                        class="w-full py-8 bg-gradient-to-r from-red-600 to-orange-600 rounded-3xl font-bold text-2xl hover:from-red-500 hover:to-orange-500 transition-all hover:scale-105 neon-glow">
                    <div class="flex items-center justify-center gap-4">
                        <span class="text-4xl">⚔️</span>
                        <span>Find Match</span>
                    </div>
                    <div class="text-sm font-normal text-white/70 mt-2">Energy Cost: 15</div>
                </button>
                
                <div id="matchmaking-status" class="mt-6 text-center hidden">
                    <div class="animate-spin w-12 h-12 border-4 border-white/30 border-t-white rounded-full mx-auto mb-4"></div>
                    <div class="text-xl">Searching for opponent...</div>
                    <div class="text-white/50 text-sm mt-2">±150 rating range</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="glass rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-6">Top Players</h3>
                <div class="space-y-3">
                    <?php foreach (array_slice($leaderboard, 0, 10) as $index => $player): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl <?= $player['user_id'] === ($_SESSION['user_id'] ?? 0) ? 'bg-indigo-500/20 border border-indigo-500/50' : 'bg-white/5' ?>">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center font-bold">
                                <?= $index + 1 ?>
                            </div>
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                                <div class="text-xs text-white/50">Lv.<?= $player['level'] ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-indigo-400"><?= number_format($player['rating']) ?></div>
                            <div class="text-xs text-white/50"><?= $player['wins'] ?>W / <?= $player['losses'] ?>L</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="/pvp/leaderboard" class="block mt-4 text-center text-indigo-400 hover:text-indigo-300">
                    View Full Leaderboard →
                </a>
            </div>

            <div class="glass rounded-3xl p-8">
                <h3 class="text-2xl font-bold mb-6">Recent Battles</h3>
                <?php if (empty($recentBattles)): ?>
                <div class="text-center py-8 text-white/50">
                    <div class="text-4xl mb-2">⚔️</div>
                    <p>No battles yet. Start matchmaking!</p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentBattles as $battle): ?>
                    <?php $isWinner = $battle['winner_id'] === ($_SESSION['user_id'] ?? 0); ?>
                    <div class="flex items-center justify-between p-3 rounded-xl <?= $isWinner ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-red-500/10 border border-red-500/30' ?>">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl"><?= $isWinner ? '🏆' : '💔' ?></div>
                            <div>
                                <div class="font-semibold">
                                    vs <?= htmlspecialchars($battle['attacker_id'] === ($_SESSION['user_id'] ?? 0) ? $battle['defender_name'] : $battle['attacker_name']) ?>
                                </div>
                                <div class="text-xs text-white/50"><?= date('M j, g:i A', strtotime($battle['created_at'])) ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold <?= $isWinner ? 'text-emerald-400' : 'text-red-400' ?>">
                                <?= $isWinner ? '+' : '' ?><?= $battle['attacker_id'] === ($_SESSION['user_id'] ?? 0) ? $battle['attacker_rating_change'] : $battle['defender_rating_change'] ?>
                            </div>
                            <div class="text-xs text-white/50"><?= $battle['duration_seconds'] ?>s</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="/pvp/history" class="block mt-4 text-center text-indigo-400 hover:text-indigo-300">
                    View All Battles →
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let selectedChampion = <?= $champions[0]['id'] ?? 0 ?>;
const csrfToken = '<?= $csrf_token ?>';

function selectChampion(id) {
    selectedChampion = id;
    document.getElementById('selected-champion').value = id;
    
    document.querySelectorAll('.champion-card').forEach(card => {
        card.classList.remove('ring-2', 'ring-indigo-500');
    });
    
    document.querySelector(`[data-champion-id="${id}"]`).classList.add('ring-2', 'ring-indigo-500');
}

function findMatch() {
    const btn = document.getElementById('find-match-btn');
    const status = document.getElementById('matchmaking-status');
    
    btn.disabled = true;
    btn.classList.add('opacity-50');
    status.classList.remove('hidden');
    
    fetch('/pvp/find-match', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `champion_id=${selectedChampion}&csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.innerHTML = `
                <div class="text-2xl mb-4">Opponent Found!</div>
                <div class="glass rounded-2xl p-4 mb-4">
                    <div class="text-xl font-bold">${data.opponent.username}</div>
                    <div class="text-white/60">Rating: ${data.opponent.rating} • Lv.${data.opponent.level}</div>
                    <div class="mt-2 text-sm">
                        Champion: ${data.opponent_champion.name} (Lv.${data.opponent_champion.level})
                    </div>
                </div>
            `;
            
            setTimeout(() => startPvpBattle(data), 1500);
        } else {
            alert(data.error || 'No opponents found');
            resetMatchmaking();
        }
    })
    .catch(() => {
        alert('Error finding match');
        resetMatchmaking();
    });
}

function startPvpBattle(matchData) {
    fetch('/pvp/start-battle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `opponent_id=${matchData.opponent.id}&my_champion_id=${selectedChampion}&opponent_champion_id=${matchData.opponent_champion.id}&csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.error || 'Failed to start battle');
            resetMatchmaking();
        }
    });
}

function resetMatchmaking() {
    const btn = document.getElementById('find-match-btn');
    const status = document.getElementById('matchmaking-status');
    
    btn.disabled = false;
    btn.classList.remove('opacity-50');
    status.classList.add('hidden');
}

function updateSeasonTimer() {
    const endEl = document.querySelector('#season-timer .text-4xl');
    if (!endEl) return;
    
    <?php if ($season): ?>
    const endDate = new Date('<?= $season['ends_at'] ?>').getTime();
    
    function update() {
        const now = new Date().getTime();
        const diff = endDate - now;
        
        if (diff <= 0) {
            endEl.textContent = 'Ended';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        endEl.textContent = `${days}d ${hours}h ${minutes}m`;
    }
    
    update();
    setInterval(update, 60000);
    <?php endif; ?>
}

updateSeasonTimer();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
