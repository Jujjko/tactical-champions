<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">Analytics</h1>
                <p class="text-white/60 mt-2">Game metrics and user behavior</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Back to Admin</a>
        </div>

        <!-- Daily Active Users Chart -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Daily Active Users (7 days)</h2>
            <div class="flex items-end gap-2 h-40">
                <?php 
                $maxUsers = max(array_column($dailyActive, 'users') ?: [1]);
                foreach ($dailyActive as $day): 
                    $height = ($day['users'] / $maxUsers) * 100;
                ?>
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg transition-all hover:from-indigo-400 hover:to-purple-400" 
                         style="height: <?= $height ?>%"></div>
                    <div class="text-xs text-white/60 mt-2"><?= date('D', strtotime($day['date'])) ?></div>
                    <div class="text-sm font-bold"><?= $day['users'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Page Views -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-4">Page Views (7 days)</h2>
                <div class="space-y-2">
                    <?php foreach ($pageViews as $pv): ?>
                    <div class="flex items-center justify-between p-2 bg-white/5 rounded-lg">
                        <span class="text-white/60"><?= $pv['date'] ?></span>
                        <span class="font-bold"><?= number_format($pv['count']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Battle Starts -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-xl font-bold mb-4">Battles Started (7 days)</h2>
                <div class="space-y-2">
                    <?php foreach ($battleStarts as $bs): ?>
                    <div class="flex items-center justify-between p-2 bg-white/5 rounded-lg">
                        <span class="text-white/60"><?= $bs['date'] ?></span>
                        <span class="font-bold"><?= number_format($bs['count']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Events -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Top Events (7 days)</h2>
            <div class="grid grid-cols-5 gap-4">
                <?php foreach ($topEvents as $event): ?>
                <div class="bg-white/5 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-indigo-400"><?= number_format($event['count']) ?></div>
                    <div class="text-sm text-white/60"><?= $event['event_type'] ?></div>
                    <div class="text-xs text-white/40"><?= $event['event_category'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Retention -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold mb-4">User Retention (Cohorts)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-white/5">
                            <th class="p-3 text-left">Cohort Date</th>
                            <th class="p-3 text-center">New Users</th>
                            <th class="p-3 text-center">Day 0</th>
                            <th class="p-3 text-center">Day 1</th>
                            <th class="p-3 text-center">Day 7</th>
                            <th class="p-3 text-center">Day 30</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($retention, 0, 14) as $row): ?>
                        <tr class="border-t border-white/5">
                            <td class="p-3"><?= $row['cohort_date'] ?></td>
                            <td class="p-3 text-center font-bold"><?= $row['total_users'] ?></td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400">
                                    <?= $row['day_0'] ?> (<?= $row['total_users'] > 0 ? round($row['day_0'] / $row['total_users'] * 100) : 0 ?>%)
                                </span>
                            </td>
                            <td class="p-3 text-center"><?= $row['day_1'] ?></td>
                            <td class="p-3 text-center"><?= $row['day_7'] ?></td>
                            <td class="p-3 text-center"><?= $row['day_30'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';