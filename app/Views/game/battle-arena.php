<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-8">
    <div class="max-w-7xl mx-auto px-6">

        <!-- Battle Header -->
        <div class="glass rounded-3xl p-6 mb-8 flex items-center justify-between neon-glow">
            <div class="flex items-center gap-4">
                <div class="text-4xl">⚔️</div>
                <div>
                    <div class="text-3xl font-bold title-font" id="turn-counter">Turn 1</div>
                    <div class="text-white/60 text-sm">Epic Battle Arena</div>
                </div>
            </div>
            <button id="forfeit-btn" 
                    class="px-8 py-4 bg-red-500/80 hover:bg-red-600 rounded-3xl font-semibold transition flex items-center gap-3">
                <i data-lucide="flag" class="w-5 h-5"></i> Forfeit
            </button>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <!-- Player Team -->
            <div class="col-span-12 lg:col-span-5">
                <div class="glass rounded-3xl p-8">
                    <h3 class="text-2xl font-semibold mb-6 flex items-center gap-3">
                        <span class="text-emerald-400">🛡️</span> Your Team
                    </h3>
                    <div id="player-champions" class="grid grid-cols-2 gap-4"></div>
                </div>
            </div>

            <!-- VS -->
            <div class="col-span-12 lg:col-span-2 flex items-center justify-center">
                <div class="text-7xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-white to-white/30">VS</div>
            </div>

            <!-- Enemy Team -->
            <div class="col-span-12 lg:col-span-5">
                <div class="glass rounded-3xl p-8">
                    <h3 class="text-2xl font-semibold mb-6 flex items-center gap-3 justify-end">
                        Enemy Team <span class="text-rose-400">👹</span>
                    </h3>
                    <div id="enemy-champions" class="grid grid-cols-2 gap-4"></div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="glass rounded-3xl p-8 mt-10 max-w-2xl mx-auto">
            <div class="flex justify-center gap-6">
                <button id="attack-btn" class="action-btn px-12 py-6 bg-indigo-600 hover:bg-indigo-700 text-xl font-semibold rounded-3xl transition flex items-center gap-4">
                    ⚔️ Basic Attack
                </button>
                <button id="ability-btn" class="action-btn px-12 py-6 bg-purple-600 hover:bg-purple-700 text-xl font-semibold rounded-3xl transition flex items-center gap-4">
                    ✨ Special Ability
                </button>
            </div>
            <p id="action-text" class="text-center mt-6 text-lg text-white/70 min-h-[1.5em]"></p>
        </div>

        <!-- Battle Log -->
        <div class="glass rounded-3xl p-8 mt-8 max-w-2xl mx-auto">
            <h3 class="text-xl font-semibold mb-6">Battle Log</h3>
            <div id="battle-log" class="h-80 overflow-y-auto space-y-3 pr-4"></div>
        </div>
    </div>
</div>

<!-- Floating Damage Container -->
<div id="damage-container" class="fixed inset-0 pointer-events-none z-[100]"></div>

<!-- Result Modal -->
<div id="result-modal" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-[200]">
    <div id="result-content" class="glass rounded-3xl p-12 max-w-lg w-full text-center"></div>
</div>

<style>
.action-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.action-btn:hover {
    transform: scale(1.05) translateY(-4px);
}

/* Damage Number Animations */
.damage-number {
    font-family: 'Space Grotesk', sans-serif;
    text-shadow: 0 0 20px currentColor, 0 4px 8px rgba(0,0,0,0.5);
    animation: damageFloat 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.damage-number.critical {
    animation: criticalDamage 1.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    font-size: 4rem;
}

.damage-number.heal {
    color: #10b981;
    text-shadow: 0 0 20px #10b981, 0 4px 8px rgba(0,0,0,0.5);
}

.damage-number.miss {
    color: #6b7280;
    font-size: 2rem;
}

@keyframes damageFloat {
    0% { transform: translateY(0) scale(0.5); opacity: 0; }
    10% { transform: translateY(-20px) scale(1.2); opacity: 1; }
    100% { transform: translateY(-120px) scale(0.8); opacity: 0; }
}

@keyframes criticalDamage {
    0% { transform: translateY(0) scale(0.3) rotate(-10deg); opacity: 0; }
    10% { transform: translateY(-30px) scale(1.5) rotate(5deg); opacity: 1; }
    30% { transform: translateY(-50px) scale(1.3) rotate(-3deg); }
    100% { transform: translateY(-150px) scale(0.9) rotate(0deg); opacity: 0; }
}

/* Screen Shake */
@keyframes screenShake {
    0%, 100% { transform: translateX(0); }
    10% { transform: translateX(-8px) rotate(-0.5deg); }
    20% { transform: translateX(8px) rotate(0.5deg); }
    30% { transform: translateX(-6px) rotate(-0.3deg); }
    40% { transform: translateX(6px) rotate(0.3deg); }
    50% { transform: translateX(-4px); }
    60% { transform: translateX(4px); }
    70% { transform: translateX(-2px); }
    80% { transform: translateX(2px); }
}

.screen-shake {
    animation: screenShake 0.5s ease-out;
}

/* Hit Flash */
@keyframes hitFlash {
    0%, 100% { filter: brightness(1); }
    50% { filter: brightness(2) saturate(0); }
}

.hit-flash {
    animation: hitFlash 0.3s ease-out;
}

/* Particle Effect Container */
.particle {
    position: absolute;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    pointer-events: none;
    animation: particleExplode 0.8s ease-out forwards;
}

@keyframes particleExplode {
    0% { transform: translate(0, 0) scale(1); opacity: 1; }
    100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
}

/* Combo Counter */
.combo-display {
    position: fixed;
    top: 20%;
    right: 10%;
    font-size: 3rem;
    font-weight: bold;
    color: #fbbf24;
    text-shadow: 0 0 30px #fbbf24;
    animation: comboPopIn 0.3s ease-out;
    pointer-events: none;
    z-index: 150;
}

@keyframes comboPopIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); opacity: 1; }
}

/* Health Bar Damage Animation */
.health-bar-damage {
    transition: width 0.3s ease-out !important;
}

/* Critical Hit Effect */
.critical-overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle, transparent 30%, rgba(255, 200, 0, 0.2) 100%);
    pointer-events: none;
    z-index: 90;
    animation: criticalFlash 0.3s ease-out forwards;
}

@keyframes criticalFlash {
    0% { opacity: 1; }
    100% { opacity: 0; }
}

/* Death Animation */
@keyframes championDeath {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); filter: brightness(2); }
    100% { transform: scale(0); opacity: 0; }
}

.champion-dying {
    animation: championDeath 0.5s ease-out forwards;
}
</style>

<script src="/js/battle.js"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>