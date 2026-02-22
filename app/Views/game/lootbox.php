<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl text-6xl mb-4 neon-glow">📦</div>
            <h1 class="title-font text-5xl font-bold">Lootboxes</h1>
            <p class="text-white/60 text-xl mt-2"><span id="total-count"><?= $counts['total'] ?></span> boxes waiting to be opened</p>
        </div>

        <?php if ($counts['total'] === 0): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold mb-2">No Lootboxes Available</h3>
            <p class="text-white/60 mb-6">Complete missions with lootbox chances to earn rewards!</p>
            <a href="/missions" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition">
                Start Missions
            </a>
        </div>
        <?php else: ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php 
            $types = [
                'bronze' => [
                    'name' => 'Bronze',
                    'gradient' => 'from-amber-700 to-orange-800',
                    'border' => 'border-amber-600/30'
                ],
                'silver' => [
                    'name' => 'Silver',
                    'gradient' => 'from-slate-400 to-slate-600',
                    'border' => 'border-slate-400/30'
                ],
                'gold' => [
                    'name' => 'Gold',
                    'gradient' => 'from-yellow-400 to-amber-500',
                    'border' => 'border-yellow-500/30'
                ],
                'diamond' => [
                    'name' => 'Diamond',
                    'gradient' => 'from-cyan-300 to-blue-500',
                    'border' => 'border-cyan-400/30'
                ]
            ];
            
            foreach ($types as $type => $config): 
                $count = $counts[$type] ?? 0;
            ?>
            <div class="glass rounded-3xl p-6 border <?= $config['border'] ?> <?= $count === 0 ? 'opacity-40' : '' ?>" data-type="<?= $type ?>">
                <div class="flex items-center gap-4 mb-4">
                    <div class="relative w-20 h-20">
                        <div class="absolute inset-0 bg-gradient-to-br <?= $config['gradient'] ?> rounded-xl transform rotate-3 shadow-lg"></div>
                        <div class="absolute inset-0 bg-gradient-to-br <?= $config['gradient'] ?> rounded-xl shadow-xl flex items-center justify-center">
                            <div class="absolute top-2 left-2 right-2 h-1 bg-white/30 rounded"></div>
                            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/20 to-transparent rounded-t-xl"></div>
                            <i data-lucide="gift" class="w-8 h-8 text-white/90"></i>
                        </div>
                        <div class="absolute -inset-2 bg-gradient-to-br <?= $config['gradient'] ?> opacity-30 blur-xl -z-10"></div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold"><?= $config['name'] ?> Lootbox</h3>
                        <p class="text-white/60">
                            <span class="lootbox-count font-bold text-white"><?= $count ?></span> available
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-2">
                    <button onclick="openMultiple('<?= $type ?>', 1)" 
                            class="py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition disabled:opacity-40"
                            <?= $count < 1 ? 'disabled' : '' ?>>
                        x1
                    </button>
                    <button onclick="openMultiple('<?= $type ?>', 10)" 
                            class="py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition disabled:opacity-40"
                            <?= $count < 10 ? 'disabled' : '' ?>>
                        x10
                    </button>
                    <button onclick="openMultiple('<?= $type ?>', 50)" 
                            class="py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition disabled:opacity-40"
                            <?= $count < 50 ? 'disabled' : '' ?>>
                        x50
                    </button>
                    <button onclick="openAll('<?= $type ?>')" 
                            class="py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl font-semibold transition disabled:opacity-40"
                            <?= $count < 1 ? 'disabled' : '' ?>>
                        ALL
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Open All Types -->
        <div class="mt-6 glass rounded-3xl p-6 text-center">
            <button id="openAllBtn" onclick="openAllTypes()" 
                    class="px-12 py-4 bg-gradient-to-r from-purple-500 via-pink-500 to-rose-500 hover:from-purple-600 hover:via-pink-600 hover:to-rose-600 text-xl font-bold rounded-2xl transition transform hover:scale-105 disabled:opacity-40"
                    <?= $counts['total'] < 1 ? 'disabled' : '' ?>>
                🎁 Open All Lootboxes (<?= $counts['total'] ?>)
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reward Modal -->
<div id="rewardModal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[200] p-4">
    <div class="glass rounded-3xl p-8 max-w-lg w-full text-center max-h-[90vh] overflow-y-auto">
        <div id="rewardBody"></div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[200] p-4">
    <div class="glass rounded-3xl p-8 max-w-md w-full text-center border border-amber-500/30">
        <div class="text-6xl mb-4">📦</div>
        <h3 class="text-2xl font-bold title-font mb-3">Open All Lootboxes?</h3>
        <p class="text-white/70 mb-2">You're about to open</p>
        <div class="text-4xl font-bold text-amber-400 mb-4" id="confirmCount">0</div>
        <p class="text-white/50 text-sm mb-6">This might take a moment to process</p>
        
        <div class="flex gap-3">
            <button onclick="closeConfirmModal()" 
                    class="flex-1 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition">
                Cancel
            </button>
            <button id="confirmBtn" 
                    class="flex-1 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl font-semibold transition">
                Open All
            </button>
        </div>
    </div>
