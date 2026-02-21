<?php
ob_start();
$typeIcons = ['weapon' => '⚔️', 'armor' => '🛡️', 'accessory' => '💍'];
$slotNames = [
    'main_hand' => 'Main Hand', 'off_hand' => 'Off Hand',
    'helmet' => 'Helmet', 'chest' => 'Chest',
    'gloves' => 'Gloves', 'boots' => 'Boots',
    'ring' => 'Ring', 'amulet' => 'Amulet'
];
?>
<div class="container fade-in">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">⚔️ Equipment Management</h2>
            <div>
                <button class="btn btn-sm btn-primary" onclick="showCreateForm()">Create Equipment</button>
                <a href="/admin" class="btn btn-sm btn-secondary">Back to Admin</a>
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Slot</th>
                    <th>Tier</th>
                    <th>Stats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 2rem;">
                        No equipment found. Create your first equipment item!
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td>
                            <span class="mr-2"><?= $typeIcons[$item['type']] ?? '📦' ?></span>
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td><?= ucfirst($item['type']) ?></td>
                        <td><?= $slotNames[$item['slot']] ?? $item['slot'] ?></td>
                        <td>
                            <span class="tier-<?= $item['tier'] ?> text-xs px-2 py-1 rounded-full text-white">
                                <?= ucfirst($item['tier']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $stats = [];
                            if ($item['health_bonus'] > 0) $stats[] = '<span class="text-emerald-400">+' . $item['health_bonus'] . ' HP</span>';
                            if ($item['attack_bonus'] > 0) $stats[] = '<span class="text-rose-400">+' . $item['attack_bonus'] . ' ATK</span>';
                            if ($item['defense_bonus'] > 0) $stats[] = '<span class="text-amber-400">+' . $item['defense_bonus'] . ' DEF</span>';
                            if ($item['speed_bonus'] != 0) $stats[] = '<span class="' . ($item['speed_bonus'] > 0 ? 'text-cyan-400">+' : 'text-red-400">') . $item['speed_bonus'] . ' SPD</span>';
                            echo implode(' | ', $stats) ?: '-';
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick='editEquipment(<?= json_encode($item) ?>)'>Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteEquipment(<?= $item['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($has_more): ?>
        <div class="card-footer text-center">
            <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary">Load More</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Equipment Modal -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Create New Equipment</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="equipmentForm">
            <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
            <input type="hidden" name="equipment_id" id="equipment_id">
            
            <div class="form-group">
                <label class="form-label">Equipment Name</label>
                <input type="text" name="name" id="eq_name" class="form-control" required>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" id="eq_type" class="form-select" required>
                        <option value="weapon">Weapon</option>
                        <option value="armor">Armor</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slot</label>
                    <select name="slot" id="eq_slot" class="form-select" required>
                        <option value="main_hand">Main Hand</option>
                        <option value="off_hand">Off Hand</option>
                        <option value="helmet">Helmet</option>
                        <option value="chest">Chest</option>
                        <option value="gloves">Gloves</option>
                        <option value="boots">Boots</option>
                        <option value="ring">Ring</option>
                        <option value="amulet">Amulet</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tier</label>
                    <select name="tier" id="eq_tier" class="form-select" required>
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="epic">Epic</option>
                        <option value="legendary">Legendary</option>
                        <option value="mythic">Mythic</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Health Bonus</label>
                    <input type="number" name="health_bonus" id="eq_health" class="form-control" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Attack Bonus</label>
                    <input type="number" name="attack_bonus" id="eq_attack" class="form-control" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Defense Bonus</label>
                    <input type="number" name="defense_bonus" id="eq_defense" class="form-control" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Speed Bonus</label>
                    <input type="number" name="speed_bonus" id="eq_speed" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="eq_description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Special Effect</label>
                <textarea name="special_effect" id="eq_special" class="form-control" rows="2"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Save Equipment
            </button>
        </form>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('modalTitle').textContent = 'Create New Equipment';
    document.getElementById('equipmentForm').reset();
    document.getElementById('equipment_id').value = '';
    document.getElementById('equipmentModal').classList.add('active');
}

function editEquipment(item) {
    document.getElementById('modalTitle').textContent = 'Edit Equipment';
    document.getElementById('equipment_id').value = item.id;
    document.getElementById('eq_name').value = item.name;
    document.getElementById('eq_type').value = item.type;
    document.getElementById('eq_slot').value = item.slot;
    document.getElementById('eq_tier').value = item.tier;
    document.getElementById('eq_health').value = item.health_bonus;
    document.getElementById('eq_attack').value = item.attack_bonus;
    document.getElementById('eq_defense').value = item.defense_bonus;
    document.getElementById('eq_speed').value = item.speed_bonus;
    document.getElementById('eq_description').value = item.description || '';
    document.getElementById('eq_special').value = item.special_effect || '';
    document.getElementById('equipmentModal').classList.add('active');
}

function closeModal() {
    document.getElementById('equipmentModal').classList.remove('active');
}

function deleteEquipment(id) {
    if (!confirm('Delete this equipment?')) return;
    
    fetch(`/admin/equipment/${id}/delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to delete');
        }
    });
}

document.getElementById('equipmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = formData.get('equipment_id');
    const url = id ? `/admin/equipment/${id}/update` : '/admin/equipment/create';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(id ? 'Equipment updated!' : 'Equipment created!');
            location.reload();
        } else {
            alert(data.error || 'Failed to save');
        }
    })
    .catch(() => {
        if (!id) location.reload();
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
