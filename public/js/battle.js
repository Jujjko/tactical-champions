// Modern Battle Arena with Animations - Tactical Champions 2026

let currentBattleState = null;
let selectedAttacker = null;
let selectedTarget = null;

document.addEventListener('DOMContentLoaded', () => {
    initBattle();
});

async function initBattle() {
    await fetchBattleState();
    setupEventListeners();
}

async function fetchBattleState() {
    try {
        const res = await fetch('/battle/state');
        currentBattleState = await res.json();
        renderTeams();
        updateTurnCounter();
        updateBattleLog(currentBattleState.battle_log || []);
    } catch (e) {
        console.error(e);
    }
}

function setupEventListeners() {
    document.getElementById('forfeit-btn').addEventListener('click', forfeitBattle);
    document.getElementById('attack-btn').addEventListener('click', () => executePlayerAction(false));
    document.getElementById('ability-btn').addEventListener('click', () => executePlayerAction(true));
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

    const formData = new URLSearchParams({
        action: 'attack',
        attacker_id: selectedAttacker,
        target_id: selectedTarget,
        use_ability: useAbility ? 'true' : 'false'
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
    const modal = document.getElementById('result-modal');
    const content = document.getElementById('result-content');
    const isVictory = result.winner === 'victory';

    content.innerHTML = `
        <div class="text-center">
            <div class="text-8xl mb-6 animate-bounce">${isVictory ? '🏆' : '💀'}</div>
            <h2 class="text-6xl font-bold title-font mb-4 ${isVictory ? 'text-emerald-400' : 'text-red-500'}">
                ${isVictory ? 'VICTORY' : 'DEFEAT'}
            </h2>
            <button onclick="location.href='/missions'" 
                    class="mt-8 px-12 py-6 bg-white/10 hover:bg-white/20 rounded-3xl text-xl font-semibold transition">
                Return to Base
            </button>
        </div>
    `;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function forfeitBattle() {
    if (confirm('Forfeit the battle?')) {
        const formData = new URLSearchParams();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        await fetch('/battle/forfeit', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        location.href = '/missions';
    }
}

setInterval(() => {
    if (currentBattleState && document.getElementById('result-modal').classList.contains('hidden')) {
        fetchBattleState();
    }
}, 1800);