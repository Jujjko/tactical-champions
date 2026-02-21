<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-6">
    <div class="max-w-6xl mx-auto px-4">
        <div class="glass rounded-3xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="text-4xl">⚔️</div>
                    <div>
                        <div class="text-2xl font-bold title-font" id="turn-counter">Turn <?= $battle_state['turn'] ?? 1 ?></div>
                        <div class="text-orange-400">PvP Battle • VS <?= htmlspecialchars($opponent_name) ?></div>
                    </div>
                </div>
                <div class="text-right">
                    <div id="battle-timer" class="text-2xl font-bold text-indigo-400">0:00</div>
                    <div class="text-white/50 text-sm">Battle Duration</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="glass rounded-3xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="text-2xl">🛡️</div>
                    <div>
                        <div class="text-xl font-bold">Your Team</div>
                        <div class="text-emerald-400 text-sm">Player</div>
                    </div>
                </div>
                <div id="player-team" class="space-y-3">
                    <?php foreach ($battle_state['player_team'] ?? [] as $champion): ?>
                    <div class="champion-slot bg-white/5 rounded-2xl p-4" data-champion-id="<?= $champion['id'] ?>">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold <?= $champion['alive'] ? '' : 'text-white/30 line-through' ?>">
                                <?= htmlspecialchars($champion['name']) ?>
                            </div>
                            <div class="text-sm <?= $champion['alive'] ? 'text-emerald-400' : 'text-red-400' ?>">
                                <?= $champion['alive'] ? 'Active' : 'Defeated' ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="flex justify-between text-xs mb-1">
                                <span>HP</span>
                                <span><?= max(0, $champion['current_health']) ?>/<?= $champion['max_health'] ?></span>
                            </div>
                            <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full <?= $champion['alive'] ? 'bg-gradient-to-r from-emerald-500 to-green-400' : 'bg-gray-500' ?>" 
                                     style="width: <?= max(0, ($champion['current_health'] / $champion['max_health']) * 100) ?>%"></div>
                            </div>
                        </div>
                        <div class="flex gap-4 text-xs text-white/60">
                            <span>⚔️ <?= $champion['attack'] ?></span>
                            <span>🛡️ <?= $champion['defense'] ?></span>
                            <span>⚡ <?= $champion['speed'] ?></span>
                            <?php if ($champion['ability_cooldown'] > 0): ?>
                            <span class="text-orange-400">CD: <?= $champion['ability_cooldown'] ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($champion['stunned']): ?>
                        <div class="mt-2 text-xs text-yellow-400">💫 Stunned</div>
                        <?php endif; ?>
                        <?php if ($champion['frozen']): ?>
                        <div class="mt-2 text-xs text-cyan-400">❄️ Frozen</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="glass rounded-3xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="text-2xl">👹</div>
                    <div>
                        <div class="text-xl font-bold">Enemy Team</div>
                        <div class="text-red-400 text-sm"><?= htmlspecialchars($opponent_name) ?></div>
                    </div>
                </div>
                <div id="enemy-team" class="space-y-3">
                    <?php foreach ($battle_state['enemy_team'] ?? [] as $champion): ?>
                    <div class="champion-slot bg-white/5 rounded-2xl p-4 cursor-pointer hover:bg-white/10 transition <?= $champion['alive'] ? 'targetable' : 'opacity-50' ?>" 
                         data-champion-id="<?= $champion['id'] ?>"
                         onclick="selectTarget(<?= $champion['id'] ?>)">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold <?= $champion['alive'] ? '' : 'text-white/30 line-through' ?>">
                                <?= htmlspecialchars($champion['name']) ?>
                            </div>
                            <div class="text-sm <?= $champion['alive'] ? 'text-red-400' : 'text-gray-400' ?>">
                                <?= $champion['alive'] ? 'Enemy' : 'Defeated' ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="flex justify-between text-xs mb-1">
                                <span>HP</span>
                                <span><?= max(0, $champion['current_health']) ?>/<?= $champion['max_health'] ?></span>
                            </div>
                            <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full <?= $champion['alive'] ? 'bg-gradient-to-r from-red-500 to-orange-400' : 'bg-gray-500' ?>" 
                                     style="width: <?= max(0, ($champion['current_health'] / $champion['max_health']) * 100) ?>%"></div>
                            </div>
                        </div>
                        <div class="flex gap-4 text-xs text-white/60">
                            <span>⚔️ <?= $champion['attack'] ?></span>
                            <span>🛡️ <?= $champion['defense'] ?></span>
                            <span>⚡ <?= $champion['speed'] ?></span>
                        </div>
                        <?php if ($champion['stunned']): ?>
                        <div class="mt-2 text-xs text-yellow-400">💫 Stunned</div>
                        <?php endif; ?>
                        <?php if ($champion['frozen']): ?>
                        <div class="mt-2 text-xs text-cyan-400">❄️ Frozen</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="glass rounded-3xl p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="text-2xl">📜</div>
                <div class="text-xl font-bold">Battle Log</div>
            </div>
            <div id="battle-log" class="max-h-48 overflow-y-auto space-y-2 text-sm">
                <?php foreach ($battle_state['battle_log'] ?? [] as $log): ?>
                <div class="p-2 bg-white/5 rounded-lg">
                    <span class="text-white/50">[Turn <?= $log['turn'] ?>]</span>
                    <?= htmlspecialchars($log['message']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="glass rounded-3xl p-6" id="action-panel">
            <div class="text-center mb-4">
                <div class="text-xl font-bold">Select Action</div>
                <div class="text-white/50 text-sm" id="selected-attacker">Choose your champion and target</div>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="executeAction('attack')" 
                        id="btn-attack"
                        class="py-4 px-6 bg-gradient-to-r from-red-600 to-orange-600 rounded-2xl font-bold hover:from-red-500 hover:to-orange-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="text-2xl mb-1">⚔️</div>
                    <div>Attack</div>
                </button>
                
                <?php 
                $playerChamp = $battle_state['player_team'][0] ?? [];
                $canUseAbility = ($playerChamp['ability_cooldown'] ?? 0) <= 0 && ($playerChamp['alive'] ?? false);
                ?>
                <button onclick="executeAction('attack', true)" 
                        id="btn-ability"
                        class="py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl font-bold hover:from-purple-500 hover:to-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        <?= $canUseAbility ? '' : 'disabled' ?>>
                    <div class="text-2xl mb-1">✨</div>
                    <div>Ability</div>
                    <?php if (!$canUseAbility && ($playerChamp['ability_cooldown'] ?? 0) > 0): ?>
                    <div class="text-xs text-white/50">CD: <?= $playerChamp['ability_cooldown'] ?></div>
                    <?php endif; ?>
                </button>
                
                <button onclick="executeAction('defend')" 
                        id="btn-defend"
                        class="py-4 px-6 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl font-bold hover:from-blue-500 hover:to-cyan-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="text-2xl mb-1">🛡️</div>
                    <div>Defend</div>
                </button>
                
                <a href="/pvp" 
                   class="py-4 px-6 bg-white/10 rounded-2xl font-bold hover:bg-white/20 transition-all text-center">
                    <div class="text-2xl mb-1">🏃</div>
                    <div>Flee</div>
                </a>
            </div>
            
            <input type="hidden" id="selected-target" value="">
            <input type="hidden" id="csrf-token" value="<?= $csrf_token ?>">
        </div>
    </div>
</div>

<div id="battle-result-modal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 hidden">
    <div class="glass rounded-3xl p-10 max-w-md text-center">
        <div id="result-icon" class="text-8xl mb-6"></div>
        <h2 id="result-title" class="text-4xl font-bold mb-4"></h2>
        <div id="result-stats" class="mb-6"></div>
        <a href="/pvp" class="block py-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl font-bold hover:from-indigo-500 hover:to-purple-500">
            Return to Arena
        </a>
    </div>
</div>

<script>
let selectedTarget = null;
let battleStartTime = Date.now();
const csrfToken = '<?= $csrf_token ?>';

function selectTarget(targetId) {
    const targetEl = document.querySelector(`[data-champion-id="${targetId}"].targetable`);
    if (!targetEl) return;
    
    document.querySelectorAll('.champion-slot').forEach(el => el.classList.remove('ring-2', 'ring-red-500'));
    targetEl.classList.add('ring-2', 'ring-red-500');
    
    selectedTarget = targetId;
    document.getElementById('selected-target').value = targetId;
    document.getElementById('selected-attacker').textContent = `Target selected: ${targetEl.querySelector('.font-semibold').textContent}`;
}

function executeAction(action, useAbility = false) {
    const attackerId = '<?= $battle_state['player_team'][0]['id'] ?? 0 ?>';
    
    if (!selectedTarget && action !== 'defend') {
        alert('Select a target first!');
        return;
    }
    
    const buttons = document.querySelectorAll('#action-panel button');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch('/pvp/action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&attacker_id=${attackerId}&target_id=${selectedTarget || 0}&use_ability=${useAbility ? 1 : 0}&csrf_token=${csrfToken}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateBattleState(data.state);
            
            if (data.battle_ended) {
                showBattleResult(data);
            }
        } else {
            alert(data.error || 'Action failed');
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(() => {
        alert('Connection error');
        buttons.forEach(btn => btn.disabled = false);
    });
}

function updateBattleState(state) {
    document.getElementById('turn-counter').textContent = `Turn ${state.turn}`;
    
    const playerHtml = state.player_team.map(c => createChampionSlot(c, true)).join('');
    document.getElementById('player-team').innerHTML = playerHtml;
    
    const enemyHtml = state.enemy_team.map(c => createChampionSlot(c, false)).join('');
    document.getElementById('enemy-team').innerHTML = enemyHtml;
    
    const logHtml = state.battle_log.map(l => `
        <div class="p-2 bg-white/5 rounded-lg">
            <span class="text-white/50">[Turn ${l.turn}]</span>
            ${escapeHtml(l.message)}
        </div>
    `).join('');
    document.getElementById('battle-log').innerHTML = logHtml;
    
    const abilityBtn = document.getElementById('btn-ability');
    const playerChamp = state.player_team[0];
    if (playerChamp && playerChamp.ability_cooldown <= 0 && playerChamp.alive) {
        abilityBtn.disabled = false;
        abilityBtn.querySelector('.text-xs')?.remove();
    } else {
        abilityBtn.disabled = true;
        if (playerChamp && playerChamp.ability_cooldown > 0) {
            abilityBtn.innerHTML = `<div class="text-2xl mb-1">✨</div><div>Ability</div><div class="text-xs text-white/50">CD: ${playerChamp.ability_cooldown}</div>`;
        }
    }
    
    document.querySelectorAll('#action-panel button:not(#btn-ability)').forEach(btn => btn.disabled = false);
}

function createChampionSlot(c, isPlayer) {
    const hpPercent = Math.max(0, (c.current_health / c.max_health) * 100);
    const statusEffects = [];
    if (c.stunned) statusEffects.push('<div class="mt-2 text-xs text-yellow-400">💫 Stunned</div>');
    if (c.frozen) statusEffects.push('<div class="mt-2 text-xs text-cyan-400">❄️ Frozen</div>');
    
    return `
        <div class="champion-slot bg-white/5 rounded-2xl p-4 ${!isPlayer && c.alive ? 'cursor-pointer hover:bg-white/10 transition targetable' : ''} ${c.alive ? '' : 'opacity-50'}" 
             data-champion-id="${c.id}"
             ${!isPlayer && c.alive ? `onclick="selectTarget(${c.id})"` : ''}>
            <div class="flex items-center justify-between mb-2">
                <div class="font-semibold ${c.alive ? '' : 'text-white/30 line-through'}">
                    ${escapeHtml(c.name)}
                </div>
                <div class="text-sm ${c.alive ? (isPlayer ? 'text-emerald-400' : 'text-red-400') : 'text-gray-400'}">
                    ${c.alive ? (isPlayer ? 'Active' : 'Enemy') : 'Defeated'}
                </div>
            </div>
            <div class="mb-2">
                <div class="flex justify-between text-xs mb-1">
                    <span>HP</span>
                    <span>${Math.max(0, c.current_health)}/${c.max_health}</span>
                </div>
                <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full ${c.alive ? (isPlayer ? 'bg-gradient-to-r from-emerald-500 to-green-400' : 'bg-gradient-to-r from-red-500 to-orange-400') : 'bg-gray-500'}" 
                         style="width: ${hpPercent}%"></div>
                </div>
            </div>
            <div class="flex gap-4 text-xs text-white/60">
                <span>⚔️ ${c.attack}</span>
                <span>🛡️ ${c.defense}</span>
                <span>⚡ ${c.speed}</span>
                ${c.ability_cooldown > 0 ? `<span class="text-orange-400">CD: ${c.ability_cooldown}</span>` : ''}
            </div>
            ${statusEffects.join('')}
        </div>
    `;
}

function showBattleResult(data) {
    const modal = document.getElementById('battle-result-modal');
    const icon = document.getElementById('result-icon');
    const title = document.getElementById('result-title');
    const stats = document.getElementById('result-stats');
    
    if (data.result === 'victory') {
        icon.textContent = '🏆';
        title.textContent = 'Victory!';
        title.className = 'text-4xl font-bold mb-4 text-emerald-400';
    } else {
        icon.textContent = '💔';
        title.textContent = 'Defeat';
        title.className = 'text-4xl font-bold mb-4 text-red-400';
    }
    
    let statsHtml = `
        <div class="grid grid-cols-2 gap-4 text-center mb-6">
            <div>
                <div class="text-2xl font-bold text-indigo-400">${data.stats?.rating || 0}</div>
                <div class="text-white/50 text-sm">New Rating</div>
            </div>
            <div>
                <div class="text-2xl font-bold ${data.result === 'victory' ? 'text-emerald-400' : 'text-red-400'}">
                    ${data.result === 'victory' ? '+' : ''}${data.stats?.rating_change || 0}
                </div>
                <div class="text-white/50 text-sm">Points</div>
            </div>
        </div>
    `;
    
    if (data.rewards) {
        statsHtml += `
            <div class="border-t border-white/10 pt-4 mt-4">
                <h3 class="text-lg font-bold mb-4 text-amber-400">Rewards</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-3xl">💰</div>
                        <div class="text-2xl font-bold text-amber-400">${data.rewards.gold || 0}</div>
                        <div class="text-xs text-white/50">Gold</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl">💎</div>
                        <div class="text-2xl font-bold text-cyan-400">${data.rewards.gems || 0}</div>
                        <div class="text-xs text-white/50">Gems</div>
                    </div>
                    ${data.rewards.shard ? `
                    <div class="text-center animate-pulse">
                        <div class="text-3xl">✨</div>
                        <div class="text-lg font-bold text-purple-400">Shard!</div>
                    </div>
                    ` : '<div class="text-center opacity-30"><div class="text-3xl">✨</div><div class="text-xs text-white/50">No Shard</div></div>'}
                </div>
                ${data.rewards.item ? `
                <div class="mt-4 text-center">
                    <span class="px-4 py-2 bg-amber-500/20 text-amber-400 rounded-xl text-sm font-semibold">
                        🎁 Bonus: ${data.rewards.item}
                    </span>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    stats.innerHTML = statsHtml;
    modal.classList.remove('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateTimer() {
    const elapsed = Math.floor((Date.now() - battleStartTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    document.getElementById('battle-timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

setInterval(updateTimer, 1000);
updateTimer();

const firstEnemy = document.querySelector('.targetable');
if (firstEnemy) {
    selectTarget(firstEnemy.dataset.championId);
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
