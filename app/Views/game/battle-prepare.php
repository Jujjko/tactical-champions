<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-6xl mx-auto px-6">
        <a href="/missions" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            ← Back to Missions
        </a>

        <!-- Mission Header -->
        <div class="glass rounded-3xl p-10 mb-10 neon-glow">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="title-font text-5xl font-bold"><?= htmlspecialchars($mission['name']) ?></h1>
                    <span class="inline-block mt-4 px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl text-sm font-medium tracking-wide">
                        <?= strtoupper($mission['difficulty']) ?> • <?= $mission['enemy_count'] ?> ENEMIES
                    </span>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-3">
                        <span class="text-5xl">⚡</span>
                        <div>
                            <div class="text-5xl font-bold text-emerald-400"><?= $mission['energy_cost'] ?></div>
                            <div class="text-xs tracking-widest uppercase text-emerald-400/80">ENERGY</div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-white/70 mt-6 max-w-lg"><?= htmlspecialchars($mission['description']) ?></p>
        </div>

        <!-- Team Selection -->
        <div class="glass rounded-3xl p-10">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-semibold">Select Your Team <span class="text-white/50">(max 5)</span></h2>
                <div id="selected-count" class="text-3xl font-bold text-indigo-400 transition-all duration-300">0 / 5</div>
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
            <div id="champion-selection" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <?php foreach ($champions as $champion): ?>
                <div class="champion-select-card group relative cursor-pointer" data-champion-id="<?= $champion['id'] ?>">
                    
                    <!-- Glow border -->
                    <div class="absolute -inset-[3px] bg-gradient-to-br 
                        <?= $champion['tier'] === 'epic' ? 'from-purple-500 to-pink-500' : 
                           ($champion['tier'] === 'rare' ? 'from-blue-500 to-cyan-500' : 'from-zinc-500 to-slate-500') ?> 
                        opacity-0 group-hover:opacity-40 transition-all duration-500 rounded-[22px] -z-10"></div>

                    <div class="relative glass rounded-3xl overflow-hidden transition-all duration-300 group-hover:scale-[1.06]">
                        <div class="h-56 bg-gradient-to-br from-slate-900 to-slate-800 flex items-center justify-center relative overflow-hidden">
                            <?php if (!empty($champion['image_url'])): ?>
                                <img src="<?= htmlspecialchars($champion['image_url']) ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-8xl"><?= $champion['icon'] ?? '🛡️' ?></span>
                            <?php endif; ?>
                            <div class="absolute top-4 right-4">
                                <span class="tier-badge tier-<?= $champion['tier'] ?> text-xs px-5 py-1.5">
                                    <?= ucfirst($champion['tier']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <h4 class="font-semibold text-lg"><?= htmlspecialchars($champion['name']) ?></h4>
                            <div class="text-sm text-white/60">Lv.<?= $champion['level'] ?></div>

                            <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                                <div>❤️ <span class="font-bold text-emerald-400"><?= $champion['health'] ?></span></div>
                                <div>⚔️ <span class="font-bold text-rose-400"><?= $champion['attack'] ?></span></div>
                                <div>🛡️ <span class="font-bold text-amber-400"><?= $champion['defense'] ?></span></div>
                                <div>⚡ <span class="font-bold text-sky-400"><?= $champion['speed'] ?></span></div>
                            </div>
                        </div>

                        <!-- Selected Checkmark -->
                        <div class="select-check absolute top-5 right-5 w-10 h-10 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-3xl scale-0 shadow-2xl transition-all duration-300">
                            ✓
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button id="start-battle-btn" 
                    class="mt-12 w-full py-7 text-2xl font-bold bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 rounded-3xl disabled:opacity-40 hover:scale-[1.03] transition-all duration-300 shadow-2xl"
                    disabled>
                START BATTLE
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const selectedChampions = new Set();
const maxChampions = 5;
const countEl = document.getElementById('selected-count');
const startBtn = document.getElementById('start-battle-btn');

document.querySelectorAll('.champion-select-card').forEach(card => {
    card.addEventListener('click', () => {
        const id = card.dataset.championId;
        
        if (selectedChampions.has(id)) {
            selectedChampions.delete(id);
            card.querySelector('.select-check').classList.remove('scale-100', 'rotate-12');
            card.classList.remove('selected');
        } else if (selectedChampions.size < maxChampions) {
            selectedChampions.add(id);
            const check = card.querySelector('.select-check');
            check.classList.add('scale-100', 'rotate-12');
            card.classList.add('selected');
        } else {
            card.classList.add('animate-shake');
            setTimeout(() => card.classList.remove('animate-shake'), 600);
        }
        
        updateUI();
    });
});

function updateUI() {
    countEl.textContent = `${selectedChampions.size} / ${maxChampions}`;
    startBtn.disabled = selectedChampions.size === 0;
    
    countEl.classList.add('scale-125');
    setTimeout(() => countEl.classList.remove('scale-125'), 180);
}

document.getElementById('start-battle-btn')?.addEventListener('click', () => {
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

const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-8px); }
        40%, 80% { transform: translateX(8px); }
    }
    .animate-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
    .champion-select-card.selected .glass { border: 2px solid #22c55e; box-shadow: 0 0 30px rgba(34, 197, 94, 0.5); }
    .select-check.scale-100 { transform: scale(1) rotate(12deg); }
`;
document.head.appendChild(style);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
