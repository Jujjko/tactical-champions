<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-5xl font-bold">PvP Arena</h1>
                <p class="text-white/60 text-xl mt-2">Challenge other players and climb the ranks</p>
            </div>
            <div class="flex gap-4 items-start">
                <?= \App\Helpers\RankHelper::getRankBadgeHtml((int)$rating['rating']) ?>
                <div class="glass rounded-2xl p-4 text-center">
                    <div class="text-sm text-white/60">W/L</div>
                    <div class="mt-1">
                        <span class="text-emerald-400 font-bold"><?= $rating['wins'] ?></span>
                        <span class="text-white/40">/</span>
                        <span class="text-red-400 font-bold"><?= $rating['losses'] ?></span>
                    </div>
                    <?php if ($rating['current_streak'] > 2): ?>
                    <div class="text-xs text-yellow-400 mt-1"><?= $rating['current_streak'] ?> win streak!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <?php if ($rankInfo['next_rank']): ?>
                <div class="glass rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-white/60">Progress to <?= $rankInfo['next_rank']['name'] ?></span>
                        <span class="text-sm" style="color: <?= $rankInfo['next_rank']['color'] ?>;">
                            <?= $rankInfo['next_rank']['rating_needed'] ?> rating needed
                        </span>
                    </div>
                    <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" 
                             style="width: <?= $rankInfo['progress'] ?>%; background: linear-gradient(90deg, <?= $rankInfo['color'] ?>, <?= $rankInfo['next_rank']['color'] ?>);"></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($queueEntry)): ?>
                <div class="glass rounded-3xl p-6 neon-glow">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold flex items-center gap-2">
                            <span class="animate-pulse">🔍</span> Finding Match...
                        </h2>
                        <button onclick="leaveQueue()" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 text-red-400 rounded-xl text-sm font-semibold transition">
                            Cancel
                        </button>
                    </div>
                    <div class="flex items-center gap-6 text-white/60">
                        <div>
                            <span class="text-2xl font-bold text-white" id="queuePosition"><?= $queueCount ?></span>
                            <span class="text-sm">in queue</span>
                        </div>
                        <div>
                            <span class="text-2xl font-bold text-white" id="waitTime">0</span>
                            <span class="text-sm">seconds</span>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-white/40">Searching for opponents near your rating...</div>
                </div>
                <?php else: ?>
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-2xl font-bold mb-4">Quick Match</h2>
                    <input type="hidden" id="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm text-white/60 mb-2">Select Your Champion</label>
                        <select id="matchmaking_champion_id" class="form-select w-full bg-white/10 border-white/20 rounded-xl px-4 py-3">
                            <option value="">Choose champion...</option>
                            <?php foreach ($champions as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Lv.<?= $c['level'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button onclick="joinQueue()" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-600 transition flex items-center justify-center gap-2">
                        <span class="text-xl">⚔️</span> Find Match
                    </button>
                    <p class="text-center text-sm text-white/40 mt-3">
                        Rating range: ±150 (expands after 30s)
                    </p>
                </div>
                <?php endif; ?>

                <?php if (!empty($pendingChallenges)): ?>
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                        <span>📥</span> Pending Challenges
                    </h2>
                    <div class="space-y-3">
                        <?php foreach ($pendingChallenges as $challenge): ?>
                        <div class="bg-white/5 rounded-2xl p-4 flex items-center justify-between">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($challenge['challenger_name']) ?></div>
                                <div class="text-sm text-white/60">
                                    Lv.<?= $challenge['challenger_champion_level'] ?> <?= htmlspecialchars($challenge['challenger_champion_name']) ?>
                                </div>
                                <div class="text-xs text-yellow-400 mt-1">Reward: <?= $challenge['rewards_gold'] ?> gold</div>
                            </div>
                            <div class="flex gap-2 items-center">
                                <select id="champion_<?= $challenge['id'] ?>" class="form-select text-sm bg-white/10 border-white/20 rounded-xl px-3 py-2">
                                    <option value="">Select champion</option>
                                    <?php foreach ($champions as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Lv.<?= $c['level'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button onclick="acceptChallenge(<?= $challenge['id'] ?>)" class="btn btn-sm bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl">Accept</button>
                                <button onclick="declineChallenge(<?= $challenge['id'] ?>)" class="btn btn-sm bg-red-500/20 hover:bg-red-500/40 text-red-400 px-4 py-2 rounded-xl">Decline</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="glass rounded-3xl p-6">
                    <h2 class="text-2xl font-bold mb-4">Challenge a Player</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-white/60 mb-2">Search Player</label>
                        <input type="text" id="playerSearch" class="form-input w-full bg-white/10 border-white/20 rounded-xl px-4 py-3" placeholder="Enter username...">
                        <input type="hidden" id="defender_id">
                        <div id="searchResults" class="mt-2 space-y-2"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm text-white/60 mb-2">Select Your Champion</label>
                        <select id="champion_id" class="form-select w-full bg-white/10 border-white/20 rounded-xl px-4 py-3">
                            <option value="">Choose champion...</option>
                            <?php foreach ($champions as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Lv.<?= $c['level'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button onclick="sendChallenge()" class="w-full py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-600 transition">
                        Send Challenge
                    </button>
                </div>

                <?php if (!empty($sentChallenges)): ?>
                <div class="glass rounded-3xl p-6">
                    <h2 class="text-2xl font-bold mb-4">Sent Challenges</h2>
                    <div class="space-y-3">
                        <?php foreach ($sentChallenges as $challenge): ?>
                        <div class="bg-white/5 rounded-2xl p-4 flex items-center justify-between">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($challenge['defender_name']) ?></div>
                                <div class="text-sm text-white/60">Status: <span class="capitalize <?= $challenge['status'] === 'pending' ? 'text-yellow-400' : ($challenge['status'] === 'completed' ? 'text-emerald-400' : 'text-white/40') ?>"><?= $challenge['status'] ?></span></div>
                            </div>
                            <div class="text-right text-sm text-white/40">
                                <?= date('M j, H:i', strtotime($challenge['created_at'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="glass rounded-3xl p-6 sticky top-24">
                    <h2 class="text-2xl font-bold mb-4">Top Players</h2>
                    <div class="space-y-3">
                        <?php foreach ($leaderboard as $i => $player): ?>
                        <?php $playerRank = \App\Helpers\RankHelper::getRank((int)$player['rating']); ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl <?= $i < 3 ? 'bg-white/5' : '' ?>">
                            <div class="text-2xl font-bold w-8">
                                <?= $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) ?>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold"><?= htmlspecialchars($player['username']) ?></div>
                                <div class="text-xs flex items-center gap-1">
                                    <span style="color: <?= $playerRank['color'] ?>"><?= $playerRank['icon'] ?></span>
                                    <span class="text-white/60"><?= $playerRank['name'] ?></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold" style="color: <?= $playerRank['color'] ?>"><?= $player['rating'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/arena/leaderboard" class="block mt-4 text-center text-indigo-400 hover:text-indigo-300">View Full Leaderboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="matchFoundModal" class="modal">
    <div class="modal-content text-center">
        <div class="text-6xl mb-4">⚔️</div>
        <h2 class="text-3xl font-bold mb-2">Match Found!</h2>
        <div id="opponentInfo" class="mt-4"></div>
        <button onclick="startMatch()" class="w-full mt-6 py-4 bg-gradient-to-r from-emerald-500 to-green-500 rounded-xl font-bold text-xl hover:from-emerald-600 hover:to-green-600 transition">
            Enter Battle!
        </button>
    </div>
</div>

<script>
const csrf = document.getElementById('csrf_token').value;
let matchmakingInterval = null;
let currentChallengeId = null;

<?php if (!empty($queueEntry)): ?>
startMatchmakingPoll();
<?php endif; ?>

document.getElementById('playerSearch').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }
    
    fetch(`/friends/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.friends) {
                const results = document.getElementById('searchResults');
                results.innerHTML = data.data.friends.slice(0, 5).map(f => `
                    <div class="bg-white/10 rounded-xl p-3 cursor-pointer hover:bg-white/20 transition" onclick="selectPlayer(${f.id}, '${f.username}')">
                        <div class="font-semibold">${f.username}</div>
                        <div class="text-xs text-white/60">Level ${f.level}</div>
                    </div>
                `).join('');
            }
        });
});

function selectPlayer(id, name) {
    document.getElementById('defender_id').value = id;
    document.getElementById('playerSearch').value = name;
    document.getElementById('searchResults').innerHTML = '';
}

function joinQueue() {
    const championId = document.getElementById('matchmaking_champion_id').value;
    if (!championId) {
        showToast('Please select a champion', 'error');
        return;
    }
    
    fetch('/arena/queue/join', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&champion_id=${championId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Joined matchmaking queue!', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.error || 'Failed to join queue', 'error');
        }
    });
}

function leaveQueue() {
    fetch('/arena/queue/leave', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (matchmakingInterval) clearInterval(matchmakingInterval);
            showToast('Left matchmaking queue', 'success');
            setTimeout(() => location.reload(), 500);
        }
    });
}

function startMatchmakingPoll() {
    let waitTime = 0;
    matchmakingInterval = setInterval(() => {
        waitTime++;
        document.getElementById('waitTime').textContent = waitTime;
        
        fetch('/arena/queue/check')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.data.status === 'matched') {
                        clearInterval(matchmakingInterval);
                        showMatchFound(data.data);
                    } else if (data.data.status === 'searching') {
                        document.getElementById('queuePosition').textContent = data.data.queue_count;
                    } else {
                        clearInterval(matchmakingInterval);
                        location.reload();
                    }
                }
            });
    }, 2000);
}

function showMatchFound(match) {
    currentChallengeId = match.challenge_id;
    const opponent = match.opponent;
    
    document.getElementById('opponentInfo').innerHTML = `
        <div class="glass rounded-xl p-4 mt-4">
            <div class="text-lg font-bold">${opponent.username}</div>
            <div class="text-sm text-white/60">${opponent.champion_name} Lv.${opponent.champion_level}</div>
            <div class="flex items-center justify-center gap-2 mt-2">
                <span style="color: ${opponent.rank.color}">${opponent.rank.icon}</span>
                <span style="color: ${opponent.rank.color}">${opponent.rank.full_name}</span>
                <span class="text-white/60">(${opponent.rating})</span>
            </div>
        </div>
    `;
    
    document.getElementById('matchFoundModal').classList.add('active');
}

function startMatch() {
    if (currentChallengeId) {
        window.location.href = `/battle/prepare/${currentChallengeId}`;
    }
}

function sendChallenge() {
    const defenderId = document.getElementById('defender_id').value;
    const championId = document.getElementById('champion_id').value;
    
    if (!defenderId) {
        showToast('Please select a player to challenge', 'error');
        return;
    }
    if (!championId) {
        showToast('Please select your champion', 'error');
        return;
    }
    
    fetch('/arena/challenge', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&defender_id=${defenderId}&champion_id=${championId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to send challenge', 'error');
        }
    });
}

function acceptChallenge(challengeId) {
    const championId = document.getElementById('champion_' + challengeId).value;
    if (!championId) {
        showToast('Please select a champion', 'error');
        return;
    }
    
    fetch(`/arena/challenge/${challengeId}/accept`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}&champion_id=${championId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to accept challenge', 'error');
        }
    });
}

function declineChallenge(challengeId) {
    if (!confirm('Decline this challenge?')) return;
    
    fetch(`/arena/challenge/${challengeId}/decline`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=${csrf}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Challenge declined', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.error || 'Failed to decline', 'error');
        }
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';