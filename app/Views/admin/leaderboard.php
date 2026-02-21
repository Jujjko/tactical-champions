<?php
ob_start();
?>
<div class="container fade-in">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">🏆 Global Leaderboard</h2>
            <div>
                <a href="/admin" class="btn btn-sm btn-secondary">Back to Admin</a>
            </div>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Level</th>
                    <th>Champions</th>
                    <th>Battles</th>
                    <th>Victories</th>
                    <th>Win Rate</th>
                    <th>Gold</th>
                    <th>Gems</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaderboard)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 2rem;">
                        No players found
                    </td>
                </tr>
                <?php else: ?>
                    <?php $rank = ($page - 1) * 50 + 1; ?>
                    <?php foreach ($leaderboard as $player): ?>
                    <tr>
                        <td>
                            <?php if ($rank <= 3): ?>
                                <span style="font-size: 1.5rem;">
                                    <?= $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : '🥉') ?>
                                </span>
                            <?php else: ?>
                                #<?= $rank ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($player['username']) ?></strong>
                        </td>
                        <td>
                            <span class="tier-badge tier-<?= $player['level'] >= 50 ? 'mythic' : ($player['level'] >= 30 ? 'legendary' : ($player['level'] >= 15 ? 'epic' : 'common')) ?>">
                                Level <?= $player['level'] ?>
                            </span>
                        </td>
                        <td><?= $player['champion_count'] ?></td>
                        <td><?= $player['total_battles'] ?></td>
                        <td style="color: var(--success);"><?= $player['victories'] ?></td>
                        <td>
                            <span style="color: <?= $player['win_rate'] >= 50 ? 'var(--success)' : 'var(--warning)' ?>">
                                <?= number_format($player['win_rate'], 1) ?>%
                            </span>
                        </td>
                        <td>💰 <?= number_format($player['gold']) ?></td>
                        <td>💎 <?= $player['gems'] ?></td>
                    </tr>
                    <?php $rank++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (count($leaderboard) === 50): ?>
        <div class="card-footer text-center">
            <a href="/admin/leaderboard?page=<?= $page + 1 ?>" class="btn btn-primary">
                Next Page →
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
