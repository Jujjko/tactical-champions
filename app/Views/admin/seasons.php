<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">Season Management</h1>
                <p class="text-white/60 mt-2">Manage PvP seasons and rewards</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Back to Admin</a>
        </div>

        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">

        <!-- Active Season -->
        <?php if ($activeSeason): ?>
        <div class="glass rounded-2xl p-6 mb-8 border-2 border-indigo-500/50 neon-glow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-indigo-400 uppercase tracking-widest mb-1">Active Season</div>
                    <h2 class="text-3xl font-bold"><?= htmlspecialchars($activeSeason['name']) ?></h2>
                    <p class="text-white/60 mt-2"><?= htmlspecialchars($activeSeason['description'] ?? '') ?></p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-white/60">Ends at</div>
                    <div class="text-xl font-bold"><?= date('M j, Y', strtotime($activeSeason['ends_at'])) ?></div>
                    <div class="text-sm text-white/40"><?= date('H:i', strtotime($activeSeason['ends_at'])) ?></div>
                </div>
            </div>
            <div class="mt-6 flex gap-4">
                <button onclick="endSeason(<?= $activeSeason['id'] ?>)" class="px-6 py-3 bg-red-500/20 text-red-400 rounded-xl hover:bg-red-500/40 transition">
                    End Season & Distribute Rewards
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="glass rounded-2xl p-6 mb-8 text-center">
            <div class="text-6xl mb-4">📅</div>
            <h2 class="text-2xl font-bold mb-2">No Active Season</h2>
            <p class="text-white/60">Start a new season to enable PvP rankings</p>
        </div>
        <?php endif; ?>

        <!-- New Season Form -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Start New Season</h2>
            <form id="newSeasonForm" class="space-y-4">
                <div>
                    <label class="block text-sm text-white/60 mb-2">Season Name</label>
                    <input type="text" name="name" placeholder="Season 2: Rise of Legends" 
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-2">Description</label>
                    <textarea name="description" rows="2" placeholder="Season description..."
                              class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3"></textarea>
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-2">Duration (days)</label>
                    <input type="number" name="duration_days" value="30" min="7" max="90"
                           class="w-32 bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                </div>
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-bold hover:from-indigo-600 hover:to-purple-600 transition">
                    Start New Season
                </button>
            </form>
        </div>

        <!-- Season History -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-4">Season History</h2>
            <div class="space-y-3">
                <?php foreach ($seasons as $season): ?>
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                    <div>
                        <div class="font-bold"><?= htmlspecialchars($season['name']) ?></div>
                        <div class="text-sm text-white/60">
                            <?= date('M j', strtotime($season['starts_at'])) ?> - <?= date('M j, Y', strtotime($season['ends_at'])) ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <?php if ($season['is_active']): ?>
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-sm">Active</span>
                        <?php elseif ($season['rewards_distributed']): ?>
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm">Completed</span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-white/10 text-white/60 rounded-full text-sm">Ended</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;

function endSeason(seasonId) {
    if (!confirm('End this season and distribute rewards? This will reset all ratings.')) return;
    
    fetch('/admin/seasons/end', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&season_id=${seasonId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Season ended and rewards distributed!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to end season', 'error');
        }
    });
}

document.getElementById('newSeasonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/seasons/start', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData).toString() + `&csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('New season started!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to start season', 'error');
        }
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';