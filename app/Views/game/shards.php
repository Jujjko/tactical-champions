<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h1 class="title-font text-6xl font-bold">💠 Your Shards</h1>
                <p class="text-white/70 text-2xl"><?= count($shards) ?> champions with shards</p>
            </div>
            <a href="/shop" class="px-8 py-5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-3xl font-semibold transition">
                Get More Shards
            </a>
        </div>

        <?php if (empty($shards)): ?>
        <div class="glass rounded-3xl p-16 text-center">
            <div class="text-8xl mb-6">💠</div>
            <h2 class="text-3xl font-bold mb-4">No Shards Collected</h2>
            <p class="text-white/60 text-lg mb-8">Open lootboxes or win PvP battles to earn champion shards!</p>
            <a href="/lootbox" class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl font-bold">
                Open Lootbox
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($shards as $shard): ?>
            <div class="glass rounded-2xl p-6 hover:bg-white/5 transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-violet-900 to-purple-900 flex items-center justify-center text-3xl">
                        🛡️
                    </div>
                    <div>
                        <h3 class="font-bold text-lg"><?= htmlspecialchars($shard['name']) ?></h3>
                        <span class="text-xs px-2 py-1 rounded-full tier-<?= $shard['tier'] ?>">
                            <?= ucfirst($shard['tier']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-white/60">Shards</span>
                        <span class="font-bold text-purple-400"><?= number_format($shard['amount']) ?></span>
                    </div>
                    <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500" 
                             style="width: <?= min(($shard['amount'] / 100) * 100, 100) ?>%"></div>
                    </div>
                </div>
                
                <?php if (!empty($shard['stars'])): ?>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-white/60">Current</span>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded text-xs <?= $shard['star_tier'] === 'gold' ? 'bg-yellow-500/20 text-yellow-400' : ($shard['star_tier'] === 'red' ? 'bg-red-500/20 text-red-400' : ($shard['star_tier'] === 'blue' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/10 text-white/60')) ?>">
                            <?= ucfirst($shard['star_tier'] ?? 'white') ?>
                        </span>
                        <span><?= $shard['stars'] ?>★</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Info Box -->
        <div class="mt-12 glass rounded-2xl p-8">
            <h3 class="text-xl font-bold mb-4 flex items-center gap-3">
                <span class="text-2xl">ℹ️</span> How Shards Work
            </h3>
            <div class="grid md:grid-cols-2 gap-6 text-white/70">
                <div>
                    <h4 class="font-semibold text-white mb-2">Collecting Shards</h4>
                    <ul class="space-y-1 text-sm">
                        <li>• Open lootboxes to earn random champion shards</li>
                        <li>• Win PvP battles for a chance to earn shards</li>
                        <li>• Purchase shard packs in the shop</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-2">Ascending Champions</h4>
                    <ul class="space-y-1 text-sm">
                        <li>• Collect enough shards to increase star level</li>
                        <li>• Each star boosts your champion's stats</li>
                        <li>• At 5★, you can tier up to the next tier</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
