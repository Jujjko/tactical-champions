<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-6xl mx-auto px-6">
        <a href="/missions" class="inline-flex items-center gap-3 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Missions
        </a>

        <!-- Mission Header -->
        <div class="glass rounded-3xl p-8 mb-8 neon-glow">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h1 class="title-font text-4xl font-bold"><?= htmlspecialchars($mission['name']) ?></h1>
                    <span class="difficulty-<?= $mission['difficulty'] ?> text-lg px-6 py-2 rounded-full mt-4 inline-block text-white">
                        <?= strtoupper($mission['difficulty']) ?> • <?= $mission['enemy_count'] ?> ENEMIES
                    </span>
                </div>
                <div class="text-right">
                    <div class="text-emerald-400 text-5xl font-bold">⚡ <?= $mission['energy_cost'] ?></div>
                    <div class="uppercase text-sm tracking-widest text-white/60">Energy Cost</div>
                </div>
            </div>
            <p class="text-white/60 mt-4 max-w-lg"><?= htmlspecialchars($mission['description']) ?></p>
            
            <div class="grid grid-cols-3 gap-6 mt-6">
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-xs text-white/50 uppercase">Gold Reward</div>
                    <div class="text-2xl font-bold text-yellow-400">💰 <?= number_format($mission['gold_reward']) ?></div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-xs text-white/50 uppercase">XP Reward</div>
                    <div class="text-2xl font-bold text-indigo-400">✨ <?= $mission['experience_reward'] ?></div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-xs text-white/50 uppercase">Lootbox Chance</div>
                    <div class="text-2xl font-bold text-purple-400">📦 <?= $mission['lootbox_chance'] ?>%</div>
                </div>
            </div>
        </div>

        <!-- Team Selection -->
        <div class="glass rounded-3xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold">Select Your Team <span class="text-white/50">(max 5)</span></h2>
                <div id="selected-count" class="text-2xl font-bold text-indigo-400">0 / 5</div>
            </div>

            <?php if (empty($champions)): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🛡️</div>
                <h3 class="text-xl font-semibold mb-2">No Champions Yet!</h3>
                <p class="text-white/60 mb-6">Get champions from lootboxes to start battling</p>
                <a href="/lootbox" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-6 rounded-xl transition">
                    Open Lootboxes
                </a>
            </div>
            <?php else: ?>
            <div id="champion-selection" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php foreach ($champions as $champion): ?>
                <div class="champion-select-card glass rounded-2xl overflow-hidden cursor-pointer group transition-all" data-champion-id="<?= $champion['id'] ?>">
                    <div class="h-32 bg-gradient-to-br from-violet-900/50 to-fuchsia-900/50 flex items-center justify-center text-6xl group-hover:scale-110 transition">
                        🛡️
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-sm truncate"><?= htmlspecialchars($champion['name']) ?></h4>
                            <span class="tier-<?= $champion['tier'] ?> text-xs px-2 py-0.5 rounded-full text-white">
                                <?= ucfirst($champion['tier']) ?>
                            </span>
                        </div>
                        <div class="text-xs text-white/60 mb-2">Level <?= $champion['level'] ?></div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-emerald-400">❤️ <?= $champion['health'] ?></div>
                            <div class="text-rose-400">⚔️ <?= $champion['attack'] ?></div>
                            <div class="text-amber-400">🛡️ <?= $champion['defense'] ?></div>
                            <div class="text-cyan-400">⚡ <?= $champion['speed'] ?></div>
                        </div>
                    </div>
                    <div class="select-overlay bg-emerald-500 text-white text-2xl font-bold">✓</div>
                </div>
                <?php endforeach; ?>
            </div>

            <button id="start-battle-btn" 
                    class="mt-8 w-full py-5 text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-2xl transition disabled:opacity-40 disabled:cursor-not-allowed"
                    disabled>
                START BATTLE
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.champion-select-card {
    border: 2px solid transparent;
    position: relative;
}
.champion-select-card:hover {
    border-color: rgba(99, 102, 241, 0.5);
}
.champion-select-card.selected {
    border-color: #22c55e;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
}
.champion-select-card .select-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
}
.champion-select-card.selected .select-overlay {
    display: flex;
}
</style>

<script>
const selectedChampions = new Set();
const maxChampions = 5;

document.querySelectorAll('.champion-select-card').forEach(card => {
    card.addEventListener('click', () => {
        const championId = card.dataset.championId;
        
        if (card.classList.contains('selected')) {
            card.classList.remove('selected');
            selectedChampions.delete(championId);
        } else {
            if (selectedChampions.size < maxChampions) {
                card.classList.add('selected');
                selectedChampions.add(championId);
            } else {
                alert(`Maximum ${maxChampions} champions allowed!`);
            }
        }
        
        updateSelectedCount();
    });
});

function updateSelectedCount() {
    document.getElementById('selected-count').textContent = `${selectedChampions.size} / ${maxChampions}`;
    document.getElementById('start-battle-btn').disabled = selectedChampions.size === 0;
}

document.getElementById('start-battle-btn').addEventListener('click', () => {
    if (selectedChampions.size === 0) return;
    
    const formData = new FormData();
    formData.append('mission_id', <?= $mission['id'] ?>);
    formData.append('csrf_token', '<?= \Core\Session::csrfToken() ?>');
    
    Array.from(selectedChampions).forEach((championId, index) => {
        formData.append(`champions[${index}]`, championId);
    });
    
    fetch('/battle/start', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/battle/arena';
        } else {
            alert(data.error || 'Failed to start battle');
        }
    })
    .catch(err => alert('Error: ' + err.message));
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>