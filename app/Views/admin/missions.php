<?php
// app/Views/admin/missions.php
ob_start();
?>
<div class="container fade-in">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">🎯 Mission Management</h2>
            <div>
                <button class="btn btn-sm btn-primary" onclick="showCreateForm()">Create Mission</button>
                <a href="/admin" class="btn btn-sm btn-secondary">Back to Admin</a>
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Difficulty</th>
                    <th>Required Level</th>
                    <th>Energy Cost</th>
                    <th>Rewards</th>
                    <th>Enemies</th>
                    <th>Lootbox Chance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($missions as $mission): ?>
                <tr>
                    <td><?= $mission['id'] ?></td>
                    <td><?= htmlspecialchars($mission['name']) ?></td>
                    <td>
                        <span class="difficulty-badge difficulty-<?= $mission['difficulty'] ?>">
                            <?= $mission['difficulty'] ?>
                        </span>
                    </td>
                    <td>Level <?= $mission['required_level'] ?></td>
                    <td>⚡ <?= $mission['energy_cost'] ?></td>
                    <td>
                        💰 <?= $mission['gold_reward'] ?><br>
                        ✨ <?= $mission['experience_reward'] ?> XP
                    </td>
                    <td>👹 <?= $mission['enemy_count'] ?></td>
                    <td><?= $mission['lootbox_chance'] ?>%</td>
                    <td>
                        <?php if ($mission['is_active']): ?>
                            <span style="color: var(--success);">● Active</span>
                        <?php else: ?>
                            <span style="color: var(--danger);">● Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button 
                            class="btn btn-sm <?= $mission['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                            onclick="toggleMission(<?= $mission['id'] ?>)">
                            <?= $mission['is_active'] ? 'Disable' : 'Enable' ?>
                        </button>
                        <button 
                            class="btn btn-sm btn-primary"
                            onclick="editMission(<?= htmlspecialchars(json_encode($mission)) ?>)">
                            Edit
                        </button>
                        <button 
                            class="btn btn-sm btn-danger"
                            onclick="deleteMission(<?= $mission['id'] ?>)">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Mission Modal -->
<div id="missionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Create New Mission</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="missionForm" method="POST" action="/admin/missions/save">
            <input type="hidden" name="mission_id" id="mission_id">
            
            <div class="form-group">
                <label class="form-label">Mission Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" id="difficulty" class="form-select" required>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Required Level</label>
                    <input type="number" name="required_level" id="required_level" class="form-control" value="1" min="1" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Energy Cost</label>
                    <input type="number" name="energy_cost" id="energy_cost" class="form-control" value="10" min="1" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Number of Enemies</label>
                    <input type="number" name="enemy_count" id="enemy_count" class="form-control" value="3" min="1" max="10" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gold Reward</label>
                    <input type="number" name="gold_reward" id="gold_reward" class="form-control" value="50" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Experience Reward</label>
                    <input type="number" name="experience_reward" id="experience_reward" class="form-control" value="25" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Lootbox Chance (%)</label>
                    <input type="number" name="lootbox_chance" id="lootbox_chance" class="form-control" value="10.00" min="0" max="100" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="is_active" class="form-select" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Save Mission
            </button>
        </form>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('modalTitle').textContent = 'Create New Mission';
    document.getElementById('missionForm').reset();
    document.getElementById('mission_id').value = '';
    document.getElementById('missionModal').classList.add('active');
}

function editMission(mission) {
    document.getElementById('modalTitle').textContent = 'Edit Mission';
    document.getElementById('mission_id').value = mission.id;
    document.getElementById('name').value = mission.name;
    document.getElementById('description').value = mission.description;
    document.getElementById('difficulty').value = mission.difficulty;
    document.getElementById('required_level').value = mission.required_level;
    document.getElementById('energy_cost').value = mission.energy_cost;
    document.getElementById('enemy_count').value = mission.enemy_count;
    document.getElementById('gold_reward').value = mission.gold_reward;
    document.getElementById('experience_reward').value = mission.experience_reward;
    document.getElementById('lootbox_chance').value = mission.lootbox_chance;
    document.getElementById('is_active').value = mission.is_active ? '1' : '0';
    document.getElementById('missionModal').classList.add('active');
}

function closeModal() {
    document.getElementById('missionModal').classList.remove('active');
}

function toggleMission(missionId) {
    if (!confirm('Toggle mission status?')) return;
    
    fetch(`/admin/missions/${missionId}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle mission');
        }
    });
}

function deleteMission(missionId) {
    if (!confirm('Are you sure you want to delete this mission? This action cannot be undone.')) return;
    
    fetch(`/admin/missions/${missionId}/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to delete mission');
        }
    });
}

// Handle form submission
document.getElementById('missionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/missions/save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Mission saved successfully!');
            location.reload();
        } else {
            alert(data.error || 'Failed to save mission');
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>