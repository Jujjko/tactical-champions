<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-10">
            <h1 class="title-font text-5xl font-bold">PvP Season</h1>
            <p class="text-white/60 text-xl mt-2">Compete for rewards and glory!</p>
        </div>

        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">

        <!-- Active Season -->
        <?php if ($activeSeason): ?>
        <div class="glass rounded-3xl p-8 mb-8 neon-glow">
            <div class="text-center">
                <div class="text-sm text-indigo-400 uppercase tracking-widest mb-2">Active Season</div>
                <h2 class="text-4xl font-bold mb-4"><?= htmlspecialchars($activeSeason['name']) ?></h2>
                <p class="text-white/60 mb-6"><?= htmlspecialchars($activeSeason['description'] ?? '') ?></p>
                
                <!-- Countdown -->
                <div class="flex justify-center gap-4 mb-6">
                    <div class="bg-white/10 rounded-2xl p-4 min-w-[80px]">
                        <div class="text-4xl font-bold text-indigo-400"><?= $timeRemaining['days'] ?? 0 ?></div>
                        <div class="text-xs text-white/60 uppercase">Days</div>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-4 min-w-[80px]">
                        <div class="text-4xl font-bold text-indigo-400"><?= str_pad((string)($timeRemaining['hours'] ?? 0), 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="text-xs text-white/60 uppercase">Hours</div>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-4 min-w-[80px]">
                        <div class="text-4xl font-bold text-indigo-400"><?= str_pad((string)($timeRemaining['minutes'] ?? 0), 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="text-xs text-white/60 uppercase">Minutes</div>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-4 min-w-[80px]">
                        <div class="text-4xl font-bold text-indigo-400"><?= str_pad((string)($timeRemaining['seconds'] ?? 0), 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="text-xs text-white/60 uppercase">Seconds</div>
                    </div>
                </div>

                <a href="/arena" class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-bold text-lg hover:from-indigo-600 hover:to-purple-600 transition">
                    Enter Arena
                </a>
            </div>
        </div>

        <!-- Season Rewards Preview -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Season Rewards</h3>
            <div class="grid grid-cols-5 gap-4">
                <div class="text-center p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                    <div class="text-2xl mb-2">🥇</div>
                    <div class="font-bold text-yellow-400">Rank 1</div>
                    <div class="text-sm text-white/60 mt-2">10,000 Gold<br>100 Gems<br>5 Lootboxes</div>
                </div>
                <div class="text-center p-4 bg-gray-500/10 border border-gray-500/30 rounded-xl">
                    <div class="text-2xl mb-2">🥈</div>
                    <div class="font-bold text-gray-300">Rank 2</div>
                    <div class="text-sm text-white/60 mt-2">7,500 Gold<br>75 Gems<br>4 Lootboxes</div>
                </div>
                <div class="text-center p-4 bg-orange-500/10 border border-orange-500/30 rounded-xl">
                    <div class="text-2xl mb-2">🥉</div>
                    <div class="font-bold text-orange-400">Rank 3</div>
                    <div class="text-sm text-white/60 mt-2">5,000 Gold<br>50 Gems<br>3 Lootboxes</div>
                </div>
                <div class="text-center p-4 bg-white/5 rounded-xl">
                    <div class="font-bold">Ranks 4-10</div>
                    <div class="text-sm text-white/60 mt-2">1,000-4,000 Gold<br>10-40 Gems<br>1-2 Lootboxes</div>
                </div>
                <div class="text-center p-4 bg-white/5 rounded-xl">
                    <div class="font-bold">All Players</div>
                    <div class="text-sm text-white/60 mt-2">500 Gold<br>5 Gems</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Unclaimed Rewards -->
        <?php if (!empty($userRewards)): ?>
        <div class="glass rounded-2xl p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Your Season Rewards</h3>
            <div class="space-y-3">
                <?php foreach ($userRewards as $reward): ?>
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                    <div>
                        <div class="font-bold"><?= htmlspecialchars($reward['season_name']) ?></div>
                        <div class="text-sm text-white/60">
                            Final Rank: #<?= $reward['final_rank'] ?> (<?= $reward['final_rating'] ?> rating)
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-sm">
                            <span class="text-yellow-400">💰 <?= number_format($reward['reward_gold']) ?></span>
                            <span class="text-cyan-400 ml-2">💎 <?= $reward['reward_gems'] ?></span>
                            <?php if ($reward['reward_lootboxes'] > 0): ?>
                            <span class="text-purple-400 ml-2">📦 <?= $reward['reward_lootboxes'] ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$reward['claimed']): ?>
                        <button onclick="claimReward(<?= $reward['id'] ?>)" class="px-4 py-2 bg-emerald-500 rounded-xl font-semibold hover:bg-emerald-600 transition">
                            Claim
                        </button>
                        <?php else: ?>
                        <span class="px-4 py-2 bg-white/10 text-white/40 rounded-xl">Claimed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Season History -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-xl font-bold mb-4">Season History</h3>
            <?php if (empty($seasons)): ?>
            <p class="text-white/60 text-center py-8">No past seasons</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($seasons as $season): ?>
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                    <div>
                        <div class="font-bold"><?= htmlspecialchars($season['name']) ?></div>
                        <div class="text-sm text-white/60">
                            <?= date('M j, Y', strtotime($season['starts_at'])) ?> - <?= date('M j, Y', strtotime($season['ends_at'])) ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($season['is_active']): ?>
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-sm">Active</span>
                        <?php elseif ($season['rewards_distributed']): ?>
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm">Completed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;

function claimReward(rewardId) {
    fetch(`/season/rewards/${rewardId}/claim`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Rewards claimed!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to claim', 'error');
        }
    });
}

// Update countdown every second
setInterval(() => {
    const secondsEl = document.querySelector('.min-w-\\[80px\\]:last-child .text-4xl');
    if (secondsEl) {
        let seconds = parseInt(secondsEl.textContent);
        seconds = (seconds - 1 + 60) % 60;
        secondsEl.textContent = String(seconds).padStart(2, '0');
    }
}, 1000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';