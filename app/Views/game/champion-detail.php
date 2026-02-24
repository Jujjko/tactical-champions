<?php 
ob_start();

$powerRating = ($champion['health'] + $equipmentStats['health']) + 
               (($champion['attack'] + $equipmentStats['attack']) * 10) + 
               (($champion['defense'] + $equipmentStats['defense']) * 5) + 
               (($champion['speed'] + $equipmentStats['speed']) * 2);

$rarityColors = [
    'common' => [
        'glow' => 'from-zinc-500/30 to-slate-600/30',
        'border' => 'border-zinc-500/40',
        'accent' => '#71717a',
        'gradient' => 'from-zinc-600 to-slate-700',
        'shimmer' => 'zinc'
    ],
    'rare' => [
        'glow' => 'from-blue-500/30 to-cyan-600/30',
        'border' => 'border-blue-500/40',
        'accent' => '#3b82f6',
        'gradient' => 'from-blue-600 to-cyan-700',
        'shimmer' => 'blue'
    ],
    'epic' => [
        'glow' => 'from-purple-500/30 to-pink-600/30',
        'border' => 'border-purple-500/40',
        'accent' => '#a855f7',
        'gradient' => 'from-purple-600 to-pink-700',
        'shimmer' => 'purple'
    ],
    'legendary' => [
        'glow' => 'from-amber-500/30 to-orange-600/30',
        'border' => 'border-amber-500/40',
        'accent' => '#f59e0b',
        'gradient' => 'from-amber-600 to-orange-700',
        'shimmer' => 'amber'
    ],
    'mythic' => [
        'glow' => 'from-red-500/30 to-rose-600/30',
        'border' => 'border-red-500/40',
        'accent' => '#ef4444',
        'gradient' => 'from-red-600 to-rose-700',
        'shimmer' => 'red'
    ],
];

$rarity = $champion['tier'] ?? 'common';
$colors = $rarityColors[$rarity] ?? $rarityColors['common'];
?>
<style>
.rarity-shimmer {
    position: relative;
    overflow: hidden;
}
.rarity-shimmer::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s infinite;
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.power-glow {
    animation: powerPulse 2s ease-in-out infinite;
}
@keyframes powerPulse {
    0%, 100% { filter: drop-shadow(0 0 20px <?= $colors['accent'] ?>40); }
    50% { filter: drop-shadow(0 0 40px <?= $colors['accent'] ?>60); }
}

.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 40px -10px <?= $colors['accent'] ?>30;
}

.number-animate {
    display: inline-block;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.progress-fire {
    background: linear-gradient(90deg, #f97316, #ef4444, #fbbf24);
    background-size: 200% 100%;
    animation: fireFlow 2s linear infinite;
}
@keyframes fireFlow {
    0% { background-position: 0% 0%; }
    100% { background-position: 200% 0%; }
}

.tab-btn {
    position: relative;
    transition: all 0.3s ease;
}
.tab-btn.active {
    color: white;
}
.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: <?= $colors['accent'] ?>;
    border-radius: 2px;
}

.equipment-slot {
    transition: all 0.3s ease;
}
.equipment-slot:hover {
    transform: scale(1.1);
    z-index: 10;
}
.equipment-slot.empty {
    background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.05));
    border: 2px dashed rgba(255,255,255,0.1);
}
.equipment-slot.empty:hover {
    border-color: <?= $colors['accent'] ?>50;
    background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.08));
}

.ascend-btn {
    position: relative;
    overflow: hidden;
}
.ascend-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.2) 50%, transparent 70%);
    animation: btnShine 2s infinite;
}
@keyframes btnShine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.tier-badge-v2 {
    background: linear-gradient(135deg, <?= $colors['accent'] ?>20, <?= $colors['accent'] ?>10);
    border: 1px solid <?= $colors['accent'] ?>40;
}

