<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">⚔️ Champion Management</h1>
                <p class="text-white/60 mt-2">Create, edit, and manage champions</p>
            </div>
            <button onclick="showCreateForm()" class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl font-semibold flex items-center gap-3 hover:scale-105 transition">
                <i data-lucide="plus" class="w-5 h-5"></i> Create Champion
            </button>
        </div>

        <div class="glass rounded-3xl overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-white/60 text-sm border-b border-white/10">
                        <th class="p-5 font-medium">ID</th>
                        <th class="p-5 font-medium">Image</th>
                        <th class="p-5 font-medium">Name</th>
                        <th class="p-5 font-medium">Tier</th>
                        <th class="p-5 font-medium">HP</th>
                        <th class="p-5 font-medium">ATK</th>
                        <th class="p-5 font-medium">DEF</th>
                        <th class="p-5 font-medium">SPD</th>
                        <th class="p-5 font-medium">Ability</th>
                        <th class="p-5 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($champions as $champion): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="p-5 text-white/40">#<?= $champion['id'] ?></td>
                        <td class="p-5">
                            <?php if (!empty($champion['image_url'])): ?>
                                <img src="<?= htmlspecialchars($champion['image_url']) ?>" alt="" class="w-12 h-12 rounded-xl object-cover">
                            <?php else: ?>
                                <span class="text-2xl"><?= $champion['icon'] ?? '🛡️' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 font-semibold"><?= htmlspecialchars($champion['name']) ?></td>
                        <td class="p-5">
                            <span class="tier-<?= $champion['tier'] ?> text-xs px-3 py-1 rounded-full text-white">
                                <?= ucfirst($champion['tier']) ?>
                            </span>
                        </td>
                        <td class="p-5 text-emerald-400"><?= $champion['base_health'] ?></td>
                        <td class="p-5 text-rose-400"><?= $champion['base_attack'] ?></td>
                        <td class="p-5 text-amber-400"><?= $champion['base_defense'] ?></td>
                        <td class="p-5 text-cyan-400"><?= $champion['base_speed'] ?></td>
                        <td class="p-5 text-white/60 text-sm max-w-32 truncate">
                            <?= htmlspecialchars($champion['special_ability'] ?? '-') ?>
                        </td>
                        <td class="p-5">
                            <div class="flex gap-2">
                                <button onclick='editChampion(<?= json_encode($champion, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                                        class="px-4 py-2 bg-indigo-500/20 hover:bg-indigo-500/40 text-indigo-400 rounded-xl text-sm font-medium transition flex items-center gap-2">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i> Edit
                                </button>
                                <button onclick="deleteChampion(<?= $champion['id'] ?>, <?= htmlspecialchars(json_encode($champion['name']), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 rounded-xl text-sm font-medium transition flex items-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isset($last_page) && $last_page > 1): ?>
        <div class="flex justify-center gap-2 mt-8">
            <?php for ($i = 1; $i <= $last_page; $i++): ?>
            <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-xl <?= $page == $i ? 'bg-indigo-500' : 'bg-white/10 hover:bg-white/20' ?> transition">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Champion Modal -->
