<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl text-6xl mb-4 neon-glow">📦</div>
            <h1 class="title-font text-5xl font-bold">Lootboxes</h1>
            <p class="text-white/60 text-xl mt-2"><?= count($lootboxes) ?> boxes waiting to be opened</p>
        </div>

        <?php if (empty($lootboxes)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold mb-2">No Lootboxes Available</h3>
            <p class="text-white/60 mb-6">Complete missions with lootbox chances to earn rewards!</p>
            <a href="/missions" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition">
                Start Missions
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($lootboxes as $lootbox): ?>
            <div class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-7xl mb-4 animate-pulse">📦</div>
                <h3 class="text-2xl font-bold capitalize mb-1"><?= $lootbox['lootbox_type'] ?> Lootbox</h3>
                <p class="text-white/50 text-sm mb-6">Acquired <?= date('M j, Y', strtotime($lootbox['acquired_at'])) ?></p>
                
                <button onclick="openLootbox(<?= $lootbox['id'] ?>)" 
                        class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-lg font-bold rounded-2xl transition transform hover:scale-105">
                    OPEN LOOTBOX
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reward Modal -->
<div id="rewardModal" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-[200]">
    <div class="glass rounded-3xl p-10 max-w-md w-full mx-4 text-center">
        <div id="rewardBody"></div>
    </div>
</div>

<script>
function openLootbox(lootboxId) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= \Core\Session::csrfToken() ?>');
    
    fetch(`/lootbox/${lootboxId}/open`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showRewards(data);
        } else {
            alert(data.error || 'Failed to open lootbox');
        }
    })
    .catch(err => alert('Error: ' + err.message));
}

function showRewards(data) {
    const html = `
        <div class="text-6xl mb-4 animate-bounce">🎁</div>
        <h3 class="text-3xl font-bold title-font mb-6">You Received!</h3>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white/5 rounded-2xl p-6">
                <div class="text-4xl mb-2">💰</div>
                <div class="text-3xl font-bold text-yellow-400">${data.rewards.gold}</div>
                <div class="text-xs text-white/60 uppercase">Gold</div>
            </div>
            <div class="bg-white/5 rounded-2xl p-6">
                <div class="text-4xl mb-2">💎</div>
                <div class="text-3xl font-bold text-cyan-400">${data.rewards.gems}</div>
                <div class="text-xs text-white/60 uppercase">Gems</div>
            </div>
        </div>
        
        ${data.champion ? `
            <div class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-2xl p-6 mb-6 border border-indigo-500/30">
                <div class="text-4xl mb-2">🛡️</div>
                <div class="text-2xl font-bold">${data.champion.name}</div>
                <span class="tier-${data.champion.tier} text-sm px-3 py-1 rounded-full text-white mt-2 inline-block">
                    ${data.champion.tier}
                </span>
                <div class="text-sm text-white/60 mt-2">New Champion!</div>
            </div>
        ` : ''}
        
        <button onclick="closeRewardModal()" 
                class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-lg font-bold rounded-2xl transition">
            Awesome!
        </button>
    `;
    
    document.getElementById('rewardBody').innerHTML = html;
    document.getElementById('rewardModal').classList.remove('hidden');
    document.getElementById('rewardModal').classList.add('flex');
}

function closeRewardModal() {
    document.getElementById('rewardModal').classList.add('hidden');
    document.getElementById('rewardModal').classList.remove('flex');
    location.reload();
}

document.getElementById('rewardModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('rewardModal')) {
        closeRewardModal();
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>