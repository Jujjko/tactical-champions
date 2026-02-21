<?php ob_start(); ?>

<div id="successToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass px-6 py-3 rounded-xl border border-emerald-500/30 text-emerald-400">
        <span id="successMessage"></span>
    </div>
</div>
<div id="errorToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass px-6 py-3 rounded-xl border border-red-500/30 text-red-400">
        <span id="errorMessage"></span>
    </div>
</div>

<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-10">
            <h1 class="title-font text-5xl font-bold mb-4">Getting Started</h1>
            <p class="text-white/60 text-xl">Complete these steps to master Tactical Champions</p>
            
            <div class="mt-6 max-w-md mx-auto">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-white/60">Progress</span>
                    <span class="text-sm font-bold"><?= $completionPercent ?>%</span>
                </div>
                <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500" style="width: <?= $completionPercent ?>%"></div>
                </div>
            </div>
        </div>

        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">

        <?php if ($hasCompletedAll): ?>
        <div class="glass rounded-3xl p-8 text-center neon-glow">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-bold mb-4">Tutorial Complete!</h2>
            <p class="text-white/60 mb-6">You're ready to become a champion!</p>
            <a href="/dashboard" class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-600 transition">
                Go to Dashboard
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($steps as $key => $step): ?>
            <div class="glass rounded-2xl p-6 <?= $step['completed'] ? 'opacity-60' : ($key === $currentStep ? 'neon-glow' : '') ?>">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center text-3xl <?= $step['completed'] ? 'bg-emerald-500/20' : 'bg-indigo-500/20' ?>">
                        <?= $step['completed'] ? '✓' : $step['icon'] ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold <?= $step['completed'] ? 'text-emerald-400' : '' ?>"><?= $step['title'] ?></h3>
                        <p class="text-white/60 text-sm mt-1"><?= $step['description'] ?></p>
                        <?php if ($step['reward_gold'] > 0): ?>
                        <p class="text-yellow-400 text-sm mt-1">Reward: <?= $step['reward_gold'] ?> gold</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($step['completed']): ?>
                        <span class="px-4 py-2 bg-emerald-500/20 text-emerald-400 rounded-xl text-sm font-semibold">Completed</span>
                        <?php elseif ($key === $currentStep): ?>
                        <?php if (isset($step['action_url'])): ?>
                        <div class="flex gap-2">
                            <a href="<?= $step['action_url'] ?>" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 rounded-xl text-sm font-semibold transition">
                                <?= $step['action_text'] ?>
                            </a>
                            <button onclick="completeStep('<?= $key ?>')" class="px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-400 rounded-xl text-sm font-semibold transition">
                                Done
                            </button>
                        </div>
                        <?php else: ?>
                        <button onclick="completeStep('<?= $key ?>')" class="px-6 py-2 bg-gradient-to-r from-emerald-500 to-green-500 rounded-xl text-sm font-semibold hover:from-emerald-600 hover:to-green-600 transition">
                            Complete
                        </button>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="px-4 py-2 bg-white/10 text-white/40 rounded-xl text-sm">Locked</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 text-center">
            <button onclick="skipTutorial()" class="text-white/40 hover:text-white/60 text-sm transition">
                Skip Tutorial
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;

function completeStep(step) {
    fetch(`/tutorial/${step}/complete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${encodeURIComponent(csrf)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.data && data.data.reward_gold > 0) {
                showToast(`+${data.data.reward_gold} gold!`, 'success');
            }
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.error || 'Failed to complete step', 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function skipTutorial() {
    if (!confirm('Skip the tutorial? You can always view it later from the menu.')) return;
    
    fetch('/tutorial/skip', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${encodeURIComponent(csrf)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Tutorial skipped', 'success');
            setTimeout(() => location.href = '/dashboard', 500);
        }
    })
    .catch(err => showToast('Error skipping tutorial', 'error'));
}

function showToast(message, type) {
    const toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
    const msg = document.getElementById(type === 'success' ? 'successMessage' : 'errorMessage');
    
    msg.textContent = message;
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';