<div id="championModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100]">
    <div class="glass rounded-3xl p-8 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 id="modalTitle" class="text-2xl font-semibold">Create Champion</h3>
            <button onclick="closeModal()" class="text-white/60 hover:text-white transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form id="championForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
            <input type="hidden" name="champion_id" id="champion_id">
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-white/80 mb-2">Name</label>
                <input type="text" name="name" id="champ_name" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" required>
            </div>
            
            <!-- Image Upload Section -->
            <div class="mb-5 p-4 bg-white/5 rounded-xl border border-white/10">
                <label class="block text-sm font-medium text-white/80 mb-3">Champion Image</label>
                
                <div class="flex items-start gap-4">
                    <!-- Preview -->
                    <div id="image_preview" class="w-24 h-24 bg-white/10 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                        <span id="preview_icon" class="text-4xl">🛡️</span>
                        <img id="preview_img" src="" alt="" class="w-full h-full object-cover hidden">
                    </div>
                    
                    <div class="flex-1 space-y-3">
                        <!-- File Upload -->
                        <label class="block">
                            <span class="cursor-pointer px-4 py-2 bg-indigo-500/20 hover:bg-indigo-500/40 text-indigo-400 rounded-lg text-sm inline-flex items-center gap-2 transition">
                                <i data-lucide="upload" class="w-4 h-4"></i> Upload Image
                            </span>
                            <input type="file" name="image_file" id="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="previewImage(this)">
                        </label>
                        <p class="text-xs text-white/40">JPG, PNG, WebP, GIF (max 5MB)</p>
                        
                        <!-- Or URL -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-white/40">or</span>
                            <input type="text" name="image_url" id="champ_image" class="flex-1 bg-white/5 border border-white/10 rounded-lg py-2 px-3 text-white text-sm focus:outline-none focus:border-indigo-500" placeholder="Image URL">
                        </div>
                    </div>
                </div>
                
                <!-- Icon fallback -->
                <div class="mt-3 flex items-center gap-3">
                    <label class="text-xs text-white/60">Icon (fallback):</label>
                    <input type="text" name="icon" id="champ_icon" class="w-20 bg-white/5 border border-white/10 rounded-lg py-2 px-3 text-white text-center text-lg" placeholder="🛡️" maxlength="4" oninput="updatePreviewIcon(this.value)">
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-white/80 mb-2">Tier</label>
                <select name="tier" id="champ_tier" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" required>
                    <option value="common">Common</option>
                    <option value="rare">Rare</option>
                    <option value="epic">Epic</option>
                    <option value="legendary">Legendary</option>
                    <option value="mythic">Mythic</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Health</label>
                    <input type="number" name="health" id="champ_health" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" value="100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Attack</label>
                    <input type="number" name="attack" id="champ_attack" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" value="10" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Defense</label>
                    <input type="number" name="defense" id="champ_defense" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" value="5" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Speed</label>
                    <input type="number" name="speed" id="champ_speed" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500" value="50" required>
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-white/80 mb-2">Special Ability</label>
                <input type="text" name="special_ability" id="champ_ability" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-white/80 mb-2">Description</label>
                <textarea name="description" id="champ_desc" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl font-semibold text-lg transition hover:scale-[1.02]">
                Save Champion
            </button>
        </form>
    </div>
</div>

<script>
const csrfToken = '<?= \Core\Session::csrfToken() ?>';

function previewImage(input) {
    const previewImg = document.getElementById('preview_img');
    const previewIcon = document.getElementById('preview_icon');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.classList.remove('hidden');
            previewIcon.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updatePreviewIcon(value) {
    const previewIcon = document.getElementById('preview_icon');
    const previewImg = document.getElementById('preview_img');
    if (value && previewImg.classList.contains('hidden')) {
        previewIcon.textContent = value;
    }
}

function resetPreview() {
    document.getElementById('preview_img').classList.add('hidden');
    document.getElementById('preview_img').src = '';
    document.getElementById('preview_icon').classList.remove('hidden');
    document.getElementById('preview_icon').textContent = '🛡️';
}

function showCreateForm() {
    document.getElementById('modalTitle').textContent = 'Create Champion';
    document.getElementById('championForm').reset();
    document.getElementById('champion_id').value = '';
    resetPreview();
    document.getElementById('championModal').classList.remove('hidden');
    document.getElementById('championModal').classList.add('flex');
    lucide.createIcons();
}

function editChampion(champion) {
    document.getElementById('modalTitle').textContent = 'Edit Champion';
    document.getElementById('champion_id').value = champion.id;
    document.getElementById('champ_name').value = champion.name;
    document.getElementById('champ_tier').value = champion.tier;
    document.getElementById('champ_health').value = champion.base_health;
    document.getElementById('champ_attack').value = champion.base_attack;
    document.getElementById('champ_defense').value = champion.base_defense;
    document.getElementById('champ_speed').value = champion.base_speed;
    document.getElementById('champ_ability').value = champion.special_ability || '';
    document.getElementById('champ_desc').value = champion.description || '';
    document.getElementById('champ_image').value = champion.image_url || '';
    document.getElementById('champ_icon').value = champion.icon || '';
    
    // Update preview
    const previewImg = document.getElementById('preview_img');
    const previewIcon = document.getElementById('preview_icon');
    if (champion.image_url) {
        previewImg.src = champion.image_url;
        previewImg.classList.remove('hidden');
        previewIcon.classList.add('hidden');
    } else {
        previewImg.classList.add('hidden');
        previewIcon.classList.remove('hidden');
        previewIcon.textContent = champion.icon || '🛡️';
    }
    
    document.getElementById('championModal').classList.remove('hidden');
    document.getElementById('championModal').classList.add('flex');
    lucide.createIcons();
}

function closeModal() {
    document.getElementById('championModal').classList.add('hidden');
    document.getElementById('championModal').classList.remove('flex');
}

function deleteChampion(id, name) {
    if (!confirm(`Delete champion "${name}"? This cannot be undone.`)) return;
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    
    fetch(`/admin/champions/${id}/delete`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.error || 'Failed to delete');
        });
}

document.getElementById('championForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('champion_id').value;
    const formData = new FormData(this);
    
    const url = id ? `/admin/champions/${id}/update` : '/admin/champions/create';
    
    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to save');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
});

document.getElementById('championModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>