</div>

<style>
#rewardModal::-webkit-scrollbar {
    width: 8px;
}
#rewardModal::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}
#rewardModal::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 4px;
}
</style>

<script>
const csrf = '<?= \Core\Session::csrfToken() ?>';
let pendingAction = null;

function showConfirm(count, callback) {
    document.getElementById('confirmCount').textContent = count.toLocaleString() + ' lootboxes';
    document.getElementById('confirmModal').classList.remove('hidden');
    document.getElementById('confirmModal').classList.add('flex');
    pendingAction = callback;
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('flex');
    pendingAction = null;
}

document.getElementById('confirmBtn').addEventListener('click', () => {
    closeConfirmModal();
    if (pendingAction) pendingAction();
});

document.getElementById('confirmModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('confirmModal')) {
        closeConfirmModal();
    }
});

function openMultiple(type, count, btn = null) {
    if (!btn) btn = event?.target;
    const originalText = btn ? btn.textContent : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-pulse">...</span>';
    }
    
    fetch('/lootbox/open-multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&type=${type}&count=${count}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showRewards(data);
            updateCounts(data.remaining);
        } else {
            showToast(data.error || 'Failed to open lootboxes', 'error');
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }
    })
    .catch(err => {
        showToast('Error: ' + err.message, 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

function updateCounts(remaining) {
    if (!remaining) return;
    
    const types = ['bronze', 'silver', 'gold', 'diamond'];
    types.forEach(type => {
        if (remaining[type] !== undefined) {
            const card = document.querySelector(`[data-type="${type}"]`);
            if (card) {
                const count = remaining[type];
                const countEl = card.querySelector('.lootbox-count');
                if (countEl) countEl.textContent = count;
                
                const buttons = card.querySelectorAll('button');
                if (buttons[0]) buttons[0].disabled = count < 1;
                if (buttons[1]) buttons[1].disabled = count < 10;
                if (buttons[2]) buttons[2].disabled = count < 50;
                if (buttons[3]) buttons[3].disabled = count < 1;
                
                if (count === 0) card.classList.add('opacity-40');
            }
        }
    });
    
    const totalEl = document.getElementById('total-count');
    if (totalEl) totalEl.textContent = remaining.total || 0;
    
    const openAllBtn = document.getElementById('openAllBtn');
    if (openAllBtn) {
        openAllBtn.disabled = (remaining.total || 0) < 1;
        openAllBtn.textContent = `🎁 Open All Lootboxes (${remaining.total || 0})`;
    }
}

function openAll(type) {
    const card = document.querySelector(`[data-type="${type}"]`);
    if (!card) return;
    
    const countEl = card.querySelector('.lootbox-count');
    const count = countEl ? parseInt(countEl.textContent) : 0;
    const btn = card.querySelector('button:last-child');
    
    if (count > 50) {
        showConfirm(count, () => openMultiple(type, count, btn));
    } else {
        openMultiple(type, count, btn);
    }
}

function openAllTypes() {
    const totalEl = document.getElementById('total-count');
    const total = totalEl ? parseInt(totalEl.textContent) : 0;
    
    if (total < 1) {
        showToast('No lootboxes to open', 'error');
        return;
    }
    
    const executeOpen = () => {
        const btn = document.getElementById('openAllBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-pulse">Opening all...</span>';
        }
        
        const types = ['bronze', 'silver', 'gold', 'diamond'];
        let promises = [];
        
        types.forEach(type => {
            const card = document.querySelector(`[data-type="${type}"]`);
            if (card) {
                const countEl = card.querySelector('.lootbox-count');
                const count = countEl ? parseInt(countEl.textContent) : 0;
                if (count > 0) {
                    promises.push(
                        fetch('/lootbox/open-multiple', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `csrf_token=${csrf}&type=${type}&count=${count}`
                        }).then(res => res.json())
                    );
                }
            }
        });
        
        if (promises.length === 0) {
            showToast('No lootboxes to open', 'error');
            if (btn) {
                btn.disabled = false;
                btn.textContent = `🎁 Open All Lootboxes (${total})`;
            }
            return;
        }
        
        Promise.all(promises)
        .then(results => {
            const lastRemaining = results[results.length - 1]?.remaining || { total: 0 };
            const combined = {
                success: true,
                opened: results.reduce((sum, r) => sum + (r.opened || 0), 0),
                rewards: {
                    gold: results.reduce((sum, r) => sum + (r.rewards?.gold || 0), 0),
                    gems: results.reduce((sum, r) => sum + (r.rewards?.gems || 0), 0)
                },
                champions: results.flatMap(r => r.champions || []),
                by_type: results.reduce((acc, r) => ({ ...acc, ...r.by_type }), {})
            };
            showRewards(combined);
            updateCounts(lastRemaining);
        })
        .catch(err => {
            showToast('Error: ' + err.message, 'error');
            if (btn) {
                btn.disabled = false;
                btn.textContent = `🎁 Open All Lootboxes (${total})`;
            }
        });
    };
    
    if (total > 50) {
        showConfirm(total, executeOpen);
    } else {
        executeOpen();
    }
}

function showRewards(data) {
    let typeBreakdown = '';
    if (data.by_type && Object.keys(data.by_type).length > 0) {
        const typeLabels = { bronze: 'Bronze', silver: 'Silver', gold: 'Gold', diamond: 'Diamond' };
        const typeColors = { 
            bronze: 'from-amber-700 to-orange-800', 
            silver: 'from-slate-400 to-slate-600', 
            gold: 'from-yellow-400 to-amber-500', 
            diamond: 'from-cyan-300 to-blue-500' 
        };
        typeBreakdown = '<div class="grid grid-cols-2 gap-2 mb-4">';
        for (const [type, info] of Object.entries(data.by_type)) {
            typeBreakdown += `
                <div class="bg-gradient-to-r ${typeColors[type] || 'from-slate-500 to-slate-600'} rounded-xl p-3 text-sm">
                    <div class="font-semibold text-white">${typeLabels[type] || type}</div>
                    <div class="text-white/80">x${info.count} opened</div>
                </div>
            `;
        }
        typeBreakdown += '</div>';
    }
    
    let championsHtml = '';
    if (data.champions && data.champions.length > 0) {
        const tierOrder = ['mythic', 'legendary', 'epic', 'rare', 'common'];
        const tierColors = {
            mythic: 'border-red-500/50 bg-red-500/10',
            legendary: 'border-amber-500/50 bg-amber-500/10',
            epic: 'border-purple-500/50 bg-purple-500/10',
            rare: 'border-blue-500/50 bg-blue-500/10',
            common: 'border-slate-500/50 bg-slate-500/10'
        };
        const tierTextColors = {
            mythic: 'text-red-400',
            legendary: 'text-amber-400',
            epic: 'text-purple-400',
            rare: 'text-blue-400',
            common: 'text-slate-400'
        };
        
        const grouped = {};
        data.champions.forEach(c => {
            const tier = c.tier || 'common';
            const name = c.name || 'Unknown';
            if (!grouped[tier]) grouped[tier] = {};
            if (!grouped[tier][name]) grouped[tier][name] = 0;
            grouped[tier][name]++;
        });
        
        let championGroups = '';
        tierOrder.forEach(tier => {
            if (grouped[tier] && Object.keys(grouped[tier]).length > 0) {
                const champions = Object.entries(grouped[tier]).sort((a, b) => b[1] - a[1]);
                const total = champions.reduce((sum, [, c]) => sum + c, 0);
                
                championGroups += `
                    <div class="border ${tierColors[tier]} rounded-xl p-3 mb-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold capitalize ${tierTextColors[tier]}">${tier}</span>
                            <span class="text-sm text-white/60">${total} champion${total > 1 ? 's' : ''}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            ${champions.map(([name, c]) => `
                                <span class="bg-white/10 rounded-lg px-2 py-1 text-sm flex items-center gap-1">
                                    🛡️ ${name}${c > 1 ? ` <span class="${tierTextColors[tier]} font-bold">x${c}</span>` : ''}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        });
        
        championsHtml = `
            <div class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-2xl p-4 mb-4 border border-indigo-500/30">
                <div class="text-lg font-bold mb-3">🎉 Champions Won: ${data.champions.length}</div>
                ${championGroups}
            </div>
        `;
    }
    
    const html = `
        <div class="text-5xl mb-4 animate-bounce">🎁</div>
        <h3 class="text-2xl font-bold title-font mb-2">Lootboxes Opened!</h3>
        <p class="text-white/60 mb-6">${data.opened || 0} boxes opened</p>
        
        ${typeBreakdown}
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-white/5 rounded-2xl p-4">
                <div class="text-3xl mb-1">💰</div>
                <div class="text-2xl font-bold text-yellow-400">${(data.rewards?.gold || 0).toLocaleString()}</div>
                <div class="text-xs text-white/60 uppercase">Gold</div>
            </div>
            <div class="bg-white/5 rounded-2xl p-4">
                <div class="text-3xl mb-1">💎</div>
                <div class="text-2xl font-bold text-cyan-400">${(data.rewards?.gems || 0).toLocaleString()}</div>
                <div class="text-xs text-white/60 uppercase">Gems</div>
            </div>
        </div>
        
        ${championsHtml}
        
        <button onclick="closeRewardModal()" 
                class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-lg font-bold rounded-2xl transition hover:from-indigo-600 hover:to-purple-700">
            Awesome!
        </button>
    `;
    
    document.getElementById('rewardBody').innerHTML = html;
    document.getElementById('rewardModal').classList.remove('hidden');
    document.getElementById('rewardModal').classList.add('flex');
    lucide.createIcons();
}

function closeRewardModal() {
    document.getElementById('rewardModal').classList.add('hidden');
    document.getElementById('rewardModal').classList.remove('flex');
    
    const totalEl = document.getElementById('total-count');
    const total = totalEl ? parseInt(totalEl.textContent) : 0;
    if (total < 1) {
        location.reload();
    }
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
