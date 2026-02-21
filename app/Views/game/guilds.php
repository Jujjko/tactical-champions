<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">🏰 Guilds</h1>
                <p class="text-white/60 text-xl mt-2">Join or create a guild</p>
            </div>
            <?php if (!$userGuild): ?>
            <button onclick="showCreateModal()" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-600 transition">
                Create Guild
            </button>
            <?php endif; ?>
        </div>

        <?php if ($userGuild): ?>
        <!-- User's Guild -->
        <div class="glass rounded-3xl p-8 mb-8 neon-glow">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-3xl">⚔️</div>
                    <div>
                        <h2 class="text-3xl font-bold">[<?= htmlspecialchars($userGuild['tag']) ?>] <?= htmlspecialchars($userGuild['name']) ?></h2>
                        <div class="text-white/60">Level <?= $userGuild['level'] ?> • <?= ucfirst($userGuild['role'] ?? 'member') ?></div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="/guilds/<?= $userGuild['guild_id'] ?>" class="px-5 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">View</a>
                    <?php if ($userGuild['role'] !== 'leader'): ?>
                    <button onclick="leaveGuild()" class="px-5 py-2 bg-red-500/20 text-red-400 rounded-xl hover:bg-red-500/40 transition">Leave</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recruiting Guilds -->
            <div class="glass rounded-3xl p-6">
                <h2 class="text-2xl font-bold mb-4">🎯 Recruiting Guilds</h2>
                <?php if (empty($recruitingGuilds)): ?>
                <p class="text-white/60 text-center py-8">No guilds currently recruiting</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recruitingGuilds as $guild): ?>
                    <div class="bg-white/5 rounded-2xl p-4 hover:bg-white/10 transition cursor-pointer" onclick="showJoinModal(<?= $guild['id'] ?>, '<?= htmlspecialchars($guild['name']) ?>', '<?= htmlspecialchars($guild['tag']) ?>', <?= $guild['level'] ?>, <?= $guild['member_count'] ?>, <?= $guild['max_members'] ?>)">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold">[<?= htmlspecialchars($guild['tag']) ?>] <?= htmlspecialchars($guild['name']) ?></div>
                                <div class="text-sm text-white/60">Level <?= $guild['level'] ?> • <?= $guild['member_count'] ?>/<?= $guild['max_members'] ?> members</div>
                            </div>
                            <button class="text-indigo-400 hover:text-indigo-300 px-3 py-1 bg-indigo-500/20 rounded-lg text-sm">Join</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Guild Leaderboard -->
            <div class="glass rounded-3xl p-6">
                <h2 class="text-2xl font-bold mb-4">🏆 Top Guilds</h2>
                <?php if (empty($leaderboard)): ?>
                <p class="text-white/60 text-center py-8">No guilds yet</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($leaderboard as $i => $guild): ?>
                    <a href="/guilds/<?= $guild['id'] ?>" class="flex items-center gap-4 p-3 rounded-xl <?= $i < 3 ? 'bg-white/5' : '' ?> hover:bg-white/10 transition">
                        <div class="text-xl font-bold w-8">
                            <?= $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) ?>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold">[<?= htmlspecialchars($guild['tag']) ?>] <?= htmlspecialchars($guild['name']) ?></div>
                            <div class="text-xs text-white/60">Level <?= $guild['level'] ?></div>
                        </div>
                        <div class="text-sm text-white/60"><?= $guild['member_count'] ?> members</div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Guild Modal -->
<div id="createModal" class="modal">
    <div class="modal-content max-w-md">
        <div class="modal-header">
            <h3 class="text-xl font-bold">⚔️ Create Guild</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-white/60 mb-2">Guild Name</label>
                <input type="text" id="guildName" class="form-input w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none" maxlength="50" placeholder="Enter guild name...">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Tag (2-5 characters)</label>
                <input type="text" id="guildTag" class="form-input w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none uppercase" maxlength="5" placeholder="TAG">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Description</label>
                <textarea id="guildDesc" class="form-input w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none resize-none" rows="3" placeholder="Describe your guild..."></textarea>
            </div>
            
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 text-center">
                <div class="text-sm text-yellow-400">Creation Cost</div>
                <div class="text-2xl font-bold text-yellow-400">💰 1000 Gold</div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeCreateModal()" class="flex-1 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition">Cancel</button>
                <button onclick="createGuild()" id="createGuildBtn" class="flex-1 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 rounded-xl font-semibold transition">Create Guild</button>
            </div>
        </div>
    </div>
