<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">🛒 Shop</h1>
                <p class="text-white/60 text-xl mt-2">Purchase items and resources</p>
            </div>
            <div class="flex gap-4">
                <div class="glass rounded-2xl px-6 py-3 flex items-center gap-3">
                    <span class="text-2xl">💰</span>
                    <div>
                        <div class="text-xl font-bold text-yellow-400" id="gold-display"><?= number_format($resources['gold']) ?></div>
                        <div class="text-xs text-white/60">Gold</div>
                    </div>
                </div>
                <div class="glass rounded-2xl px-6 py-3 flex items-center gap-3">
                    <span class="text-2xl">💎</span>
                    <div>
                        <div class="text-xl font-bold text-cyan-400" id="gems-display"><?= number_format($resources['gems']) ?></div>
                        <div class="text-xs text-white/60">Gems</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Items -->
        <?php if (!empty($featuredItems)): ?>
        <div class="glass rounded-3xl p-6 mb-8 neon-glow">
            <h2 class="text-2xl font-bold mb-4">⭐ Featured</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($featuredItems as $item): ?>
                <div class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-2xl p-4 border border-indigo-500/30">
                    <div class="text-4xl mb-3 text-center">📦</div>
                    <h3 class="font-semibold text-center mb-2"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-sm text-white/60 text-center mb-3"><?= htmlspecialchars($item['description']) ?></p>
                    <button onclick="showPurchaseModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', '<?= htmlspecialchars($item['description']) ?>', <?= $item['price_gems'] ?>, <?= $item['price_gold'] ?>)" 
                            class="w-full py-2 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold text-sm hover:from-indigo-600 hover:to-purple-600 transition">
                        <?= $item['price_gems'] > 0 ? '💎 ' . $item['price_gems'] : '💰 ' . $item['price_gold'] ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categories -->
        <?php foreach ($itemsByCategory as $category => $items): ?>
        <div class="glass rounded-3xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">
                <?php
                $catIcons = ['gems' => '💎', 'gold' => '💰', 'energy' => '⚡', 'special' => '🎁', 'cosmetic' => '🎨'];
                echo $catIcons[$category] ?? '📦';
                ?>
                <?= ucfirst($category) ?>
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($items as $item): ?>
                <div class="bg-white/5 rounded-xl p-4 text-center hover:bg-white/10 transition cursor-pointer"
                     onclick="showPurchaseModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', '<?= htmlspecialchars($item['description']) ?>', <?= $item['price_gems'] ?>, <?= $item['price_gold'] ?>)">
                    <div class="text-3xl mb-2">📦</div>
                    <h3 class="font-semibold text-sm mb-1"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-xs text-white/60 mb-3"><?= htmlspecialchars($item['description']) ?></p>
                    <div class="w-full py-2 bg-white/10 rounded-lg text-sm font-medium">
                        <?= $item['price_gems'] > 0 ? '💎' . $item['price_gems'] : '💰' . $item['price_gold'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Purchase Modal -->
<div id="purchaseModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closePurchaseModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="glass rounded-3xl p-8 border border-indigo-500/30 shadow-2xl">
            <div class="text-center">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-5xl mb-6 shadow-xl">
                    📦
                </div>
                <h3 id="modalItemName" class="text-2xl font-bold mb-2"></h3>
                <p id="modalItemDesc" class="text-white/60 mb-6"></p>
                
                <div class="glass rounded-2xl p-4 mb-6">
                    <div class="text-sm text-white/60 mb-2">Purchase Price</div>
                    <div id="modalPrice" class="text-3xl font-bold"></div>
                </div>
                
                <div class="glass rounded-2xl p-4 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-white/60">Your Balance</span>
                        <span id="modalBalance"></span>
                    </div>
                    <div id="modalBalanceWarning" class="hidden text-red-400 text-sm mt-2">
                        ⚠️ Insufficient funds!
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button onclick="closePurchaseModal()" class="flex-1 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition">
                        Cancel
                    </button>
                    <button id="modalConfirmBtn" onclick="confirmPurchase()" class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 rounded-xl font-semibold transition">
                        Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass rounded-2xl px-8 py-4 border border-emerald-500/30 bg-emerald-500/20 flex items-center gap-3 shadow-xl">
        <span class="text-2xl">✅</span>
        <span id="toastMessage" class="text-emerald-400 font-medium"></span>
    </div>
</div>

<!-- Error Toast -->
<div id="errorToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass rounded-2xl px-8 py-4 border border-red-500/30 bg-red-500/20 flex items-center gap-3 shadow-xl">
        <span class="text-2xl">❌</span>
        <span id="errorMessage" class="text-red-400 font-medium"></span>
    </div>
</div>

<script>
let currentItemId = null;
let canAfford = false;
const userGold = <?= $resources['gold'] ?>;
const userGems = <?= $resources['gems'] ?>;

function showPurchaseModal(itemId, name, desc, priceGems, priceGold) {
    currentItemId = itemId;
    
    document.getElementById('modalItemName').textContent = name;
    document.getElementById('modalItemDesc').textContent = desc;
    
    const priceEl = document.getElementById('modalPrice');
    const balanceEl = document.getElementById('modalBalance');
    const warningEl = document.getElementById('modalBalanceWarning');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    if (priceGems > 0) {
        priceEl.innerHTML = `<span class="text-cyan-400">💎 ${priceGems}</span>`;
        balanceEl.innerHTML = `<span class="text-cyan-400">💎 ${userGems}</span>`;
        canAfford = userGems >= priceGems;
    } else {
        priceEl.innerHTML = `<span class="text-yellow-400">💰 ${priceGold}</span>`;
        balanceEl.innerHTML = `<span class="text-yellow-400">💰 ${userGold}</span>`;
        canAfford = userGold >= priceGold;
    }
    
    if (canAfford) {
        warningEl.classList.add('hidden');
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        warningEl.classList.remove('hidden');
        confirmBtn.disabled = true;
        confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    document.getElementById('purchaseModal').classList.remove('hidden');
}

function closePurchaseModal() {
    document.getElementById('purchaseModal').classList.add('hidden');
    currentItemId = null;
}

function confirmPurchase() {
    if (!currentItemId || !canAfford) return;
    
    const btn = document.getElementById('modalConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Processing...</span>';
    
    const csrfToken = '<?= \Core\Session::csrfToken() ?>';
    
    fetch(`/shop/${currentItemId}/purchase`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrfToken}&quantity=1`
    })
    .then(res => res.json())
    .then(data => {
        closePurchaseModal();
        
        if (data.success) {
            showToast(data.message || 'Purchase successful!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Purchase failed', 'error');
        }
    })
    .catch(err => {
        closePurchaseModal();
        showToast('Error: ' + err.message, 'error');
    });
}

function showToast(message, type) {
    const toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
    const msg = document.getElementById(type === 'success' ? 'toastMessage' : 'errorMessage');
    
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
