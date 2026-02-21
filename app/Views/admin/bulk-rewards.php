<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">Bulk Rewards</h1>
                <p class="text-white/60 mt-2">Send rewards to multiple users at once</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Back to Admin</a>
        </div>

        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">

        <!-- Stats -->
        <div class="glass rounded-2xl p-6 mb-8">
            <div class="text-3xl font-bold text-indigo-400"><?= $totalUsers ?></div>
            <div class="text-sm text-white/60 mt-1">Active users available</div>
        </div>

        <form id="bulkRewardForm" class="space-y-6">
            <!-- Target Selection -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-4">Target Users</h2>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="target_type" value="all" checked class="w-5 h-5">
                        <span>All active users (<?= $totalUsers ?>)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="target_type" value="level_min" class="w-5 h-5">
                        <span>Users with minimum level:</span>
                        <input type="number" name="min_level" value="1" min="1" class="w-20 bg-white/10 border border-white/20 rounded-lg px-3 py-1">
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="target_type" value="selected" class="w-5 h-5">
                        <span>Selected users:</span>
                    </label>
                    
                    <div id="userSelector" class="hidden mt-3 max-h-60 overflow-y-auto bg-white/5 rounded-xl p-4">
                        <?php foreach ($users as $user): ?>
                        <label class="flex items-center gap-2 p-2 hover:bg-white/5 rounded cursor-pointer">
                            <input type="checkbox" name="target_users[]" value="<?= $user['id'] ?>" class="w-4 h-4">
                            <span><?= htmlspecialchars($user['username']) ?></span>
                            <span class="text-white/40 text-sm">Lv.<?= $user['level'] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Rewards -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-4">Rewards</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/60 mb-2">Gold</label>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">💰</span>
                            <input type="number" name="gold" value="0" min="0" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-white/60 mb-2">Gems</label>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">💎</span>
                            <input type="number" name="gems" value="0" min="0" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-white/60 mb-2">Energy</label>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">⚡</span>
                            <input type="number" name="energy" value="0" min="0" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-white/60 mb-2">Lootboxes</label>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">📦</span>
                            <input type="number" name="lootbox_count" value="0" min="0" class="w-24 bg-white/10 border border-white/20 rounded-xl px-4 py-3">
                            <select name="lootbox_type" class="flex-1 bg-white/10 border border-white/20 rounded-xl px-3 py-3">
                                <option value="bronze">Bronze</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-bold text-lg hover:from-indigo-600 hover:to-purple-600 transition">
                Send Rewards
            </button>
        </form>
    </div>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h2 class="text-2xl font-bold mb-4">Confirm Bulk Rewards</h2>
        <div id="confirmDetails" class="mb-6"></div>
        <div class="flex gap-4">
            <button onclick="closeModal()" class="flex-1 py-3 bg-white/10 rounded-xl hover:bg-white/20 transition">Cancel</button>
            <button onclick="confirmSend()" class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-green-500 rounded-xl font-semibold hover:from-emerald-600 hover:to-green-600 transition">Confirm</button>
        </div>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;
const form = document.getElementById('bulkRewardForm');
let formData = {};

document.querySelectorAll('input[name="target_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('userSelector').classList.toggle('hidden', this.value !== 'selected');
    });
});

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    formData = new FormData(form);
    const targetType = formData.get('target_type');
    
    let targetDesc = '';
    if (targetType === 'all') {
        targetDesc = 'All active users (<?= $totalUsers ?>)';
    } else if (targetType === 'level_min') {
        targetDesc = `Users level ${formData.get('min_level')}+`;
    } else {
        const selected = document.querySelectorAll('input[name="target_users[]"]:checked').length;
        targetDesc = `${selected} selected users`;
    }
    
    let rewards = [];
    if (formData.get('gold') > 0) rewards.push(`💰 ${formData.get('gold')} gold`);
    if (formData.get('gems') > 0) rewards.push(`💎 ${formData.get('gems')} gems`);
    if (formData.get('energy') > 0) rewards.push(`⚡ ${formData.get('energy')} energy`);
    if (formData.get('lootbox_count') > 0) rewards.push(`📦 ${formData.get('lootbox_count')} ${formData.get('lootbox_type')} lootboxes`);
    
    document.getElementById('confirmDetails').innerHTML = `
        <div class="glass rounded-xl p-4 mb-4">
            <div class="text-white/60 text-sm">Target</div>
            <div class="font-semibold">${targetDesc}</div>
        </div>
        <div class="glass rounded-xl p-4">
            <div class="text-white/60 text-sm">Rewards</div>
            <div class="font-semibold">${rewards.length > 0 ? rewards.join(', ') : 'None'}</div>
        </div>
    `;
    
    document.getElementById('confirmModal').classList.add('active');
});

function closeModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

function confirmSend() {
    closeModal();
    
    fetch('/admin/bulk-rewards/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData).toString() + `&csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`Rewards sent to ${data.data.recipients} users!`, 'success');
            form.reset();
        } else {
            showToast(data.error || 'Failed to send rewards', 'error');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';