</div>

<!-- Join Guild Modal -->
<div id="joinModal" class="modal">
    <div class="modal-content max-w-md">
        <div class="modal-header">
            <h3 class="text-xl font-bold">🏰 Join Guild</h3>
            <button class="modal-close" onclick="closeJoinModal()">&times;</button>
        </div>
        
        <div class="text-center py-4">
            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-4xl mb-4">⚔️</div>
            <h4 id="joinGuildName" class="text-2xl font-bold mb-1"></h4>
            <p id="joinGuildInfo" class="text-white/60"></p>
        </div>
        
        <div class="flex gap-3">
            <button onclick="closeJoinModal()" class="flex-1 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-semibold transition">Cancel</button>
            <button onclick="joinGuild()" id="joinGuildBtn" class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 rounded-xl font-semibold transition">Join Guild</button>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div id="successToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass rounded-2xl px-8 py-4 border border-emerald-500/30 bg-emerald-500/20 flex items-center gap-3 shadow-xl">
        <span class="text-2xl">✅</span>
        <span id="successMessage" class="text-emerald-400 font-medium"></span>
    </div>
</div>

<div id="errorToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="glass rounded-2xl px-8 py-4 border border-red-500/30 bg-red-500/20 flex items-center gap-3 shadow-xl">
        <span class="text-2xl">❌</span>
        <span id="errorMessage" class="text-red-400 font-medium"></span>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;
let joinGuildId = null;

function showCreateModal() {
    document.getElementById('createModal').classList.add('active');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('active');
}

function showJoinModal(guildId, name, tag, level, members, maxMembers) {
    joinGuildId = guildId;
    document.getElementById('joinGuildName').textContent = `[${tag}] ${name}`;
    document.getElementById('joinGuildInfo').textContent = `Level ${level} • ${members}/${maxMembers} members`;
    document.getElementById('joinModal').classList.add('active');
}

function closeJoinModal() {
    document.getElementById('joinModal').classList.remove('active');
    joinGuildId = null;
}

function createGuild() {
    const name = document.getElementById('guildName').value.trim();
    const tag = document.getElementById('guildTag').value.trim().toUpperCase();
    const desc = document.getElementById('guildDesc').value.trim();
    
    if (name.length < 3) {
        showToast('Guild name must be at least 3 characters', 'error');
        return;
    }
    if (tag.length < 2) {
        showToast('Tag must be at least 2 characters', 'error');
        return;
    }
    
    const btn = document.getElementById('createGuildBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Creating...</span>';
    
    fetch('/guilds/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&name=${encodeURIComponent(name)}&tag=${encodeURIComponent(tag)}&description=${encodeURIComponent(desc)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeCreateModal();
            showToast(data.message || 'Guild created!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to create guild', 'error');
            btn.disabled = false;
            btn.textContent = 'Create Guild';
        }
    })
    .catch(err => {
        showToast('Error: ' + err.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Create Guild';
    });
}

function joinGuild() {
    if (!joinGuildId) return;
    
    const btn = document.getElementById('joinGuildBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Joining...</span>';
    
    fetch(`/guilds/${joinGuildId}/join`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeJoinModal();
            showToast(data.data?.message || 'Joined guild!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to join guild', 'error');
            btn.disabled = false;
            btn.textContent = 'Join Guild';
        }
    })
    .catch(err => {
        showToast('Error: ' + err.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Join Guild';
    });
}

function leaveGuild() {
    if (!confirm('Are you sure you want to leave your guild?')) return;
    
    fetch('/guilds/leave', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Left guild successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to leave guild', 'error');
        }
    });
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

// Auto-uppercase tag
document.getElementById('guildTag').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
