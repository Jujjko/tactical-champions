<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <a href="/champions/<?= $champion['id'] ?>" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Champion
        </a>

        <div class="text-center mb-10">
            <h1 class="title-font text-5xl font-bold mb-4">Champion Fusion</h1>
            <p class="text-white/60 text-xl">Merge two identical champions to increase star level and power!</p>
        </div>

        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
        <input type="hidden" id="target_id" value="<?= $champion['id'] ?>">

        <?php if ($champion['stars'] >= 5): ?>
        <div class="glass rounded-3xl p-8 text-center">
            <div class="text-6xl mb-4">🏆</div>
            <h2 class="text-3xl font-bold mb-4">Max Stars Reached!</h2>
            <p class="text-white/60 mb-6">This champion is already at maximum star level.</p>
            <a href="/champions/<?= $champion['id'] ?>" class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold">
                Back to Champion
            </a>
        </div>
        <?php elseif (empty($fusionCandidates)): ?>
        <div class="glass rounded-3xl p-8 text-center">
            <div class="text-6xl mb-4">😔</div>
            <h2 class="text-3xl font-bold mb-4">No Fusion Candidates</h2>
            <p class="text-white/60 mb-6">You need another <strong><?= htmlspecialchars($champion['name']) ?></strong> with the same star level (<?= $champion['stars'] ?>⭐) to perform fusion.</p>
            <a href="/lootbox" class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold">
                Open Lootbox
            </a>
        </div>
        <?php else: ?>
        
        <!-- Fusion Cost -->
        <div class="glass rounded-2xl p-6 mb-8 text-center">
            <h3 class="text-xl font-bold mb-4">Fusion Cost</h3>
            <div class="flex justify-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="text-3xl">💰</span>
                    <div>
                        <div class="text-2xl font-bold <?= $fusionInfo['has_enough_gold'] ? 'text-yellow-400' : 'text-red-400' ?>">
                            <?= number_format($fusionInfo['gold_cost']) ?>
                        </div>
                        <div class="text-xs text-white/60">Gold</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-3xl">💎</span>
                    <div>
                        <div class="text-2xl font-bold <?= $fusionInfo['has_enough_gems'] ? 'text-cyan-400' : 'text-red-400' ?>">
                            <?= number_format($fusionInfo['gems_cost']) ?>
                        </div>
                        <div class="text-xs text-white/60">Gems</div>
                    </div>
                </div>
            </div>
            <?php if (!$fusionInfo['has_enough_gold'] || !$fusionInfo['has_enough_gems']): ?>
            <p class="text-red-400 text-sm mt-4">You don't have enough resources for fusion.</p>
            <?php endif; ?>
        </div>

        <!-- Fusion Preview -->
        <div class="glass rounded-3xl p-8 mb-8">
            <div class="flex items-center justify-center gap-8">
                <!-- Target Champion -->
                <div class="text-center">
                    <div class="glass rounded-2xl p-6 w-48">
                        <div class="h-32 bg-gradient-to-br from-violet-900 to-purple-900 rounded-xl flex items-center justify-center text-5xl mb-3">
                            🛡️
                        </div>
                        <div class="font-bold"><?= htmlspecialchars($champion['name']) ?></div>
                        <div class="text-sm text-white/60">Level <?= $champion['level'] ?></div>
                        <?= \App\Services\FusionService::getStarsHtml((int)$champion['stars']) ?>
                    </div>
                    <div class="mt-2 text-indigo-400 font-semibold">Base</div>
                </div>

                <div class="text-4xl">+</div>

                <!-- Material Champion Selection -->
                <div class="text-center">
                    <div class="glass rounded-2xl p-6 w-48" id="materialPreview">
                        <div class="h-32 bg-gradient-to-br from-yellow-900 to-orange-900 rounded-xl flex items-center justify-center text-5xl mb-3">
                            ?
                        </div>
                        <div class="font-bold">Select Material</div>
                        <div class="text-sm text-white/60">Choose below</div>
                    </div>
                    <div class="mt-2 text-yellow-400 font-semibold">Material</div>
                </div>

                <div class="text-4xl">=</div>

                <!-- Result Preview -->
                <div class="text-center">
                    <div class="glass rounded-2xl p-6 w-48 border-2 border-yellow-500/50 neon-glow">
                        <div class="h-32 bg-gradient-to-br from-yellow-900 to-orange-900 rounded-xl flex items-center justify-center text-5xl mb-3">
                            🛡️
                        </div>
                        <div class="font-bold"><?= htmlspecialchars($champion['name']) ?></div>
                        <div class="text-sm text-white/60">Level <?= $champion['level'] ?></div>
                        <?= \App\Services\FusionService::getStarsHtml((int)$champion['stars'] + 1) ?>
                    </div>
                    <div class="mt-2 text-yellow-400 font-semibold">Result</div>
                </div>
            </div>
        </div>

        <!-- Material Candidates -->
        <div class="glass rounded-3xl p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Select Material Champion</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($fusionCandidates as $candidate): ?>
                <div class="glass rounded-xl p-4 cursor-pointer transition hover:bg-white/10 candidate-card" 
                     data-id="<?= $candidate['id'] ?>"
                     data-name="<?= htmlspecialchars($candidate['name']) ?>"
                     data-level="<?= $candidate['level'] ?>"
                     onclick="selectMaterial(<?= $candidate['id'] ?>, '<?= htmlspecialchars($candidate['name']) ?>', <?= $candidate['level'] ?>)">
                    <div class="h-20 bg-gradient-to-br from-yellow-900/50 to-orange-900/50 rounded-lg flex items-center justify-center text-3xl mb-2">
                        🛡️
                    </div>
                    <div class="font-semibold text-sm"><?= htmlspecialchars($candidate['name']) ?></div>
                    <div class="text-xs text-white/60">Level <?= $candidate['level'] ?></div>
                    <?= \App\Services\FusionService::getStarsHtml((int)$candidate['stars']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Fusion Button -->
        <div class="text-center">
            <button id="fusionBtn" onclick="performFusion()" disabled
                    class="px-12 py-5 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl text-xl font-bold transition disabled:opacity-50 disabled:cursor-not-allowed hover:from-yellow-600 hover:to-orange-600">
                Perform Fusion
            </button>
            <p class="text-white/40 text-sm mt-3">Select a material champion to enable fusion</p>
        </div>

        <!-- Star Bonus Info -->
        <div class="mt-12 glass rounded-2xl p-6">
            <h3 class="text-xl font-bold mb-4 text-center">Star Level Bonuses</h3>
            <div class="grid grid-cols-5 gap-4">
                <?php 
                $bonuses = [1 => '0%', '10%', '25%', '45%', '70%'];
                for ($i = 1; $i <= 5; $i++): 
                ?>
                <div class="text-center p-4 rounded-xl <?= $i === $champion['stars'] + 1 ? 'bg-yellow-500/20 border border-yellow-500/50' : 'bg-white/5' ?>">
                    <div class="text-2xl mb-2"><?= str_repeat('⭐', $i) ?></div>
                    <div class="font-bold"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></div>
                    <div class="text-sm <?= $i === $champion['stars'] + 1 ? 'text-yellow-400' : 'text-white/60' ?>">
                        +<?= $bonuses[$i] ?> stats
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
let selectedMaterialId = null;

function selectMaterial(id, name, level) {
    selectedMaterialId = id;
    
    document.querySelectorAll('.candidate-card').forEach(card => {
        card.classList.remove('ring-2', 'ring-yellow-500');
    });
    
    const selectedCard = document.querySelector(`.candidate-card[data-id="${id}"]`);
    if (selectedCard) {
        selectedCard.classList.add('ring-2', 'ring-yellow-500');
    }
    
    const preview = document.getElementById('materialPreview');
    preview.innerHTML = `
        <div class="h-32 bg-gradient-to-br from-yellow-900 to-orange-900 rounded-xl flex items-center justify-center text-5xl mb-3">🛡️</div>
        <div class="font-bold">${name}</div>
        <div class="text-sm text-white/60">Level ${level}</div>
        <div class="text-yellow-400 text-sm mt-1">Will be consumed</div>
    `;
    
    const btn = document.getElementById('fusionBtn');
    btn.disabled = false;
    btn.classList.add('neon-glow');
}

function performFusion() {
    if (!selectedMaterialId) return;
    
    if (!confirm('Are you sure? The material champion will be permanently consumed.')) return;
    
    const csrf = document.getElementById('csrf_token').value;
    const targetId = document.getElementById('target_id').value;
    
    fetch('/champions/fusion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&target_id=${targetId}&material_id=${selectedMaterialId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`Fusion successful! Champion is now ${data.data.new_stars}⭐!`, 'success');
            setTimeout(() => window.location.href = `/champions/${targetId}`, 1500);
        } else {
            showToast(data.error || 'Fusion failed', 'error');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';