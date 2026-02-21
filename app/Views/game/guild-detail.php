<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <a href="/guilds" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Guilds
        </a>

        <div class="glass rounded-3xl p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-4xl">⚔️</div>
                    <div>
                        <h1 class="title-font text-4xl font-bold">[<?= htmlspecialchars($guild['tag']) ?>] <?= htmlspecialchars($guild['name']) ?></h1>
                        <div class="text-white/60">Level <?= $guild['level'] ?> • <?= $guild['member_count'] ?> members</div>
                    </div>
                </div>
                <?php if (!$isMember && $guild['is_recruiting']): ?>
                <form method="POST" action="/guilds/<?= $guild['id'] ?>/join">
                    <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl font-semibold">
                        Join Guild
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if ($guild['description']): ?>
            <p class="text-white/70 mb-6"><?= htmlspecialchars($guild['description']) ?></p>
            <?php endif; ?>

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-400"><?= number_format($guild['gold_treasury']) ?></div>
                    <div class="text-xs text-white/60 uppercase">Gold Treasury</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-cyan-400"><?= number_format($guild['gems_treasury']) ?></div>
                    <div class="text-xs text-white/60 uppercase">Gems Treasury</div>
                </div>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold">Level <?= $guild['min_level_req'] ?>+</div>
                    <div class="text-xs text-white/60 uppercase">Required</div>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <div class="glass rounded-3xl p-6">
            <h2 class="text-2xl font-bold mb-4">👥 Members</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($guild['members'] as $member): ?>
                <div class="bg-white/5 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-xl">
                        <?= $member['role'] === 'leader' ? '👑' : ($member['role'] === 'officer' ? '⭐' : '👤') ?>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold"><?= htmlspecialchars($member['username']) ?></div>
                        <div class="text-sm text-white/60">Level <?= $member['level'] ?> • <?= ucfirst($member['role']) ?></div>
                    </div>
                    <?php if ($member['contribution_gold'] > 0 || $member['contribution_gems'] > 0): ?>
                    <div class="text-xs text-white/40">
                        💰<?= $member['contribution_gold'] ?> 💎<?= $member['contribution_gems'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
