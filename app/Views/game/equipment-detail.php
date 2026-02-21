<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <a href="/equipment" class="inline-flex items-center gap-2 text-white/60 hover:text-white mb-8 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Equipment
        </a>

        <div class="grid grid-cols-12 gap-10">
            <!-- Left - Visual -->
            <div class="col-span-12 lg:col-span-5">
                <div class="glass rounded-3xl overflow-hidden neon-glow">
                    <div class="h-72 bg-gradient-to-br from-violet-900 via-purple-900 to-fuchsia-900 flex items-center justify-center text-[8rem]">
                        <?php
                        $typeIcons = ['weapon' => '⚔️', 'armor' => '🛡️', 'accessory' => '💍'];
                        echo $typeIcons[$equipment['type']] ?? '📦';
                        ?>
                    </div>
                    <div class="p-6 text-center">
                        <div class="text-sm text-white/60 uppercase tracking-widest">Level</div>
                        <div class="text-2xl font-bold mt-1"><?= $userEquipment['level'] ?></div>
                    </div>
                </div>

                <?php if ($userEquipment['is_equipped']): ?>
                <form method="POST" action="/equipment/<?= $userEquipment['id'] ?>/unequip" id="unequipForm">
                    <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                    <button type="button" onclick="unequipItem()" 
                            class="w-full mt-6 py-5 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-xl font-bold rounded-2xl transition">
                        🗑️ Unequip Item
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Right - Info -->
            <div class="col-span-12 lg:col-span-7 space-y-6">
                <div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <h1 class="title-font text-5xl font-bold"><?= htmlspecialchars($equipment['name']) ?></h1>
                        <span class="tier-<?= $equipment['tier'] ?> text-lg px-4 py-2 rounded-full text-white">
                            <?= ucfirst($equipment['tier']) ?>
                        </span>
                    </div>
                    <div class="text-2xl text-white/60 mt-2">
                        <?= ucfirst($equipment['type']) ?> • <?= ucfirst(str_replace('_', ' ', $equipment['slot'])) ?>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <?php if ($equipment['health_bonus'] > 0): ?>
                    <div class="glass rounded-2xl p-5">
                        <div class="text-emerald-400 text-sm uppercase tracking-widest">Health Bonus</div>
                        <div class="text-4xl font-bold mt-2">+<?= $equipment['health_bonus'] ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($equipment['attack_bonus'] > 0): ?>
                    <div class="glass rounded-2xl p-5">
                        <div class="text-rose-400 text-sm uppercase tracking-widest">Attack Bonus</div>
                        <div class="text-4xl font-bold mt-2">+<?= $equipment['attack_bonus'] ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($equipment['defense_bonus'] > 0): ?>
                    <div class="glass rounded-2xl p-5">
                        <div class="text-amber-400 text-sm uppercase tracking-widest">Defense Bonus</div>
                        <div class="text-4xl font-bold mt-2">+<?= $equipment['defense_bonus'] ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($equipment['speed_bonus'] != 0): ?>
                    <div class="glass rounded-2xl p-5">
                        <div class="text-cyan-400 text-sm uppercase tracking-widest">Speed Bonus</div>
                        <div class="text-4xl font-bold mt-2 <?= $equipment['speed_bonus'] < 0 ? 'text-red-400' : '' ?>">
                            <?= $equipment['speed_bonus'] > 0 ? '+' : '' ?><?= $equipment['speed_bonus'] ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($equipment['description']): ?>
                <div class="glass rounded-2xl p-5">
                    <p class="text-white/80 leading-relaxed"><?= htmlspecialchars($equipment['description']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($equipment['special_effect'])): ?>
                <div class="glass rounded-2xl p-5">
                    <div class="text-purple-400 font-semibold flex items-center gap-3 mb-3">
                        <span class="text-2xl">✨</span> SPECIAL EFFECT
                    </div>
                    <p class="text-lg leading-relaxed"><?= htmlspecialchars($equipment['special_effect']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Equip to Champion -->
                <?php if (!$userEquipment['is_equipped'] && !empty($champions)): ?>
                <div class="glass rounded-2xl p-6">
                    <div class="text-white/60 text-sm uppercase tracking-widest mb-4">Equip to Champion</div>
                    <form method="POST" action="/equipment/<?= $userEquipment['id'] ?>/equip" id="equipForm">
                        <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                        <select name="champion_id" id="championSelect" class="w-full p-4 bg-white/10 rounded-xl text-white border border-white/20 focus:border-indigo-500 outline-none mb-4">
                            <option value="">Select a champion...</option>
                            <?php foreach ($champions as $champion): ?>
                            <option value="<?= $champion['id'] ?>">
                                <?= htmlspecialchars($champion['name']) ?> (Level <?= $champion['level'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="equipItem()" 
                                class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 font-bold rounded-xl transition">
                            ⚔️ Equip to Selected Champion
                        </button>
                    </form>
                </div>
                <?php elseif ($userEquipment['is_equipped']): ?>
                <div class="glass rounded-2xl p-6">
                    <div class="text-emerald-400 text-sm uppercase tracking-widest mb-2">Currently Equipped To</div>
                    <div class="text-xl font-bold">
                        <?php foreach ($champions as $c): ?>
                            <?php if ($c['id'] == $userEquipment['equipped_to_champion_id']): ?>
                                <?= htmlspecialchars($c['name']) ?> (Level <?= $c['level'] ?>)
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function equipItem() {
    const select = document.getElementById('championSelect');
    if (!select.value) {
        alert('Please select a champion');
        return;
    }
    
    const form = document.getElementById('equipForm');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Equipment equipped successfully!');
            location.reload();
        } else {
            alert(data.error || 'Failed to equip item');
        }
    })
    .catch(err => alert('Error: ' + err.message));
}

function unequipItem() {
    if (!confirm('Unequip this item?')) return;
    
    const form = document.getElementById('unequipForm');
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Equipment unequipped successfully!');
            location.reload();
        } else {
            alert(data.error || 'Failed to unequip item');
        }
    })
    .catch(err => alert('Error: ' + err.message));
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