.tooltip-v2 {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-8px);
    padding: 8px 12px;
    background: rgba(0,0,0,0.9);
    border: 1px solid <?= $colors['accent'] ?>30;
    border-radius: 8px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.2s ease;
    z-index: 50;
}
.stat-card:hover .tooltip-v2 {
    opacity: 1;
    transform: translateX(-50%) translateY(-12px);
}
</style>

<div class="min-h-screen bg-gradient-to-br from-[#0b0f1a] via-[#0d1220] to-[#121a2b] py-8 pt-24 relative">
    <!-- Ambient Rarity Glow -->
    <div class="fixed inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-gradient-to-b <?= $colors['glow'] ?> blur-[120px] opacity-40"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <!-- Top Bar: Back + Resources -->
        <div class="flex items-center justify-between mb-6">
            <a href="/champions" class="inline-flex items-center gap-2 text-white/60 hover:text-white transition group">
                <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition"></i> 
                <span>Back to Champions</span>
            </a>
            
            <!-- Resources (Desktop) -->
            <div class="hidden md:flex items-center gap-6 glass px-6 py-3 rounded-2xl">
                <div class="flex items-center gap-2 group cursor-pointer">
                    <span class="text-2xl">💰</span>
                    <span class="text-xl font-bold text-yellow-400 number-animate"><?= number_format($resources['gold']) ?></span>
                </div>
                <div class="w-px h-6 bg-white/10"></div>
                <div class="flex items-center gap-2 group cursor-pointer">
                    <span class="text-2xl">💎</span>
                    <span class="text-xl font-bold text-cyan-400 number-animate"><?= number_format($resources['gems']) ?></span>
                </div>
            </div>
        </div>

        <!-- HERO HEADER SECTION -->
        <div class="glass rounded-3xl overflow-hidden rarity-shimmer border <?= $colors['border'] ?> mb-6">
            <div class="relative">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M30 0L60 30L30 60L0 30Z\' fill=\'none\' stroke=\'<?= $colors['accent'] ?>\' stroke-width=\'0.5\'/%3E%3C/svg%3E'); background-size: 60px 60px;"></div>
                </div>
                
                <div class="relative p-8">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Champion Portrait -->
                        <div class="relative">
                            <!-- Portrait Frame -->
                            <div class="relative w-48 h-48 lg:w-56 lg:h-56">
                                <!-- Outer Ring -->
                                <div class="absolute inset-0 rounded-full border-2 <?= $colors['border'] ?> animate-spin" style="animation-duration: 20s;"></div>
                                <div class="absolute inset-2 rounded-full border <?= $colors['border'] ?>"></div>
                                
                                <!-- Portrait Container -->
                                <div class="absolute inset-4 rounded-full overflow-hidden bg-gradient-to-br <?= $colors['gradient'] ?>">
                                    <?php if (!empty($champion['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($champion['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($champion['name']) ?>"
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full items-center justify-center text-6xl hidden">
                                            <?= $champion['icon'] ?? '🛡️' ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-6xl flex items-center justify-center h-full"><?= $champion['icon'] ?? '🛡️' ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Level Badge -->
                                <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r <?= $colors['gradient'] ?> text-white font-bold text-sm border border-white/20">
                                    Lv.<?= $champion['level'] ?>
                                </div>
                            </div>
                            
                            <!-- Tier Badge -->
                            <div class="absolute -top-2 -right-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider tier-badge-v2" style="color: <?= $colors['accent'] ?>;">
                                <?= $rarity ?>
                            </div>
                        </div>

                        <!-- Champion Info -->
                        <div class="flex-1 text-center lg:text-left">
                            <!-- Name + Rarity Icon -->
                            <div class="flex items-center justify-center lg:justify-start gap-3 mb-2">
                                <h1 class="title-font text-4xl lg:text-5xl font-bold text-white"><?= htmlspecialchars($champion['name']) ?></h1>
                                <?php if ($rarity === 'mythic'): ?>
                                    <span class="text-3xl" title="Mythic">🔥</span>
                                <?php elseif ($rarity === 'legendary'): ?>
                                    <span class="text-3xl" title="Legendary">⭐</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stars -->
                            <div class="flex items-center justify-center lg:justify-start gap-2 mb-4">
                                <?= \App\Services\AscensionService::getStarsHtml((int)$champion['stars']) ?>
                                <span class="text-white/60 text-sm ml-2">
                                    <?= ucfirst($ascensionInfo['star_tier'] ?? 'white') ?> Tier
                                </span>
                            </div>
                            
                            <!-- Power Rating -->
                            <div class="inline-flex flex-col items-center lg:items-start">
                                <div class="text-white/40 text-xs uppercase tracking-widest mb-1">Power Rating</div>
                                <div class="text-5xl lg:text-6xl font-black power-glow" style="color: <?= $colors['accent'] ?>;">
                                    <?= number_format($powerRating) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats (Desktop) -->
                        <div class="hidden xl:grid grid-cols-2 gap-3">
                            <div class="text-center px-4 py-2 rounded-xl bg-white/5">
                                <div class="text-emerald-400 text-xs uppercase">Health</div>
                                <div class="text-2xl font-bold"><?= $champion['health'] ?></div>
                            </div>
                            <div class="text-center px-4 py-2 rounded-xl bg-white/5">
                                <div class="text-rose-400 text-xs uppercase">Attack</div>
                                <div class="text-2xl font-bold"><?= $champion['attack'] ?></div>
                            </div>
                            <div class="text-center px-4 py-2 rounded-xl bg-white/5">
                                <div class="text-amber-400 text-xs uppercase">Defense</div>
                                <div class="text-2xl font-bold"><?= $champion['defense'] ?></div>
                            </div>
                            <div class="text-center px-4 py-2 rounded-xl bg-white/5">
                                <div class="text-cyan-400 text-xs uppercase">Speed</div>
                                <div class="text-2xl font-bold"><?= $champion['speed'] ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- XP Bar -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-white/40">Experience</span>
                            <span class="text-white/60"><?= $champion['experience'] ?> / <?= $champion['level'] * 50 ?> XP</span>
                        </div>
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-1000 ease-out" 
                                 style="width: <?= min(($champion['experience'] / ($champion['level'] * 50)) * 100, 100) ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGATION -->
        <div class="glass rounded-2xl p-2 mb-6 flex gap-2 overflow-x-auto">
            <button onclick="switchTab('stats')" class="tab-btn active flex-1 px-6 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/5 font-medium transition whitespace-nowrap" data-tab="stats">
                <span class="flex items-center justify-center gap-2">
                    <span>📊</span> Stats
                </span>
            </button>
            <button onclick="switchTab('equipment')" class="tab-btn flex-1 px-6 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/5 font-medium transition whitespace-nowrap" data-tab="equipment">
                <span class="flex items-center justify-center gap-2">
                    <span>⚔️</span> Equipment
                </span>
            </button>
            <button onclick="switchTab('ascend')" class="tab-btn flex-1 px-6 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/5 font-medium transition whitespace-nowrap" data-tab="ascend">
                <span class="flex items-center justify-center gap-2">
                    <span>💠</span> Ascend
                    <?php if ($ascensionInfo['can_ascend'] || $ascensionInfo['can_tier_up']): ?>
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <?php endif; ?>
                </span>
            </button>
            <?php if (!empty($champion['special_ability'])): ?>
            <button onclick="switchTab('skills')" class="tab-btn flex-1 px-6 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/5 font-medium transition whitespace-nowrap" data-tab="skills">
                <span class="flex items-center justify-center gap-2">
                    <span>✨</span> Skills
                </span>
            </button>
            <?php endif; ?>
        </div>

        <!-- TAB CONTENTS -->
        <div id="tab-stats" class="tab-content">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php 
                $stats = [
                    ['name' => 'Health', 'value' => $champion['health'], 'bonus' => $equipmentStats['health'], 'icon' => '❤️', 'color' => 'emerald', 'power_mult' => 1],
                    ['name' => 'Attack', 'value' => $champion['attack'], 'bonus' => $equipmentStats['attack'], 'icon' => '⚔️', 'color' => 'rose', 'power_mult' => 10],
                    ['name' => 'Defense', 'value' => $champion['defense'], 'bonus' => $equipmentStats['defense'], 'icon' => '🛡️', 'color' => 'amber', 'power_mult' => 5],
                    ['name' => 'Speed', 'value' => $champion['speed'], 'bonus' => $equipmentStats['speed'], 'icon' => '⚡', 'color' => 'cyan', 'power_mult' => 2],
                ];
                foreach ($stats as $stat): 
                    $totalStat = $stat['value'] + $stat['bonus'];
                    $powerContribution = $totalStat * $stat['power_mult'];
                ?>
                <div class="stat-card glass rounded-2xl p-6 relative cursor-pointer border border-transparent hover:border-<?= $stat['color'] ?>-500/30">
                    <div class="tooltip-v2">
                        Power Contribution: <?= number_format($powerContribution) ?>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl"><?= $stat['icon'] ?></span>
                        <span class="text-<?= $stat['color'] ?>-400 text-sm uppercase tracking-widest"><?= $stat['name'] ?></span>
                    </div>
                    <div class="text-4xl font-bold mb-1">
                        <span class="number-animate"><?= $stat['value'] ?></span>
                        <?php if ($stat['bonus'] > 0): ?>
                            <span class="text-lg text-<?= $stat['color'] ?>-400">+<?= $stat['bonus'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-white/40 text-xs">Total: <?= $totalStat ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Star Bonus -->
            <?php if ($champion['stars'] > 1): ?>
            <div class="glass rounded-2xl p-6 border border-yellow-500/30 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-yellow-500/20 flex items-center justify-center text-3xl">
                        ⭐
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-yellow-400 text-lg">Star Bonus Active</div>
                        <div class="text-white/60">+<?= round((\App\Services\AscensionService::getStarBonus((int)$champion['stars']) - 1) * 100) ?>% base stats from stars</div>
                    </div>
                    <div class="text-4xl font-bold text-yellow-400">
                        +<?= round((\App\Services\AscensionService::getStarBonus((int)$champion['stars']) - 1) * 100) ?>%
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Power Calculation -->
            <div class="glass rounded-2xl p-6">
                <div class="text-white/60 text-sm uppercase tracking-widest mb-4">Power Calculation</div>
                <div class="space-y-3 text-sm">
                    <?php foreach ($stats as $stat): 
                        $totalStat = $stat['value'] + $stat['bonus'];
                        $powerContribution = $totalStat * $stat['power_mult'];
                    ?>
                    <div class="flex items-center justify-between">
                        <span class="text-white/60"><?= $stat['name'] ?> × <?= $stat['power_mult'] ?></span>
                        <span class="font-mono"><?= $totalStat ?> × <?= $stat['power_mult'] ?> = <span class="text-white font-bold"><?= number_format($powerContribution) ?></span></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="h-px bg-white/10 my-2"></div>
                    <div class="flex items-center justify-between text-lg">
                        <span class="text-white/80">Total Power</span>
                        <span class="font-bold" style="color: <?= $colors['accent'] ?>;"><?= number_format($powerRating) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- EQUIPMENT TAB -->
        <div id="tab-equipment" class="tab-content hidden">
            <!-- Circular Equipment Layout -->
            <div class="glass rounded-3xl p-8">
                <div class="text-white/60 text-sm uppercase tracking-widest mb-6 flex items-center justify-between">
                    <span>Equipment Slots</span>
                    <a href="/equipment/<?= $champion['id'] ?>" class="text-sm px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 transition">
                        Manage Equipment →
                    </a>
                </div>
                
                <div class="relative w-full max-w-lg mx-auto aspect-square">
                    <!-- Center: Champion Icon -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 rounded-full bg-gradient-to-br <?= $colors['gradient'] ?> flex items-center justify-center text-5xl border-2 <?= $colors['border'] ?>">
                        <?= $champion['icon'] ?? '🛡️' ?>
                    </div>
                    
                    <?php 
                    $slots = [
                        ['name' => 'Weapon', 'icon' => '⚔️', 'angle' => 0],
                        ['name' => 'Helmet', 'icon' => '🪖', 'angle' => 60],
                        ['name' => 'Armor', 'icon' => '🛡️', 'angle' => 120],
                        ['name' => 'Boots', 'icon' => '👢', 'angle' => 180],
                        ['name' => 'Ring', 'icon' => '💍', 'angle' => 240],
                        ['name' => 'Amulet', 'icon' => '📿', 'angle' => 300],
                    ];
                    $equipmentByType = [];
                    foreach ($equipment as $item) {
                        $equipmentByType[$item['type']] = $item;
                    }
                    ?>
                    
                    <!-- Equipment Slots in Circle -->
                    <?php foreach ($slots as $index => $slot): 
                        $angle = $slot['angle'] - 90;
                        $radius = 42;
                        $x = 50 + $radius * cos(deg2rad($angle));
                        $y = 50 + $radius * sin(deg2rad($angle));
                        $hasItem = isset($equipmentByType[strtolower($slot['name'])]);
                        $item = $equipmentByType[strtolower($slot['name'])] ?? null;
                    ?>
                    <div class="equipment-slot <?= $hasItem ? '' : 'empty' ?> absolute w-16 h-16 rounded-xl flex flex-col items-center justify-center cursor-pointer group"
                         style="left: <?= $x ?>%; top: <?= $y ?>%; transform: translate(-50%, -50%);">
                        <?php if ($hasItem): ?>
                            <span class="text-2xl group-hover:scale-125 transition"><?= $slot['icon'] ?></span>
                            <span class="text-[10px] text-white/60 mt-1 opacity-0 group-hover:opacity-100 transition"><?= $item['name'] ?></span>
                        <?php else: ?>
                            <span class="text-2xl opacity-30"><?= $slot['icon'] ?></span>
                            <span class="text-[10px] text-white/40 mt-1">Empty</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Equipped Items List -->
                <?php if (!empty($equipment)): ?>
                <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($equipment as $item): ?>
                    <div class="glass rounded-xl p-4 flex items-center gap-3 hover:bg-white/5 transition cursor-pointer">
                        <div class="text-2xl">
                            <?php
                            $typeIcons = ['weapon' => '⚔️', 'armor' => '🛡️', 'helmet' => '🪖', 'boots' => '👢', 'ring' => '💍', 'amulet' => '📿', 'accessory' => '💍'];
                            echo $typeIcons[$item['type']] ?? '📦';
                            ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold truncate"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-xs text-white/50 flex flex-wrap gap-1">
                                <?php
                                $bonuses = [];
                                if ($item['health_bonus'] > 0) $bonuses[] = '<span class="text-emerald-400">+' . $item['health_bonus'] . ' HP</span>';
                                if ($item['attack_bonus'] > 0) $bonuses[] = '<span class="text-rose-400">+' . $item['attack_bonus'] . ' ATK</span>';
                                if ($item['defense_bonus'] > 0) $bonuses[] = '<span class="text-amber-400">+' . $item['defense_bonus'] . ' DEF</span>';
                                if ($item['speed_bonus'] != 0) $bonuses[] = '<span class="text-cyan-400">' . ($item['speed_bonus'] > 0 ? '+' : '') . $item['speed_bonus'] . ' SPD</span>';
                                echo implode(' ', $bonuses);
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ASCEND TAB -->
        <div id="tab-ascend" class="tab-content hidden">
            <!-- Shard Progress -->
            <div class="glass rounded-3xl p-8 mb-6 border <?= $colors['border'] ?>">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">💠</span>
                        <div>
                            <div class="font-bold text-xl">Shard Progress</div>
                            <div class="text-white/60 text-sm"><?= ucfirst($ascensionInfo['star_tier'] ?? 'white') ?> Tier • Level <?= $ascensionInfo['total_level'] ?>/20</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold" style="color: <?= $colors['accent'] ?>;">
                            <?= $ascensionInfo['current_shards'] ?>/<?= $ascensionInfo['required_shards'] ?>
                        </div>
                        <div class="text-white/40 text-sm">Shards</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="relative h-8 bg-white/5 rounded-full overflow-hidden mb-6">
                    <?php 
                    $progress = 0;
                    if (!empty($ascensionInfo['required_shards']) && $ascensionInfo['required_shards'] > 0) {
                        $progress = min(($ascensionInfo['current_shards'] / $ascensionInfo['required_shards']) * 100, 100);
                    }
                    ?>
                    <div class="h-full progress-fire rounded-full transition-all duration-1000 ease-out" 
                         style="width: <?= $progress ?>%"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-sm font-bold text-white drop-shadow-lg"><?= round($progress) ?>%</span>
                    </div>
                </div>

                <!-- Status / CTA -->
                <?php if ($ascensionInfo['is_maxed']): ?>
                    <div class="text-center py-6 rounded-2xl bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border border-yellow-500/30">
                        <div class="text-3xl mb-2">🏆</div>
                        <div class="text-2xl font-bold text-yellow-400">MAX LEVEL REACHED</div>
                        <div class="text-white/60 mt-1">Gold 5★ - Maximum Power!</div>
                    </div>
                <?php elseif ($ascensionInfo['can_tier_up']): ?>
                    <button onclick="tierUpChampion(<?= $champion['id'] ?>)" 
                            class="ascend-btn w-full py-5 rounded-2xl text-xl font-bold text-white transition transform hover:scale-[1.02]"
                            style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                        <span class="relative z-10 flex items-center justify-center gap-3">
                            <span>🔥</span>
                            <span>TIER UP TO <?= strtoupper($ascensionInfo['next_tier']) ?></span>
                            <span>🔥</span>
                        </span>
                    </button>
                <?php elseif ($ascensionInfo['can_ascend']): ?>
                    <button onclick="ascendChampion(<?= $champion['id'] ?>)" 
                            class="ascend-btn w-full py-5 rounded-2xl text-xl font-bold text-white transition transform hover:scale-[1.02]"
                            style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                        <span class="relative z-10 flex items-center justify-center gap-3">
                            <span>⭐</span>
                            <span>ASCEND TO <?= $champion['stars'] + 1 ?>★</span>
                            <span>⭐</span>
                        </span>
                    </button>
                <?php else: ?>
                    <div class="text-center py-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-lg text-white/80 mb-2">
                            <span class="text-amber-400 font-bold"><?= $ascensionInfo['required_shards'] - $ascensionInfo['current_shards'] ?></span> more shards to ascend
                        </div>
                        <a href="/shop" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/30 text-amber-400 hover:bg-amber-500/30 transition">
                            <span>💎</span>
                            <span>Find Shards</span>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Tier Info -->
                <div class="mt-6 grid grid-cols-4 gap-3 text-center text-sm">
                    <?php 
                    $tiers = ['white', 'blue', 'red', 'gold'];
                    $tierColors = ['white' => 'zinc', 'blue' => 'blue', 'red' => 'red', 'gold' => 'amber'];
                    $currentTierIndex = array_search($ascensionInfo['star_tier'] ?? 'white', $tiers);
                    foreach ($tiers as $i => $tier): 
                        $isActive = $i <= $currentTierIndex;
                        $isCurrent = $i === $currentTierIndex;
                    ?>
                    <div class="py-2 px-3 rounded-xl transition <?= $isCurrent ? 'bg-'.$tierColors[$tier].'-500/20 border border-'.$tierColors[$tier].'-500/40' : ($isActive ? 'bg-white/5' : 'bg-white/5 opacity-40') ?>">
                        <div class="font-bold <?= $isCurrent ? 'text-'.$tierColors[$tier].'-400' : 'text-white/60' ?>"><?= ucfirst($tier) ?></div>
                        <div class="text-xs text-white/40"><?= \App\Services\AscensionService::TIER_MULTIPLIERS[$tier] ?>x</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Convert Duplicates -->
            <?php if ($duplicateCount > 1): ?>
            <div class="glass rounded-2xl p-6 border border-purple-500/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="text-3xl">🔄</div>
                        <div>
                            <div class="font-bold text-purple-400">Convert Duplicates</div>
                            <div class="text-white/60 text-sm">You have <?= $duplicateCount - 1 ?> duplicate(s) worth <?= match($rarity) { 'mythic' => '80-120', 'legendary' => '50-80', 'epic' => '35-55', 'rare' => '25-40', default => '15-30' } ?> shards each</div>
                        </div>
                    </div>
                    <button onclick="convertDuplicate(<?= $champion['id'] ?>)" 
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30 text-purple-400 hover:bg-purple-500/30 transition font-medium">
                        Convert →
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SKILLS TAB -->
        <?php if (!empty($champion['special_ability'])): ?>
        <div id="tab-skills" class="tab-content hidden">
            <div class="glass rounded-3xl p-8">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br <?= $colors['gradient'] ?> flex items-center justify-center text-4xl flex-shrink-0">
                        ✨
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="font-bold text-xl text-white">Special Ability</div>
                            <span class="px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                Passive
                            </span>
                        </div>
                        <p class="text-lg text-white/80 leading-relaxed"><?= htmlspecialchars($champion['special_ability']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mobile Resources -->
        <div class="md:hidden mt-6 glass rounded-2xl p-4 flex justify-around">
            <div class="flex items-center gap-2">
                <span class="text-xl">💰</span>
                <span class="text-lg font-bold text-yellow-400"><?= number_format($resources['gold']) ?></span>
            </div>
            <div class="w-px h-6 bg-white/10"></div>
            <div class="flex items-center gap-2">
                <span class="text-xl">💎</span>
                <span class="text-lg font-bold text-cyan-400"><?= number_format($resources['gems']) ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
}

function ascendChampion(userChampionId) {
    if (!confirm('Ascend this champion to the next star level?')) return;
    
    fetch(`/champions/${userChampionId}/ascend`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Champion ascended successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error || 'Failed to ascend champion', 'error');
        }
    })
    .catch(() => showNotification('An error occurred', 'error'));
}

function tierUpChampion(userChampionId) {
    if (!confirm('Tier up this champion? This will reset stars to 1 but increase the tier multiplier!')) return;
    
    fetch(`/champions/${userChampionId}/tier-up`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Champion tier increased!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error || 'Failed to tier up champion', 'error');
        }
    })
    .catch(() => showNotification('An error occurred', 'error'));
}

function convertDuplicate(userChampionId) {
    if (!confirm('Convert this duplicate champion to shards?')) return;
    
    fetch(`/champions/${userChampionId}/convert-duplicate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Duplicate converted to shards!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.error || 'Failed to convert duplicate', 'error');
        }
    })
    .catch(() => showNotification('An error occurred', 'error'));
}

function showNotification(message, type) {
    const colors = {
        success: 'from-emerald-500/20 to-teal-500/20 border-emerald-500/40 text-emerald-400',
        error: 'from-red-500/20 to-rose-500/20 border-red-500/40 text-red-400'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-24 left-1/2 -translate-x-1/2 px-6 py-4 rounded-2xl bg-gradient-to-r ${colors[type]} border backdrop-blur-xl z-50 animate-bounce`;
    notification.innerHTML = `<span class="font-medium">${message}</span>`;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 3000);
}

// Auto-switch to Ascend tab if ready
<?php if ($ascensionInfo['can_ascend'] || $ascensionInfo['can_tier_up']): ?>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => switchTab('ascend'), 500);
});
<?php endif; ?>
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
