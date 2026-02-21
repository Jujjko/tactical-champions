<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-12">
            <div>
                <a href="/tournaments" class="text-indigo-400 hover:text-indigo-300 mb-2 inline-block">← Back to Tournaments</a>
                <h1 class="title-font text-5xl font-bold"><?= htmlspecialchars($tournament['name']) ?></h1>
                <?php if (!empty($tournament['description'])): ?>
                <p class="text-white/60 mt-2"><?= htmlspecialchars($tournament['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="px-4 py-2 rounded-xl <?= $tournament['status'] === 'ongoing' ? 'bg-emerald-500/20 text-emerald-400' : ($tournament['status'] === 'finished' ? 'bg-gray-500/20 text-gray-400' : 'bg-indigo-500/20 text-indigo-400') ?>">
                    <?= ucfirst($tournament['status']) ?>
                </div>
                <div class="text-white/50 text-sm mt-2">
                    <?= $tournament['current_players'] ?? count($tournament['participants']) ?>/<?= $tournament['max_players'] ?> players
                </div>
            </div>
        </div>

        <?php if ($tournament['status'] === 'finished' && $tournament['winner_id']): ?>
        <div class="glass rounded-3xl p-8 mb-8 text-center border-2 border-amber-500/30">
            <div class="text-6xl mb-4">🏆</div>
            <h2 class="text-3xl font-bold text-amber-400">Tournament Champion</h2>
            <?php 
            $winner = null;
            foreach ($tournament['participants'] as $p) {
                if ($p['user_id'] == $tournament['winner_id']) {
                    $winner = $p;
                    break;
                }
            }
            ?>
            <?php if ($winner): ?>
            <div class="text-2xl font-semibold mt-2"><?= htmlspecialchars($winner['username']) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($tournament['status'] === 'open' || $tournament['status'] === 'full'): ?>
        <div class="glass rounded-3xl p-8 mb-8">
            <h2 class="text-2xl font-bold mb-6">Registered Players</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($tournament['participants'] as $participant): ?>
                <div class="bg-white/5 rounded-xl p-4 text-center <?= $participant['user_id'] == ($_SESSION['user_id'] ?? 0) ? 'ring-2 ring-indigo-500' : '' ?>">
                    <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-xl">
                        <?= strtoupper(substr($participant['username'], 0, 1)) ?>
                    </div>
                    <div class="font-semibold text-sm truncate"><?= htmlspecialchars($participant['username']) ?></div>
                    <div class="text-xs text-white/50">Lv.<?= $participant['level'] ?></div>
                    <?php if ($participant['rating']): ?>
                    <div class="text-xs text-indigo-400"><?= $participant['rating'] ?> SR</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$tournament['is_registered'] && $tournament['status'] === 'open'): ?>
            <div class="mt-8 text-center">
                <button onclick="joinTournament(<?= $tournament['id'] ?>)" 
                        class="px-12 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl font-bold text-lg hover:from-emerald-500 hover:to-teal-500 transition">
                    Join Tournament
                </button>
            </div>
            <?php elseif ($tournament['is_registered'] && $tournament['status'] === 'open'): ?>
            <div class="mt-8 text-center">
                <span class="px-8 py-3 bg-emerald-500/20 text-emerald-400 rounded-xl font-semibold">✓ You are registered</span>
                <button onclick="leaveTournament(<?= $tournament['id'] ?>)" 
                        class="ml-4 px-6 py-3 bg-red-500/20 text-red-400 rounded-xl hover:bg-red-500/30 transition">
                    Leave
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($tournament['status'] === 'ongoing' || $tournament['status'] === 'finished'): ?>
        <?php
        $rounds = [];
        foreach ($tournament['matches'] as $match) {
            $rounds[$match['round']][] = $match;
        }
        $totalRounds = $tournament['round_total'] ?? max(array_keys($rounds));
        ?>
        
        <div class="glass rounded-3xl p-8">
            <h2 class="text-2xl font-bold mb-6">Tournament Bracket</h2>
            
            <div class="overflow-x-auto">
                <div class="flex gap-8 min-w-max">
                    <?php for ($round = 1; $round <= $totalRounds; $round++): ?>
                    <div class="flex flex-col gap-4" style="min-width: 220px;">
                        <div class="text-center text-white/60 font-semibold mb-2">
                            <?= $round === $totalRounds ? 'Finals' : ($round === $totalRounds - 1 ? 'Semifinals' : 'Round ' . $round) ?>
                        </div>
                        
                        <?php 
                        $roundMatches = $rounds[$round] ?? [];
                        $matchSpacing = pow(2, $round - 1);
                        ?>
                        
                        <?php for ($m = 0; $m < count($roundMatches); $m++): ?>
                        <?php $match = $roundMatches[$m]; ?>
                        <div class="match-card bg-white/5 rounded-xl overflow-hidden <?= $match['status'] === 'finished' ? 'opacity-80' : '' ?>" 
                             style="margin: <?= $matchSpacing * 20 ?>px 0;">
                            
                            <div class="p-3 flex items-center justify-between <?= $match['winner_id'] == $match['player1_id'] ? 'bg-emerald-500/10' : ($match['player1_id'] == ($_SESSION['user_id'] ?? 0) ? 'bg-indigo-500/10' : '') ?>">
                                <span class="font-medium <?= $match['status'] === 'bye' ? 'text-amber-400' : '' ?>">
                                    <?= $match['player1_name'] ?? 'TBD' ?>
                                    <?= $match['status'] === 'bye' ? '(BYE)' : '' ?>
                                </span>
                                <?php if ($match['winner_id'] == $match['player1_id']): ?>
                                <span class="text-emerald-400 text-xs font-bold">WIN</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($match['status'] !== 'bye'): ?>
                            <div class="h-px bg-white/10"></div>
                            <div class="p-3 flex items-center justify-between <?= $match['winner_id'] == $match['player2_id'] ? 'bg-emerald-500/10' : ($match['player2_id'] == ($_SESSION['user_id'] ?? 0) ? 'bg-indigo-500/10' : '') ?>">
                                <span class="font-medium">
                                    <?= $match['player2_name'] ?? 'TBD' ?>
                                </span>
                                <?php if ($match['winner_id'] == $match['player2_id']): ?>
                                <span class="text-emerald-400 text-xs font-bold">WIN</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($match['status'] === 'pending' && ($match['player1_id'] == ($_SESSION['user_id'] ?? 0) || $match['player2_id'] == ($_SESSION['user_id'] ?? 0))): ?>
                            <div class="p-2 bg-indigo-500/20 text-center">
                                <a href="/tournaments/<?= $tournament['id'] ?>/match/<?= $match['id'] ?>" 
                                   class="text-indigo-400 text-sm font-semibold hover:text-indigo-300">
                                    Start Match →
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($tournament['rewards'])): ?>
        <div class="glass rounded-3xl p-8 mt-8">
            <h2 class="text-2xl font-bold mb-6">Prize Pool</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($tournament['rewards'] as $reward): ?>
                <div class="bg-white/5 rounded-xl p-6 text-center <?= $reward['place'] === 1 ? 'ring-2 ring-amber-500/50' : '' ?>">
                    <div class="text-4xl mb-3">
                        <?= $reward['place'] === 1 ? '🥇' : ($reward['place'] === 2 ? '🥈' : ($reward['place'] === 3 ? '🥉' : '🏅')) ?>
                    </div>
                    <div class="text-lg font-bold mb-2">
                        <?= $reward['place'] === 1 ? '1st Place' : ($reward['place'] === 2 ? '2nd Place' : ($reward['place'] === 3 ? '3rd Place' : $reward['place'] . 'th Place')) ?>
                    </div>
                    <div class="space-y-1 text-sm">
                        <?php if ($reward['gold'] > 0): ?>
                        <div class="text-amber-400">💰 <?= number_format($reward['gold']) ?> Gold</div>
                        <?php endif; ?>
                        <?php if ($reward['gems'] > 0): ?>
                        <div class="text-cyan-400">💎 <?= number_format($reward['gems']) ?> Gems</div>
                        <?php endif; ?>
                        <?php if ($reward['lootbox_type']): ?>
                        <div class="text-purple-400">📦 <?= $reward['lootbox_count'] ?>x <?= ucfirst($reward['lootbox_type']) ?> Lootbox</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const csrfToken = '<?= $csrf_token ?>';

function joinTournament(id) {
    fetch(`/tournaments/${id}/join`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to join');
        }
    });
}

function leaveTournament(id) {
    if (!confirm('Are you sure you want to leave?')) return;
    
    fetch(`/tournaments/${id}/leave`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to leave');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
