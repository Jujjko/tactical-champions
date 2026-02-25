<?php 
ob_start();

$rarityColors = [
    'common' => ['glow' => 'from-zinc-500/20 to-slate-600/20', 'border' => 'border-zinc-500/30', 'accent' => '#71717a'],
    'rare' => ['glow' => 'from-blue-500/20 to-cyan-600/20', 'border' => 'border-blue-500/30', 'accent' => '#3b82f6'],
    'epic' => ['glow' => 'from-purple-500/20 to-pink-600/20', 'border' => 'border-purple-500/30', 'accent' => '#a855f7'],
    'legendary' => ['glow' => 'from-amber-500/20 to-orange-600/20', 'border' => 'border-amber-500/30', 'accent' => '#f59e0b'],
    'mythic' => ['glow' => 'from-red-500/20 to-rose-600/20', 'border' => 'border-red-500/30', 'accent' => '#ef4444'],
];
?>
<style>
.champion-card {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
    animation: cardReveal 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes cardReveal {
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.champion-card:hover {
    transform: translateY(-12px) scale(1.02) !important;
}
.champion-card:hover .glow-bg {
    opacity: 0.5;
}
.glow-bg {
    transition: opacity 0.3s ease;
}
.card-inner {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<div class="min-h-screen bg-gradient-to-br from-[#0b0f1a] via-[#0d1220] to-[#121a2b] py-12 relative">
    <!-- Ambient Glow -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-purple-500/5 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-20 right-1/4 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="title-font text-5xl md:text-6xl font-bold flex items-center gap-4">
                    <span class="text-5xl">⚔️</span>
                    <span>Your Champions</span>
                </h1>
                <p class="text-white/60 text-xl mt-2">
                    <span id="visible-count"><?= count($champions) ?></span> warriors ready for battle
                </p>
            </div>
            <a href="/lootbox" 
               class="group px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl font-semibold flex items-center gap-3 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/20 transition-all">
                <span class="text-2xl group-hover:scale-110 transition">📦</span>
                <span>Open Lootbox</span>
            </a>
        </div>

        <!-- Filters -->
        <div class="glass rounded-2xl p-4 mb-8">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Name Search -->
                <div class="flex-1 min-w-[180px]">
                    <input type="text" 
                           id="filter-name" 
                           placeholder="Search champions..." 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:border-purple-500/50 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>
                
                <!-- Tier Filter -->
                <div class="relative">
                    <select id="filter-tier" 
                            class="appearance-none bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 pr-10 text-white focus:outline-none focus:border-purple-500/50 cursor-pointer min-w-[130px]">
                        <option value="">All Tiers</option>
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="epic">Epic</option>
                        <option value="legendary">Legendary</option>
                        <option value="mythic">Mythic</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-white/40 pointer-events-none"></i>
                </div>
                
                <!-- Stars Filter -->
                <div class="relative">
                    <select id="filter-stars" 
                            class="appearance-none bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 pr-10 text-white focus:outline-none focus:border-purple-500/50 cursor-pointer min-w-[130px]">
                        <option value="">All Stars</option>
                        <option value="1">⭐ 1★</option>
                        <option value="2">⭐⭐ 2★</option>
                        <option value="3">⭐⭐⭐ 3★</option>
                        <option value="4">⭐⭐⭐⭐ 4★</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5★</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-white/40 pointer-events-none"></i>
                </div>
                
                <!-- Clear Filters -->
                <button id="clear-filters" class="px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white/60 hover:text-white transition text-sm hidden">
                    Clear ✕
                </button>
            </div>
        </div>

        <?php if (empty($champions)): ?>
        <div class="glass rounded-3xl p-16 text-center">
            <div class="text-7xl mb-6">⚔️</div>
            <h3 class="text-2xl font-bold mb-3">No Champions Yet!</h3>
            <p class="text-white/50 text-lg mb-8">Open lootboxes to discover powerful champions!</p>
            <a href="/lootbox" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold py-4 px-10 rounded-2xl transition hover:scale-105 hover:shadow-lg hover:shadow-purple-500/20">
                Open Lootboxes
            </a>
        </div>
        <?php else: ?>
        <div id="champions-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <?php foreach ($champions as $index => $champion): 
                $tier = $champion['tier'] ?? 'common';
                $colors = $rarityColors[$tier] ?? $rarityColors['common'];
            ?>
            <a href="/champions/<?= $champion['id'] ?>" 
               class="champion-card group relative cursor-pointer" 
               data-name="<?= htmlspecialchars($champion['name'], ENT_QUOTES, 'UTF-8') ?>"
               data-tier="<?= $tier ?>"
               data-stars="<?= (int)($champion['stars'] ?? 1) ?>"
               style="animation-delay: <?= min($index * 30, 500) ?>ms">

                <!-- Rarity Glow -->
                <div class="glow-bg absolute -inset-0.5 rounded-2xl bg-gradient-to-br <?= $colors['glow'] ?> opacity-10 group-hover:opacity-40 blur-lg transition-all duration-500"></div>

                <div class="card-inner relative glass rounded-2xl overflow-hidden border <?= $colors['border'] ?> group-hover:border-opacity-80 transition-all duration-300">
                    <!-- Image -->
                    <div class="h-32 sm:h-36 bg-gradient-to-br <?= str_replace('/30', '', $colors['glow']) ?> group-hover:scale-105 transition-transform duration-500 flex items-center justify-center">
                        <?php if (!empty($champion['image_url'])): ?>
                            <img src="<?= htmlspecialchars($champion['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($champion['name']) ?>"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full items-center justify-center text-4xl hidden">
                                <?= $champion['icon'] ?? '🛡️' ?>
                            </div>
                        <?php else: ?>
                            <span class="text-4xl"><?= $champion['icon'] ?? '🛡️' ?></span>
                        <?php endif; ?>
                        
                        <!-- Tier Badge -->
                        <div class="absolute top-2 right-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider" 
                                  style="background: <?= $colors['accent'] ?>20; color: <?= $colors['accent'] ?>; border: 1px solid <?= $colors['accent'] ?>40;">
                                <?= substr($tier, 0, 3) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-3">
                        <h3 class="font-bold text-sm truncate text-white/90 group-hover:text-white transition"><?= htmlspecialchars($champion['name']) ?></h3>
                        
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-white/40">Lv.<?= $champion['level'] ?></span>
                            <div class="flex gap-0.5">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <span class="<?= $s <= ($champion['stars'] ?? 1) ? 'text-yellow-400' : 'text-white/10' ?> text-xs">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 gap-1 mt-2 text-[10px]">
                            <div class="flex items-center gap-1 text-emerald-400/60">
                                <span>❤️</span>
                                <span class="truncate"><?= $champion['health'] ?></span>
                            </div>
                            <div class="flex items-center gap-1 text-rose-400/60">
                                <span>⚔️</span>
                                <span class="truncate"><?= $champion['attack'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <!-- No Results -->
        <div id="no-results" class="glass rounded-3xl p-12 text-center hidden">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold mb-2">No Champions Found</h3>
            <p class="text-white/60">Try adjusting your filters</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.champion-card {
    opacity: 0;
    transform: translateY(40px);
    animation: cardPopIn 0.8s forwards cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes cardPopIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.champion-card:hover {
    transform: translateY(-18px) scale(1.05) !important;
}

.tier-badge {
    transition: all 0.3s ease;
}

.champion-card:hover .tier-badge {
    transform: scale(1.1);
    box-shadow: 0 0 15px currentColor;
}

.champion-card.hidden-by-filter {
    display: none;
}

select option {
    background: #1a1a2e;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    const filterName = document.getElementById('filter-name');
    const filterTier = document.getElementById('filter-tier');
    const filterStars = document.getElementById('filter-stars');
    const clearFilters = document.getElementById('clear-filters');
    const activeFilters = document.getElementById('active-filters');
    const noResults = document.getElementById('no-results');
    const visibleCount = document.getElementById('visible-count');
    const cards = document.querySelectorAll('.champion-card');
    
    function applyFilters() {
        const name = filterName.value.toLowerCase().trim();
        const tier = filterTier.value;
        const stars = filterStars.value;
        
        let visibleCards = 0;
        let activeTags = [];
        
        cards.forEach(card => {
            const cardName = (card.dataset.name || '').toLowerCase();
            const cardTier = card.dataset.tier || '';
            const cardStars = card.dataset.stars || '1';
            
            let show = true;
            
            if (name && !cardName.includes(name)) show = false;
            if (tier && cardTier !== tier) show = false;
            if (stars && cardStars !== stars) show = false;
            
            if (show) {
                card.classList.remove('hidden-by-filter');
                visibleCards++;
            } else {
                card.classList.add('hidden-by-filter');
            }
        });
        
        visibleCount.textContent = visibleCards;
        noResults.classList.toggle('hidden', visibleCards > 0);
        
        const hasFilters = name || tier || stars;
        clearFilters.classList.toggle('hidden', !hasFilters);
        
        updateActiveTags(name, tier, stars);
    }
    
    function updateActiveTags(name, tier, stars) {
        const tags = [];
        
        const esc = (str) => str.replace(/[<>"'&]/g, (c) => ({'<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','&':'&amp;'}[c]));
        
        if (name) {
            tags.push('<span class="inline-flex items-center gap-2 bg-indigo-500/20 text-indigo-400 px-3 py-1 rounded-full text-sm">' +
                'Name: "' + esc(name) + '" ' +
                '<button onclick="clearFilter(\'name\')" class="hover:text-white">×</button>' +
            '</span>');
        }
        if (tier) {
            const tierLabel = tier.charAt(0).toUpperCase() + tier.slice(1);
            tags.push('<span class="inline-flex items-center gap-2 bg-indigo-500/20 text-indigo-400 px-3 py-1 rounded-full text-sm">' +
                'Tier: ' + esc(tierLabel) + ' ' +
                '<button onclick="clearFilter(\'tier\')" class="hover:text-white">×</button>' +
            '</span>');
        }
        if (stars) {
            tags.push('<span class="inline-flex items-center gap-2 bg-indigo-500/20 text-indigo-400 px-3 py-1 rounded-full text-sm">' +
                'Stars: ' + '⭐'.repeat(parseInt(stars)) + ' ' +
                '<button onclick="clearFilter(\'stars\')" class="hover:text-white">×</button>' +
            '</span>');
        }
        
        if (tags.length > 0) {
            activeFilters.innerHTML = tags.join('');
            activeFilters.classList.remove('hidden');
        } else {
            activeFilters.classList.add('hidden');
        }
    }
    
    window.clearFilter = function(type) {
        switch(type) {
            case 'name': filterName.value = ''; break;
            case 'tier': filterTier.value = ''; break;
            case 'stars': filterStars.value = ''; break;
        }
        applyFilters();
    };
    
    filterName.addEventListener('input', applyFilters);
    filterTier.addEventListener('change', applyFilters);
    filterStars.addEventListener('change', applyFilters);
    
    clearFilters.addEventListener('click', function() {
        filterName.value = '';
        filterTier.value = '';
        filterStars.value = '';
        applyFilters();
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
