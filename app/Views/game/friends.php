<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">👥 Friends</h1>
                <p class="text-white/60 text-xl mt-2">Connect with other players</p>
            </div>
            <div class="glass rounded-2xl p-4 text-center">
                <div class="text-3xl font-bold text-indigo-400"><?= $friendCount ?></div>
                <div class="text-xs text-white/60 uppercase tracking-widest">Friends</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Friend -->
            <div class="lg:col-span-2">
                <div class="glass rounded-3xl p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">🔍 Add Friend</h2>
                    <form method="POST" action="/friends/add" id="addFriendForm">
                        <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                        <div class="flex gap-4">
                            <input type="text" id="searchInput" class="form-input flex-1 bg-white/10 border-white/20 rounded-xl px-4 py-3" placeholder="Search by username...">
                            <input type="hidden" name="friend_id" id="friendId">
                            <button type="submit" id="addBtn" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold" disabled>
                                Add
                            </button>
                        </div>
                    </form>
                    <div id="searchResults" class="mt-4 space-y-2"></div>
                </div>

                <!-- Pending Requests -->
                <?php if (!empty($pendingRequests)): ?>
                <div class="glass rounded-3xl p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">📥 Friend Requests (<?= count($pendingRequests) ?>)</h2>
                    <div class="space-y-3">
                        <?php foreach ($pendingRequests as $request): ?>
                        <div class="bg-white/5 rounded-xl p-4 flex items-center justify-between">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($request['username']) ?></div>
                                <div class="text-sm text-white/60">Level <?= $request['level'] ?></div>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="/friends/<?= $request['user_id'] ?>/accept">
                                    <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                                    <button type="submit" class="px-4 py-2 bg-emerald-500 rounded-lg">Accept</button>
                                </form>
                                <form method="POST" action="/friends/<?= $request['user_id'] ?>/decline">
                                    <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                                    <button type="submit" class="px-4 py-2 bg-white/10 text-white/60 rounded-lg">Decline</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Friends List -->
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-2xl font-bold mb-4">👥 Your Friends</h2>
                    <?php if (empty($friends)): ?>
                    <p class="text-white/60 text-center py-8">No friends yet. Add someone above!</p>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($friends as $friend): ?>
                        <div class="bg-white/5 rounded-xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-xl">👤</div>
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($friend['username']) ?></div>
                                    <div class="text-sm text-white/60">Level <?= $friend['level'] ?></div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button class="px-4 py-2 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/40">Challenge</button>
                                <form method="POST" action="/friends/<?= $friend['friend_id'] ?>/remove">
                                    <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                                    <button type="submit" class="px-4 py-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/40">Remove</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sent Requests -->
            <div>
                <?php if (!empty($sentRequests)): ?>
                <div class="glass rounded-3xl p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-4">📤 Sent Requests</h2>
                    <div class="space-y-3">
                        <?php foreach ($sentRequests as $request): ?>
                        <div class="bg-white/5 rounded-xl p-3">
                            <div class="font-semibold"><?= htmlspecialchars($request['username']) ?></div>
                            <div class="text-xs text-yellow-400">Pending...</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value;
    const results = document.getElementById('searchResults');
    const addBtn = document.getElementById('addBtn');
    const friendId = document.getElementById('friendId');
    
    if (query.length < 2) {
        results.innerHTML = '';
        addBtn.disabled = true;
        friendId.value = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('/friends/search?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                results.innerHTML = '';
                if (data.data && data.data.users) {
                    data.data.users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'bg-white/5 rounded-lg p-3 flex items-center justify-between cursor-pointer hover:bg-white/10';
                        div.innerHTML = `
                            <div>
                                <div class="font-semibold">${user.username}</div>
                                <div class="text-sm text-white/60">Level ${user.level}</div>
                            </div>
                        `;
                        div.onclick = () => {
                            document.getElementById('searchInput').value = user.username;
                            document.getElementById('friendId').value = user.id;
                            document.getElementById('addBtn').disabled = false;
                            results.innerHTML = '';
                        };
                        results.appendChild(div);
                    });
                }
            });
    }, 300);
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
