<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <a href="/champions" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Champions
        </a>

        <div class="grid grid-cols-12 gap-10">
            <!-- Left - Visual -->
            <div class="col-span-12 lg:col-span-5">
                <div class="glass rounded-3xl overflow-hidden neon-glow">
                    <div class="h-96 bg-gradient-to-br from-violet-900 via-purple-900 to-fuchsia-900 flex items-center justify-center relative overflow-hidden">
                        <?php if (!empty($champion['image_url'])): ?>
                            <img src="<?= htmlspecialchars($champion['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($champion['name']) ?>"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full items-center justify-center text-[12rem] hidden">
                                <?= $champion['icon'] ?? '🛡️' ?>
                            </div>
                        <?php else: ?>
                            <span class="text-[12rem]"><?= $champion['icon'] ?? '🛡️' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 text-center">
                        <!-- Stars Display -->
                        <div class="mb-4">
                            <?= \App\Services\FusionService::getStarsHtml((int)$champion['stars']) ?>
                            <div class="text-sm text-white/60 mt-1"><?= $champion['stars'] ?>/5 Stars</div>
                        </div>
                        
                        <div class="text-sm text-white/60 uppercase tracking-widest">Experience</div>
                        <div class="text-2xl font-bold mt-1"><?= $champion['experience'] ?> / <?= $champion['level'] * 50 ?> XP</div>
                        <div class="h-3 bg-white/10 rounded-full overflow-hidden mt-3">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500" 
                                 style="width: <?= min(($champion['experience'] / ($champion['level'] * 50)) * 100, 100) ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    <a href="/champions/<?= $champion['id'] ?>/upgrade" 
                       class="block w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-lg font-bold rounded-2xl text-center transition transform hover:scale-[1.02]">
                        Upgrade Champion
                    </a>
                    
                    <?php if ($fusionInfo['has_candidates'] && !$fusionInfo['is_max_stars']): ?>
                    <a href="/champions/<?= $champion['id'] ?>/fusion" 
                       class="block w-full py-4 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-lg font-bold rounded-2xl text-center transition transform hover:scale-[1.02]">
                        Fusion (<?= $fusionInfo['candidate_count'] ?> candidates)
                    </a>
                    <?php elseif ($champion['stars'] >= 5): ?>
                    <div class="block w-full py-4 bg-white/10 text-lg font-bold rounded-2xl text-center text-white/40">
                        Max Stars Reached
                    </div>
                    <?php else: ?>
                    <div class="block w-full py-4 bg-white/10 text-lg font-bold rounded-2xl text-center text-white/40">
                        No Fusion Candidates
                    </div>
                    <?php endif; ?>
                    
                    <a href="/equipment/<?= $champion['id'] ?>" 
                       class="block w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-lg font-bold rounded-2xl text-center transition transform hover:scale-[1.02]">
                        Manage Equipment
                    </a>
                </div>
            </div>

            <!-- Right - Info -->
            <div class="col-span-12 lg:col-span-7 space-y-6">
                <div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <h1 class="title-font text-5xl font-bold"><?= htmlspecialchars($champion['name']) ?></h1>
                        <span class="tier-<?= $champion['tier'] ?> text-lg px-4 py-2 rounded-full text-white">
                            <?= ucfirst($champion['tier']) ?>
                        </span>
                    </div>
                    <div class="text-4xl text-indigo-400 font-bold mt-2">Level <?= $champion['level'] ?></div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="glass rounded-2xl p-6">
                        <div class="text-emerald-400 text-sm uppercase tracking-widest">Health</div>
                        <div class="text-5xl font-bold mt-2"><?= $champion['health'] ?><?= $equipmentStats['health'] > 0 ? '<span class="text-lg text-emerald-400">+' . $equipmentStats['health'] . '</span>' : '' ?></div>
                    </div>
                    <div class="glass rounded-2xl p-6">
                        <div class="text-rose-400 text-sm uppercase tracking-widest">Attack</div>
                        <div class="text-5xl font-bold mt-2"><?= $champion['attack'] ?><?= $equipmentStats['attack'] > 0 ? '<span class="text-lg text-rose-400">+' . $equipmentStats['attack'] . '</span>' : '' ?></div>
                    </div>
                    <div class="glass rounded-2xl p-6">
                        <div class="text-amber-400 text-sm uppercase tracking-widest">Defense</div>
                        <div class="text-5xl font-bold mt-2"><?= $champion['defense'] ?><?= $equipmentStats['defense'] > 0 ? '<span class="text-lg text-amber-400">+' . $equipmentStats['defense'] . '</span>' : '' ?></div>
                    </div>
                    <div class="glass rounded-2xl p-6">
                        <div class="text-cyan-400 text-sm uppercase tracking-widest">Speed</div>
                        <div class="text-5xl font-bold mt-2"><?= $champion['speed'] ?><?= $equipmentStats['speed'] != 0 ? '<span class="text-lg ' . ($equipmentStats['speed'] > 0 ? 'text-cyan-400">+' : 'text-red-400">') . $equipmentStats['speed'] . '</span>' : '' ?></div>
                    </div>
                </div>

                <!-- Star Bonus -->
                <?php if ($champion['stars'] > 1): ?>
                <div class="glass rounded-2xl p-4 border border-yellow-500/30 bg-yellow-500/10">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⭐</span>
                        <div>
                            <div class="font-bold text-yellow-400">Star Bonus Active</div>
                            <div class="text-sm text-white/60">+<?= round((\App\Services\FusionService::getStarBonus((int)$champion['stars']) - 1) * 100) ?>% base stats from stars</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Power Rating -->
                <div class="glass rounded-2xl p-6 text-center">
                    <div class="text-sm text-white/60 uppercase tracking-widest mb-2">Power Rating</div>
                    <div class="text-6xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 text-transparent bg-clip-text">
                        <?= ($champion['health'] + $equipmentStats['health']) + (($champion['attack'] + $equipmentStats['attack']) * 10) + (($champion['defense'] + $equipmentStats['defense']) * 5) + (($champion['speed'] + $equipmentStats['speed']) * 2) ?>
                    </div>
                    <div class="text-xs text-white/40 mt-2">HP + (ATK × 10) + (DEF × 5) + (SPD × 2)</div>
                </div>

                <?php if (!empty($champion['special_ability'])): ?>
                <div class="glass rounded-2xl p-6">
                    <div class="text-purple-400 font-semibold flex items-center gap-3 mb-4">
                        <span class="text-3xl">✨</span> SPECIAL ABILITY
                    </div>
                    <p class="text-lg leading-relaxed"><?= htmlspecialchars($champion['special_ability']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Equipped Equipment -->
                <?php if (!empty($equipment)): ?>
                <div class="glass rounded-2xl p-6">
                    <div class="text-indigo-400 font-semibold flex items-center gap-3 mb-4">
                        <span class="text-3xl">⚔️</span> EQUIPPED GEAR
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($equipment as $item): ?>
                        <div class="bg-white/5 rounded-xl p-3 flex items-center gap-3">
                            <div class="text-2xl">
                                <?php
                                $typeIcons = ['weapon' => '⚔️', 'armor' => '🛡️', 'accessory' => '💍'];
                                echo $typeIcons[$item['type']] ?? '📦';
                                ?>
                            </div>
                            <div>
                                <div class="font-semibold text-sm"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="text-xs text-white/50">
                                    <?php
                                    $bonuses = [];
                                    if ($item['health_bonus'] > 0) $bonuses[] = '+' . $item['health_bonus'] . ' HP';
                                    if ($item['attack_bonus'] > 0) $bonuses[] = '+' . $item['attack_bonus'] . ' ATK';
                                    if ($item['defense_bonus'] > 0) $bonuses[] = '+' . $item['defense_bonus'] . ' DEF';
                                    if ($item['speed_bonus'] != 0) $bonuses[] = ($item['speed_bonus'] > 0 ? '+' : '') . $item['speed_bonus'] . ' SPD';
                                    echo implode(' | ', $bonuses);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Resources -->
                <div class="glass rounded-2xl p-6">
                    <div class="text-white/60 text-sm uppercase tracking-widest mb-4">Your Resources</div>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">💰</span>
                            <span class="text-xl font-bold text-yellow-400"><?= number_format($resources['gold']) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">💎</span>
                            <span class="text-xl font-bold text-cyan-400"><?= number_format($resources['gems']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';