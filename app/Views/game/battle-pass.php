<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <?php if (!$season): ?>
        <div class="glass rounded-3xl p-16 text-center">
            <div class="text-6xl mb-4">📅</div>
            <h2 class="text-3xl font-bold mb-3">No Active Season</h2>
            <p class="text-white/60">A new Battle Pass season will start soon!</p>
        </div>
        <?php else: ?>
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">📜 Battle Pass</h1>
                <p class="text-white/60 text-xl mt-2"><?= htmlspecialchars($season['name']) ?></p>
            </div>
            <div class="glass rounded-2xl p-4 text-center">
                <div class="text-3xl font-bold text-indigo-400">Level <?= $progress['level'] ?></div>
                <div class="text-xs text-white/60 uppercase tracking-widest">
                    <?= $progress['is_premium'] ? 'Premium' : 'Free' ?>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="glass rounded-3xl p-6 mb-8">
            <div class="flex justify-between mb-2">
                <span>Level <?= $progress['level'] ?></span>
                <span>Level <?= min($progress['level'] + 1, $season['level_count']) ?></span>
            </div>
            <div class="h-4 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-700"
                     style="width: <?= ($progress['experience'] / (100 + $progress['level'] * 50)) * 100 ?>%"></div>
            </div>
            <div class="text-center text-sm text-white/60 mt-2">
                <?= $progress['experience'] ?>/<?= 100 + $progress['level'] * 50 ?> XP to next level
            </div>
        </div>

        <!-- Upgrade to Premium -->
        <?php if (!$progress['is_premium']): ?>
        <div class="glass rounded-3xl p-6 mb-8 border border-yellow-500/30 bg-gradient-to-r from-yellow-500/10 to-orange-500/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-2">⭐ Upgrade to Premium</h3>
                    <p class="text-white/60">Unlock premium rewards and exclusive items!</p>
                </div>
                <button class="px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl font-bold text-lg">
                    💎 500 Gems
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rewards Track -->
        <div class="glass rounded-3xl p-6">
            <h2 class="text-2xl font-bold mb-6">🎁 Rewards</h2>
            <div class="overflow-x-auto">
                <div class="flex gap-4 pb-4" style="min-width: max-content;">
                    <?php foreach ($rewards as $reward): ?>
                    <div class="w-32 flex-shrink-0">
                        <div class="text-center mb-2">
                            <div class="text-sm text-white/60">Level <?= $reward['level'] ?></div>
                        </div>
                        
                        <!-- Free Reward -->
                        <div class="bg-white/5 rounded-xl p-3 mb-2 text-center <?= $progress['level'] >= $reward['level'] ? 'border border-emerald-500/30' : '' ?>">
                            <div class="text-2xl mb-1">📦</div>
                            <div class="text-xs">
                                <?php if ($reward['free_reward_type']): ?>
                                +<?= $reward['free_reward_value'] ?> <?= ucfirst($reward['free_reward_type']) ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </div>
                            <?php if ($progress['level'] >= $reward['level'] && $progress['last_claimed_free_level'] < $reward['level']): ?>
                            <form method="POST" action="/battle-pass/claim/<?= $reward['level'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                                <button type="submit" class="mt-2 text-xs bg-emerald-500 px-2 py-1 rounded">Claim</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Premium Reward -->
                        <div class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 rounded-xl p-3 text-center <?= !$progress['is_premium'] ? 'opacity-50' : '' ?>">
                            <div class="text-2xl mb-1">⭐</div>
                            <div class="text-xs">
                                <?php if ($reward['premium_reward_type']): ?>
                                +<?= $reward['premium_reward_value'] ?> <?= ucfirst($reward['premium_reward_type']) ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
