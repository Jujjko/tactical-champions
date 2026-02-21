// Modern Battle Arena with Animations - Tactical Champions 2026

let currentBattleState = null;
let selectedAttacker = null;
let selectedTarget = null;
let battleInterval = null;
let battleEnded = false;
let autoBattleEnabled = false;
let autoBattleInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    initBattle();
});

async function initBattle() {
    await fetchBattleState();
    setupEventListeners();
}

async function fetchBattleState() {
    if (battleEnded) return;
    
    try {
        const res = await fetch('/battle/state');
        const data = await res.json();
        
        if (!res.ok || data.error) {
            console.error('Battle state error:', data.error || 'Unknown error');
            if (battleInterval) clearInterval(battleInterval);
            window.location.href = '/missions';
            return;
        }
        
        currentBattleState = data.battle_state || data;
        
        if (!currentBattleState || !currentBattleState.player_team) {
            console.error('Invalid battle state');
            if (battleInterval) clearInterval(battleInterval);
            window.location.href = '/missions';
            return;
        }
        
        renderTeams();
        updateTurnCounter();
        updateBattleLog(currentBattleState.battle_log || []);
    } catch (e) {
        console.error(e);
        if (battleInterval) clearInterval(battleInterval);
        window.location.href = '/missions';
    }
}

function setupEventListeners() {
    document.getElementById('forfeit-btn').addEventListener('click', forfeitBattle);
    document.getElementById('attack-btn').addEventListener('click', () => executePlayerAction(false));
    document.getElementById('ability-btn').addEventListener('click', () => executePlayerAction(true));
    
    const autoBattleBtn = document.getElementById('auto-battle-btn');
    if (autoBattleBtn) {
        autoBattleBtn.addEventListener('click', toggleAutoBattle);
    }
}

function renderTeams() {
    const playerEl = document.getElementById('player-champions');
    const enemyEl = document.getElementById('enemy-champions');

    playerEl.innerHTML = currentBattleState.player_team.map(champ => createChampionCard(champ, true)).join('');
    enemyEl.innerHTML = currentBattleState.enemy_team.map(champ => createChampionCard(champ, false)).join('');

    document.querySelectorAll('.battle-champion').forEach(card => {
        card.addEventListener('click', handleChampionClick);
    });
}

function createChampionCard(champ, isPlayer) {
    const healthPercent = Math.max(0, (champ.current_health / champ.max_health) * 100);
    const status = champ.stunned ? '💫' : (champ.frozen ? '❄️' : '');

    return `
        <div class="battle-champion glass rounded-3xl p-5 cursor-pointer transition-all hover:scale-105 ${!champ.alive ? 'opacity-40 pointer-events-none' : ''}" 
             data-id="${champ.id}" data-team="${isPlayer ? 'player' : 'enemy'}">
            <div class="relative h-28 bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl flex items-center justify-center text-7xl mb-4 overflow-hidden">
                🛡️
                ${status ? `<div class="absolute top-3 right-3 text-4xl">${status}</div>` : ''}
            </div>
            <div class="font-semibold text-center mb-3">${champ.name}</div>
            
            <div class="h-2.5 bg-black/50 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-400 to-cyan-400 transition-all duration-700" 
                     style="width: ${healthPercent}%"></div>
            </div>
            <div class="text-center text-xs mt-1 text-white/70">
                ${Math.floor(champ.current_health)} / ${champ.max_health}
            </div>
        </div>
    `;
}

function handleChampionClick(e) {
    const card = e.currentTarget;
    const id = card.dataset.id;
    const team = card.dataset.team;

    if (team === 'player') {
        document.querySelectorAll('.battle-champion').forEach(c => c.classList.remove('ring-4', 'ring-indigo-400'));
        card.classList.add('ring-4', 'ring-indigo-400');
        selectedAttacker = id;
        document.getElementById('action-text').textContent = 'Choose an enemy to attack';
    } else if (selectedAttacker) {
        selectedTarget = id;
        executePlayerAction(false);
    }
}

