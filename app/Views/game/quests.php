<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">📜 Quests</h1>
                <p class="text-white/60 text-xl mt-2">Complete tasks and earn rewards</p>
            </div>
            <?php if ($unclaimedCount > 0): ?>
            <button onclick="claimAll()" class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl font-semibold hover:from-yellow-600 hover:to-orange-600 transition animate-pulse">
                Claim All (<?= $unclaimedCount ?>)
            </button>
            <?php endif; ?>
        </div>

        <!-- Daily Quests -->
        <div class="glass rounded-3xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-3">
                    <span class="text-3xl">📅</span> Daily Quests
                </h2>
                <span class="text-sm text-white/60">Resets at midnight</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($dailyQuests as $quest): ?>
                <?php
                    $progress = $quest['progress'] ?? 0;
                    $completed = $quest['completed'] ?? false;
                    $claimed = $quest['claimed'] ?? false;
                    $progressPercent = min(($progress / $quest['requirement_value']) * 100, 100);
                ?>
                <div class="bg-white/5 rounded-2xl p-4 <?= $completed && !$claimed ? 'border border-yellow-500/30 bg-yellow-500/5' : '' ?> <?= $claimed ? 'opacity-60' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-xl">
                                📜
                            </div>
                            <div>
                                <h3 class="font-semibold <?= $claimed ? 'line-through text-white/40' : '' ?>"><?= htmlspecialchars($quest['name']) ?></h3>
                                <p class="text-sm text-white/60"><?= htmlspecialchars($quest['description']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-white/60">Progress</span>
                            <span class="<?= $completed ? 'text-emerald-400' : 'text-white/60' ?>">
                                <?= $progress ?>/<?= $quest['requirement_value'] ?>
                            </span>
                        </div>
                        <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 <?= $completed ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' ?>" 
                                 style="width: <?= $progressPercent ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Rewards -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3 text-sm">
                            <?php if ($quest['reward_gold'] > 0): ?>
                            <span class="text-yellow-400">💰 <?= $quest['reward_gold'] ?></span>
                            <?php endif; ?>
                            <?php if ($quest['reward_gems'] > 0): ?>
                            <span class="text-cyan-400">💎 <?= $quest['reward_gems'] ?></span>
                            <?php endif; ?>
                            <?php if ($quest['reward_battle_pass_xp'] > 0): ?>
                            <span class="text-purple-400">📜 <?= $quest['reward_battle_pass_xp'] ?> BP XP</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($completed && !$claimed): ?>
                        <button onclick="claimQuest(<?= $quest['user_quest_id'] ?>)" 
                                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg text-sm font-semibold hover:from-yellow-600 hover:to-orange-600 transition">
                            Claim
                        </button>
                        <?php elseif ($claimed): ?>
                        <span class="text-emerald-400 text-sm">✓ Claimed</span>
                        <?php else: ?>
                        <span class="text-white/40 text-sm">In Progress</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Weekly Quests -->
        <div class="glass rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-3">
                    <span class="text-3xl">📆</span> Weekly Quests
                </h2>
                <span class="text-sm text-white/60">Resets every Monday</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($weeklyQuests as $quest): ?>
                <?php
                    $progress = $quest['progress'] ?? 0;
                    $completed = $quest['completed'] ?? false;
                    $claimed = $quest['claimed'] ?? false;
                    $progressPercent = min(($progress / $quest['requirement_value']) * 100, 100);
                ?>
                <div class="bg-white/5 rounded-2xl p-4 <?= $completed && !$claimed ? 'border border-yellow-500/30 bg-yellow-500/5' : '' ?> <?= $claimed ? 'opacity-60' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-500/20 rounded-xl flex items-center justify-center text-xl">
                                🏆
                            </div>
                            <div>
                                <h3 class="font-semibold <?= $claimed ? 'line-through text-white/40' : '' ?>"><?= htmlspecialchars($quest['name']) ?></h3>
                                <p class="text-sm text-white/60"><?= htmlspecialchars($quest['description']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-white/60">Progress</span>
                            <span class="<?= $completed ? 'text-emerald-400' : 'text-white/60' ?>">
                                <?= $progress ?>/<?= $quest['requirement_value'] ?>
                            </span>
                        </div>
                        <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 <?= $completed ? 'bg-emerald-500' : 'bg-gradient-to-r from-purple-500 to-pink-500' ?>" 
                                 style="width: <?= $progressPercent ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Rewards -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-3 text-sm">
                            <?php if ($quest['reward_gold'] > 0): ?>
                            <span class="text-yellow-400">💰 <?= $quest['reward_gold'] ?></span>
                            <?php endif; ?>
                            <?php if ($quest['reward_gems'] > 0): ?>
                            <span class="text-cyan-400">💎 <?= $quest['reward_gems'] ?></span>
                            <?php endif; ?>
                            <?php if ($quest['reward_battle_pass_xp'] > 0): ?>
                            <span class="text-purple-400">📜 <?= $quest['reward_battle_pass_xp'] ?> BP XP</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($completed && !$claimed): ?>
                        <button onclick="claimQuest(<?= $quest['user_quest_id'] ?>)" 
                                class="px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg text-sm font-semibold hover:from-yellow-600 hover:to-orange-600 transition">
                            Claim
                        </button>
                        <?php elseif ($claimed): ?>
                        <span class="text-emerald-400 text-sm">✓ Claimed</span>
                        <?php else: ?>
                        <span class="text-white/40 text-sm">In Progress</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '<?= \Core\Session::csrfToken() ?>';

function claimQuest(userQuestId) {
    fetch(`/quests/${userQuestId}/claim`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const r = data.rewards;
            let msg = 'Reward claimed!';
            if (r && r.gold > 0) msg += ` +${r.gold} gold`;
            if (r && r.gems > 0) msg += ` +${r.gems} gems`;
            showToast(msg, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to claim', 'error');
        }
    });
}

function claimAll() {
    fetch('/quests/claim-all', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`${data.message} +${data.gold} gold, +${data.gems} gems`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to claim', 'error');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
