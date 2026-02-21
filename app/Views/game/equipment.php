<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">⚔️ Equipment</h1>
                <p class="text-white/60 mt-2">Manage your weapons, armor, and accessories</p>
            </div>
            <div class="flex gap-3">
                <?php 
                $types = ['all' => 'All', 'weapon' => 'Weapons', 'armor' => 'Armor', 'accessory' => 'Accessories'];
                foreach ($types as $type => $label): 
                    $active = ($type === 'all' && !$typeFilter) || $typeFilter === $type;
                ?>
                <a href="/equipment<?= $type !== 'all' ? '?type=' . $type : '' ?>" 
                   class="px-5 py-2 rounded-xl font-semibold transition <?= $active ? 'bg-indigo-500 text-white' : 'glass text-white/70 hover:text-white' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($equipment)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-2xl font-bold mb-2">No Equipment Yet</h2>
            <p class="text-white/60">Complete missions to earn lootboxes that may contain equipment!</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($equipment as $item): ?>
            <?php
                $typeIcons = ['weapon' => '⚔️', 'armor' => '🛡️', 'accessory' => '💍'];
                $slotNames = [
                    'main_hand' => 'Main Hand', 'off_hand' => 'Off Hand',
                    'helmet' => 'Helmet', 'chest' => 'Chest',
                    'gloves' => 'Gloves', 'boots' => 'Boots',
                    'ring' => 'Ring', 'amulet' => 'Amulet'
                ];
            ?>
            <div class="glass rounded-2xl overflow-hidden neon-glow hover:scale-[1.02] transition">
                <div class="h-32 bg-gradient-to-br from-violet-900 via-purple-900 to-fuchsia-900 flex items-center justify-center text-5xl">
                    <?= $typeIcons[$item['type']] ?? '📦' ?>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-bold"><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="text-sm text-white/60"><?= $slotNames[$item['slot']] ?? $item['slot'] ?></div>
                        </div>
                        <span class="tier-<?= $item['tier'] ?> text-sm px-3 py-1 rounded-full text-white">
                            <?= ucfirst($item['tier']) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-sm mb-4">
                        <?php if ($item['health_bonus'] > 0): ?>
                        <div class="flex items-center gap-1 text-emerald-400">
                            <span>❤️</span> +<?= $item['health_bonus'] ?> HP
                        </div>
                        <?php endif; ?>
                        <?php if ($item['attack_bonus'] > 0): ?>
                        <div class="flex items-center gap-1 text-rose-400">
                            <span>⚔️</span> +<?= $item['attack_bonus'] ?> ATK
                        </div>
                        <?php endif; ?>
                        <?php if ($item['defense_bonus'] > 0): ?>
                        <div class="flex items-center gap-1 text-amber-400">
                            <span>🛡️</span> +<?= $item['defense_bonus'] ?> DEF
                        </div>
                        <?php endif; ?>
                        <?php if ($item['speed_bonus'] != 0): ?>
                        <div class="flex items-center gap-1 <?= $item['speed_bonus'] > 0 ? 'text-cyan-400' : 'text-red-400' ?>">
                            <span>💨</span> <?= $item['speed_bonus'] > 0 ? '+' : '' ?><?= $item['speed_bonus'] ?> SPD
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($item['is_equipped']): ?>
                    <div class="bg-emerald-500/20 text-emerald-400 text-center py-2 rounded-lg text-sm font-semibold mb-3">
                        ✓ Equipped to <?= htmlspecialchars($item['equipped_champion_name']) ?>
                    </div>
                    <?php endif; ?>

                    <a href="/equipment/<?= $item['id'] ?>" 
                       class="block w-full py-3 bg-white/10 hover:bg-white/20 rounded-xl text-center font-semibold transition">
                        Manage Equipment
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