async function executePlayerAction(useAbility) {
    if (!selectedAttacker || !selectedTarget) {
        document.getElementById('action-text').textContent = 'Select your champion and then an enemy target!';
        return;
    }

    const csrfToken = document.getElementById('csrf-token')?.value || '';
    
    const formData = new URLSearchParams({
        action: 'attack',
        attacker_id: selectedAttacker,
        target_id: selectedTarget,
        use_ability: useAbility ? 'true' : 'false',
        csrf_token: csrfToken
    });

    try {
        const res = await fetch('/battle/action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        const result = await res.json();

        if (result.damage !== undefined) {
            showFloatingDamage(selectedTarget, result.damage, result.critical || false, result.effect);
        }

        currentBattleState = result;
        renderTeams();
        updateTurnCounter();
        updateBattleLog(result.battle_log || []);

        if (result.battle_ended) {
            battleEnded = true;
            if (battleInterval) clearInterval(battleInterval);
            setTimeout(() => showBattleResult(result), 800);
        }

        selectedAttacker = null;
        selectedTarget = null;
        document.getElementById('action-text').textContent = 'Select your champion';

    } catch (err) {
        console.error(err);
    }
}

function showFloatingDamage(targetId, amount, isCritical = false, effect = '') {
    const container = document.getElementById('damage-container');
    
    const damageEl = document.createElement('div');
    let className = 'damage-number absolute pointer-events-none';
    
    if (effect === 'heal') {
        className += ' heal';
    } else if (amount === 0) {
        className += ' miss';
    } else if (isCritical) {
        className += ' critical';
    }
    
    damageEl.className = className;
    damageEl.textContent = effect === 'heal' ? `+${amount}` : (amount === 0 ? 'MISS' : `-${amount}`);
    
    const champCard = document.querySelector(`[data-id="${targetId}"]`);
    
    if (champCard) {
        const rect = champCard.getBoundingClientRect();
        const offsetX = (Math.random() - 0.5) * 40;
        damageEl.style.left = `${rect.left + rect.width / 2 - 30 + offsetX}px`;
        damageEl.style.top = `${rect.top + 40}px`;
        
        if (!effect || effect !== 'heal') {
            champCard.classList.add('hit-flash');
            setTimeout(() => champCard.classList.remove('hit-flash'), 300);
            
            if (isCritical) {
                triggerScreenShake();
                showCriticalOverlay();
                spawnParticles(rect.left + rect.width / 2, rect.top + rect.height / 2, '#fbbf24', 15);
            } else if (amount > 0) {
                spawnParticles(rect.left + rect.width / 2, rect.top + rect.height / 2, '#ef4444', 8);
            }
        }
    } else {
        damageEl.style.left = '50%';
        damageEl.style.top = '40%';
    }

    container.appendChild(damageEl);
    setTimeout(() => damageEl.remove(), 1500);
}

function triggerScreenShake() {
    document.body.classList.add('screen-shake');
    setTimeout(() => document.body.classList.remove('screen-shake'), 500);
}

function showCriticalOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'critical-overlay';
    document.body.appendChild(overlay);
    setTimeout(() => overlay.remove(), 300);
}

function spawnParticles(x, y, color, count) {
    const container = document.getElementById('damage-container');
    
    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = `${x}px`;
        particle.style.top = `${y}px`;
        particle.style.backgroundColor = color;
        particle.style.boxShadow = `0 0 6px ${color}`;
        
        const angle = (Math.PI * 2 * i) / count + Math.random() * 0.5;
        const distance = 30 + Math.random() * 50;
        particle.style.setProperty('--tx', `${Math.cos(angle) * distance}px`);
        particle.style.setProperty('--ty', `${Math.sin(angle) * distance}px`);
        
        container.appendChild(particle);
        setTimeout(() => particle.remove(), 800);
    }
}

let comboCount = 0;
let comboTimeout = null;

function showCombo() {
    comboCount++;
    
    if (comboTimeout) clearTimeout(comboTimeout);
    
    let comboEl = document.querySelector('.combo-display');
    if (!comboEl) {
        comboEl = document.createElement('div');
        comboEl.className = 'combo-display';
        document.body.appendChild(comboEl);
    }
    
    comboEl.textContent = `${comboCount}x COMBO!`;
    comboEl.style.animation = 'none';
    comboEl.offsetHeight;
    comboEl.style.animation = 'comboPopIn 0.3s ease-out';
    
    comboTimeout = setTimeout(() => {
        comboCount = 0;
        comboEl.remove();
    }, 2000);
}

function updateTurnCounter() {
    document.getElementById('turn-counter').textContent = `Turn ${currentBattleState.turn || 1}`;
}

function updateBattleLog(log) {
    const logEl = document.getElementById('battle-log');
    logEl.innerHTML = log.slice(-10).map(entry => `
        <div class="p-3 rounded-2xl bg-white/5 text-sm">
            <span class="text-indigo-400">[Turn ${entry.turn}]</span> ${entry.message}
        </div>
    `).join('');
    logEl.scrollTop = logEl.scrollHeight;
}

