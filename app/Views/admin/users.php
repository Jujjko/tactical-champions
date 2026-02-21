<?php
// app/Views/admin/users.php
ob_start();
?>
<div class="container fade-in">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">👥 User Management</h2>
            <a href="/admin" class="btn btn-sm btn-secondary">Back to Admin</a>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="tier-badge tier-<?= $user['role'] === 'admin' ? 'legendary' : 'common' ?>">
                            <?= $user['role'] ?>
                        </span>
                    </td>
                    <td><?= $user['level'] ?></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span style="color: var(--success);">● Active</span>
                        <?php else: ?>
                            <span style="color: var(--danger);">● Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['id'] !== 1): // Don't allow disabling admin ?>
                            <button 
                                class="btn btn-sm <?= $user['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                                onclick="toggleUser(<?= $user['id'] ?>)">
                                <?= $user['is_active'] ? 'Disable' : 'Enable' ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleUser(userId) {
    if (!confirm('Toggle user status?')) return;
    
    fetch(`/admin/users/${userId}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle user');
        }
    });
}
</script>