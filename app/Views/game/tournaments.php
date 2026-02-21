<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-12">
            <h1 class="title-font text-6xl font-bold mb-3">Tournaments</h1>
            <p class="text-2xl text-white/70">Compete for glory and exclusive rewards</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="glass rounded-3xl p-8 text-center">
                <div class="text-6xl mb-4">🏆</div>
                <div class="text-4xl font-bold text-amber-400"><?= count($tournaments) ?></div>
                <div class="text-white/60">Active Tournaments</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center">
                <div class="text-6xl mb-4">⚔️</div>
                <div class="text-4xl font-bold text-indigo-400"><?= count($my_tournaments) ?></div>
                <div class="text-white/60">Your Tournaments</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center">
                <div class="text-6xl mb-4">💎</div>
                <div class="text-4xl font-bold text-emerald-400">50K+</div>
                <div class="text-white/60">Total Prize Pool</div>
            </div>
        </div>

        <?php if (!empty($my_tournaments)): ?>
        <div class="mb-12">
            <h2 class="text-3xl font-bold mb-6">Your Tournaments</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($my_tournaments as $t): ?>
                <div class="glass rounded-3xl p-6 hover-lift">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($t['name']) ?></h3>
                        <span class="px-3 py-1 rounded-full text-sm <?= $t['status'] === 'ongoing' ? 'bg-emerald-500/20 text-emerald-400' : ($t['status'] === 'finished' ? 'bg-gray-500/20 text-gray-400' : 'bg-indigo-500/20 text-indigo-400') ?>">
                            <?= ucfirst($t['status']) ?>
                        </span>
                    </div>
                    <?php if ($t['status'] === 'finished'): ?>
                    <div class="text-sm text-white/60 mb-4">
                        Final Rank: #<?= $t['final_rank'] ?? 'N/A' ?> • 
                        W: <?= $t['wins'] ?> / L: <?= $t['losses'] ?>
                    </div>
                    <?php endif; ?>
                    <a href="/tournaments/<?= $t['id'] ?>" class="block text-center py-3 bg-white/10 rounded-xl hover:bg-white/20 transition">
                        View Details
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <h2 class="text-3xl font-bold mb-6">Open Tournaments</h2>
        
        <?php if (empty($tournaments)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">📅</div>
            <h3 class="text-2xl font-bold mb-2">No Active Tournaments</h3>
            <p class="text-white/60">Check back soon for new tournaments!</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($tournaments as $tournament): ?>
            <div class="glass rounded-3xl p-6 hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($tournament['name']) ?></h3>
                    <span class="px-3 py-1 rounded-full text-sm <?= $tournament['status'] === 'ongoing' ? 'bg-emerald-500/20 text-emerald-400' : ($tournament['status'] === 'full' ? 'bg-red-500/20 text-red-400' : 'bg-indigo-500/20 text-indigo-400') ?>">
                        <?= ucfirst($tournament['status']) ?>
                    </span>
                </div>
                
                <?php if ($tournament['description']): ?>
                <p class="text-white/60 text-sm mb-4"><?= htmlspecialchars($tournament['description']) ?></p>
                <?php endif; ?>
                
                <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                    <div>
                        <div class="text-white/50">Players</div>
                        <div class="font-bold"><?= $tournament['participant_count'] ?? 0 ?>/<?= $tournament['max_players'] ?></div>
                    </div>
                    <div>
                        <div class="text-white/50">Type</div>
                        <div class="font-bold"><?= ucfirst(str_replace('_', ' ', $tournament['type'])) ?></div>
                    </div>
                </div>
                
                <div class="flex gap-4 mb-4 text-sm">
                    <?php if ($tournament['entry_fee_gold'] > 0): ?>
                    <span class="text-amber-400">💰 <?= number_format($tournament['entry_fee_gold']) ?></span>
                    <?php endif; ?>
                    <?php if ($tournament['entry_fee_gems'] > 0): ?>
                    <span class="text-cyan-400">💎 <?= number_format($tournament['entry_fee_gems']) ?></span>
                    <?php endif; ?>
                    <?php if ($tournament['entry_fee_gold'] == 0 && $tournament['entry_fee_gems'] == 0): ?>
                    <span class="text-emerald-400">Free Entry</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($tournament['start_time']): ?>
                <div class="text-xs text-white/50 mb-4">
                    Starts: <?= date('M j, g:i A', strtotime($tournament['start_time'])) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($tournament['status'] === 'open' && !$tournament['is_registered']): ?>
                <button onclick="joinTournament(<?= $tournament['id'] ?>)" 
                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-bold hover:from-indigo-500 hover:to-purple-500 transition">
                    Join Tournament
                </button>
                <?php elseif ($tournament['is_registered']): ?>
                <div class="text-center py-3 bg-emerald-500/20 rounded-xl text-emerald-400 font-semibold">
                    ✓ Registered
                </div>
                <?php else: ?>
                <div class="text-center py-3 bg-white/10 rounded-xl text-white/50">
                    <?= $tournament['status'] === 'full' ? 'Tournament Full' : 'In Progress' ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const csrfToken = '<?= $csrf_token ?>';

function joinTournament(id) {
    if (!confirm('Are you sure you want to join this tournament?')) return;
    
    fetch(`/tournaments/${id}/join`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Failed to join tournament');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