function showBattleResult(result) {
    stopAutoBattle();
    battleEnded = true;
    if (battleInterval) clearInterval(battleInterval);
    
    const modal = document.getElementById('result-modal');
    const content = document.getElementById('result-content');
    const isVictory = result.winner === 'victory';
    const rewards = result.rewards || {};
    const mission = result.mission || {};

    let rewardsHtml = '';
    if (isVictory && rewards) {
        rewardsHtml = `
            <div class="glass rounded-2xl p-6 mt-6 mb-6">
                <h3 class="text-xl font-semibold mb-4 text-yellow-400">Rewards Earned</h3>
                <div class="grid grid-cols-2 gap-4">
                    ${rewards.gold ? `<div class="bg-yellow-500/20 rounded-xl p-4"><div class="text-3xl">💰</div><div class="text-2xl font-bold text-yellow-400">+${rewards.gold}</div><div class="text-sm text-white/60">Gold</div></div>` : ''}
                    ${rewards.experience ? `<div class="bg-indigo-500/20 rounded-xl p-4"><div class="text-3xl">✨</div><div class="text-2xl font-bold text-indigo-400">+${rewards.experience}</div><div class="text-sm text-white/60">Experience</div></div>` : ''}
                    ${rewards.lootbox ? `<div class="bg-purple-500/20 rounded-xl p-4"><div class="text-3xl">📦</div><div class="text-2xl font-bold text-purple-400">+1</div><div class="text-sm text-white/60">${rewards.lootbox} Lootbox</div></div>` : ''}
                </div>
                ${mission.name ? `<div class="mt-4 text-white/60 text-sm">Mission: ${mission.name}</div>` : ''}
            </div>
        `;
    }

    content.innerHTML = `
        <div class="text-center">
            <div class="text-8xl mb-6 animate-bounce">${isVictory ? '🏆' : '💀'}</div>
            <h2 class="text-6xl font-bold title-font mb-4 ${isVictory ? 'text-emerald-400' : 'text-red-500'}">
                ${isVictory ? 'VICTORY' : 'DEFEAT'}
            </h2>
            ${rewardsHtml}
            <button onclick="location.href='/missions'" 
                    class="mt-6 px-12 py-6 bg-white/10 hover:bg-white/20 rounded-3xl text-xl font-semibold transition">
                Return to Base
            </button>
        </div>
    `;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function forfeitBattle() {
    if (confirm('Forfeit the battle?')) {
        stopAutoBattle();
        const csrfToken = document.getElementById('csrf-token')?.value || '';
        const formData = new URLSearchParams();
        formData.append('csrf_token', csrfToken);
        
        await fetch('/battle/forfeit', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        location.href = '/missions';
    }
}

function toggleAutoBattle() {
    const btn = document.getElementById('auto-battle-btn');
    
    if (autoBattleEnabled) {
        stopAutoBattle();
        btn.textContent = '⚡ Auto Battle';
        btn.classList.remove('bg-yellow-500/80', 'hover:bg-yellow-600');
        btn.classList.add('bg-purple-500/80', 'hover:bg-purple-600');
    } else {
        autoBattleEnabled = true;
        btn.textContent = '⏹ Stop Auto';
        btn.classList.remove('bg-purple-500/80', 'hover:bg-purple-600');
        btn.classList.add('bg-yellow-500/80', 'hover:bg-yellow-600');
        runAutoBattle();
    }
}

function stopAutoBattle() {
    autoBattleEnabled = false;
    if (autoBattleInterval) {
        clearTimeout(autoBattleInterval);
        autoBattleInterval = null;
    }
}

async function runAutoBattle() {
    if (!autoBattleEnabled || battleEnded) {
        stopAutoBattle();
        return;
    }
    
    const playerTeam = currentBattleState?.player_team || [];
    const enemyTeam = currentBattleState?.enemy_team || [];
    
    const alivePlayers = playerTeam.filter(c => c.alive);
    const aliveEnemies = enemyTeam.filter(c => c.alive);
    
    if (alivePlayers.length === 0 || aliveEnemies.length === 0 || battleEnded) {
        stopAutoBattle();
        return;
    }
    
    const attacker = alivePlayers[0];
    const target = aliveEnemies[Math.floor(Math.random() * aliveEnemies.length)];
    
    if (attacker && target) {
        selectedAttacker = attacker.id;
        selectedTarget = target.id;
        await executePlayerAction(false);
    }
    
    if (autoBattleEnabled && !battleEnded) {
        autoBattleInterval = setTimeout(runAutoBattle, 800);
    }
}

battleInterval = setInterval(() => {
    if (currentBattleState && document.getElementById('result-modal').classList.contains('hidden') && !battleEnded) {
        fetchBattleState();
    }
}, 1800);