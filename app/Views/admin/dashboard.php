<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <h1 class="title-font text-5xl font-bold mb-10">Admin Dashboard</h1>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-6 mb-12">
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-indigo-400"><?= $totalUsers ?></div>
                <div class="uppercase text-sm tracking-widest mt-2 text-white/60">Total Users</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-emerald-400"><?= $totalBattles ?></div>
                <div class="uppercase text-sm tracking-widest mt-2 text-white/60">Total Battles</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-yellow-400"><?= count($leaderboard) ?></div>
                <div class="uppercase text-sm tracking-widest mt-2 text-white/60">Top Players</div>
            </div>
            <div class="glass rounded-3xl p-8 text-center hover-lift">
                <div class="text-6xl font-bold text-purple-400"><?= count($recentLogs) ?></div>
                <div class="uppercase text-sm tracking-widest mt-2 text-white/60">Recent Actions</div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-12">
            <a href="/admin/users" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">👥</div>
                <h3 class="text-xl font-semibold">Users</h3>
                <p class="text-white/60 text-sm mt-2">Manage accounts</p>
            </a>
            <a href="/admin/champions" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">⚔️</div>
                <h3 class="text-xl font-semibold">Champions</h3>
                <p class="text-white/60 text-sm mt-2">Create & edit</p>
            </a>
            <a href="/admin/equipment" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">🛡️</div>
                <h3 class="text-xl font-semibold">Equipment</h3>
                <p class="text-white/60 text-sm mt-2">Weapons & armor</p>
            </a>
            <a href="/admin/missions" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">🎯</div>
                <h3 class="text-xl font-semibold">Missions</h3>
                <p class="text-white/60 text-sm mt-2">Configure battles</p>
            </a>
            <a href="/admin/leaderboard" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">🏆</div>
                <h3 class="text-xl font-semibold">Leaderboard</h3>
                <p class="text-white/60 text-sm mt-2">Player rankings</p>
            </a>
            <a href="/admin/pvp-history" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">⚔️</div>
                <h3 class="text-xl font-semibold">PvP History</h3>
                <p class="text-white/60 text-sm mt-2">Match records</p>
            </a>
            <a href="/admin/bulk-rewards" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">🎁</div>
                <h3 class="text-xl font-semibold">Bulk Rewards</h3>
                <p class="text-white/60 text-sm mt-2">Send to users</p>
            </a>
            <a href="/admin/seasons" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">📅</div>
                <h3 class="text-xl font-semibold">Seasons</h3>
                <p class="text-white/60 text-sm mt-2">PvP seasons</p>
            </a>
            <a href="/admin/analytics" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">📊</div>
                <h3 class="text-xl font-semibold">Analytics</h3>
                <p class="text-white/60 text-sm mt-2">Game metrics</p>
            </a>
            <a href="/admin/logs" class="glass rounded-3xl p-8 text-center card-hover">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-xl font-semibold">System Logs</h3>
                <p class="text-white/60 text-sm mt-2">Errors & debug</p>
            </a>
        </div>

        <!-- Recent Audit Logs -->
        <div class="glass rounded-3xl p-8">
            <h2 class="text-2xl font-bold mb-6">Recent Activity</h2>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <?php foreach ($recentLogs as $log): ?>
                <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                    <div class="flex items-center gap-4">
                        <span class="text-xl">
                            <?php
                            $icons = [
                                'create' => '➕',
                                'update' => '✏️',
                                'delete' => '🗑️',
                                'toggle' => '🔄',
                                'login' => '🔑',
                                'logout' => '🚪',
                            ];
                            echo $icons[$log['action']] ?? '📝';
                            ?>
                        </span>
                        <div>
                            <div class="font-semibold"><?= htmlspecialchars($log['action']) ?> <?= htmlspecialchars($log['entity_type']) ?></div>
                            <div class="text-xs text-white/60">
                                <?= $log['username'] ?? 'System' ?> • <?= date('M j, H:i', strtotime($log['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-white/40">
                        IP: <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';