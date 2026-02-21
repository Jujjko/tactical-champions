<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">🎁 Referrals</h1>
                <p class="text-white/60 text-xl mt-2">Invite friends and earn rewards</p>
            </div>
            <div class="glass rounded-2xl p-4 text-center">
                <div class="text-3xl font-bold text-indigo-400"><?= $referralCount ?></div>
                <div class="text-xs text-white/60 uppercase tracking-widest">Referrals</div>
            </div>
        </div>

        <!-- Your Code -->
        <div class="glass rounded-3xl p-8 mb-8 text-center neon-glow">
            <h2 class="text-xl font-semibold mb-4">Your Referral Code</h2>
            <div class="text-5xl font-mono font-bold text-indigo-400 mb-4 tracking-widest"><?= htmlspecialchars($code) ?></div>
            <p class="text-white/60 mb-4">Share this code with friends! They get 500 gold and 10 gems when they use it.</p>
            <button onclick="navigator.clipboard.writeText('<?= $code ?>')" class="px-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl transition">
                📋 Copy Code
            </button>
        </div>

        <!-- Use Code -->
        <div class="glass rounded-3xl p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Have a Referral Code?</h2>
            <form method="POST" action="/referrals/use" class="flex gap-4">
                <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                <input type="text" name="code" class="form-input flex-1 bg-white/10 border-white/20 rounded-xl px-4 py-3 uppercase" placeholder="Enter code..." maxlength="20">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl font-semibold">
                    Apply
                </button>
            </form>
        </div>

        <!-- Your Referrals -->
        <?php if (!empty($referrals)): ?>
        <div class="glass rounded-3xl p-6">
            <h2 class="text-2xl font-bold mb-4">Your Referrals</h2>
            <div class="space-y-3">
                <?php foreach ($referrals as $ref): ?>
                <div class="bg-white/5 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <div class="font-semibold"><?= htmlspecialchars($ref['referee_name']) ?></div>
                        <div class="text-sm text-white/60">Level <?= $ref['referee_level'] ?> • Status: <span class="capitalize"><?= $ref['status'] ?></span></div>
                    </div>
                    <div class="text-right">
                        <?php if ($ref['referrer_reward_claimed']): ?>
                        <span class="text-emerald-400">✓ Claimed</span>
                        <?php elseif ($ref['status'] !== 'registered'): ?>
                        <form method="POST" action="/referrals/<?= $ref['id'] ?>/claim">
                            <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                            <button type="submit" class="px-4 py-2 bg-yellow-500/20 text-yellow-400 rounded-lg hover:bg-yellow-500/40">
                                Claim Reward
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-white/40">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rewards Info -->
        <div class="glass rounded-3xl p-6 mt-8">
            <h2 class="text-2xl font-bold mb-4">🏆 Referral Rewards</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">👤</div>
                    <div class="font-semibold">Registered</div>
                    <div class="text-sm text-white/60">100g + 5💎</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">📈</div>
                    <div class="font-semibold">Level 5</div>
                    <div class="text-sm text-white/60">250g + 10💎</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">⭐</div>
                    <div class="font-semibold">Level 10</div>
                    <div class="text-sm text-white/60">500g + 25💎</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">🏆</div>
                    <div class="font-semibold">Level 20</div>
                    <div class="text-sm text-white/60">1000g + 50💎</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
