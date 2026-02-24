<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="title-font text-6xl font-bold flex items-center gap-4">
                    ⚔️ Your Champions
                </h1>
                <p class="text-white/70 text-2xl mt-2"><span id="visible-count"><?= count($champions) ?></span> warriors ready for battle</p>
            </div>
            <a href="/lootbox" 
               class="px-8 py-5 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl font-semibold flex items-center gap-3 hover:scale-105 transition-all">
                📦 Open Lootbox
            </a>
        </div>

        <!-- Filters -->
        <div class="glass rounded-2xl p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Name Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           id="filter-name" 
                           placeholder="Search by name..." 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition">
                </div>
                
                <!-- Tier Filter -->
                <div class="relative">
                    <select id="filter-tier" 
                            class="appearance-none bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-10 text-white focus:outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition cursor-pointer min-w-[140px]">
                        <option value="">All Tiers</option>
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="epic">Epic</option>
                        <option value="legendary">Legendary</option>
                        <option value="mythic">Mythic</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none"></i>
                </div>
                
                <!-- Stars Filter -->
                <div class="relative">
                    <select id="filter-stars" 
                            class="appearance-none bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-10 text-white focus:outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition cursor-pointer min-w-[140px]">
                        <option value="">All Stars</option>
                        <option value="1">⭐ 1 Star</option>
                        <option value="2">⭐⭐ 2 Stars</option>
                        <option value="3">⭐⭐⭐ 3 Stars</option>
                        <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-white/50 pointer-events-none"></i>
                </div>
                
                <!-- Clear Filters -->
                <button id="clear-filters" class="px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white/70 hover:text-white transition text-sm hidden">
                    Clear Filters
                </button>
            </div>
            
            <!-- Active Filters Tags -->
            <div id="active-filters" class="flex flex-wrap gap-2 mt-4 hidden">
            </div>
        </div>

        <?php if (empty($champions)): ?>
        <div class="glass rounded-3xl p-12 text-center">
            <div class="text-6xl mb-4">⚔️</div>
            <h3 class="text-xl font-semibold mb-2">No Champions Yet!</h3>
            <p class="text-white/60 mb-6">Open lootboxes to discover powerful champions!</p>
            <a href="/lootbox" class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition">
                Open Lootboxes
            </a>
        </div>
        <?php else: ?>
        <div id="champions-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
            <?php foreach ($champions as $index => $champion): 
                $tierColors = [
                    'common' => ['from-zinc-600 to-slate-700', 'border-zinc-500/30'],
                    'rare' => ['from-blue-600 to-cyan-700', 'border-blue-500/30'],
                    'epic' => ['from-purple-600 to-pink-700', 'border-purple-500/30'],
                    'legendary' => ['from-amber-600 to-orange-700', 'border-amber-500/30'],
                    'mythic' => ['from-red-600 to-rose-700', 'border-red-500/30'],
                ];
                $tierStyle = $tierColors[$champion['tier']] ?? $tierColors['common'];
            ?>
            <a href="/champions/<?= $champion['id'] ?>" 
               class="champion-card group relative cursor-pointer" 
               data-name="<?= htmlspecialchars($champion['name'], ENT_QUOTES, 'UTF-8') ?>"
               data-tier="<?= htmlspecialchars($champion['tier'], ENT_QUOTES, 'UTF-8') ?>"
               data-stars="<?= (int)($champion['stars'] ?? 1) ?>"
               style="animation-delay: <?= $index * 60 ?>ms">

                <!-- Tier Background Glow -->
                <div class="absolute -inset-1 rounded-3xl bg-gradient-to-br <?= $tierStyle[0] ?> opacity-20 group-hover:opacity-40 blur-xl transition-all duration-500"></div>
                
                <!-- Tier Border Glow -->
                <div class="absolute -inset-[2px] rounded-3xl bg-gradient-to-br 
                    <?= $champion['tier'] === 'mythic' ? 'from-red-500 via-orange-500 to-amber-500' : 
                       ($champion['tier'] === 'legendary' ? 'from-amber-500 via-yellow-500 to-orange-500' :
                       ($champion['tier'] === 'epic' ? 'from-purple-500 via-pink-500 to-violet-500' : 
                       ($champion['tier'] === 'rare' ? 'from-blue-500 to-cyan-500' : 'from-zinc-500 to-slate-500'))) ?> 
                    opacity-30 group-hover:opacity-70 transition-all duration-500 -z-10"></div>

                <div class="relative glass rounded-3xl overflow-hidden transition-all duration-500 group-hover:-translate-y-4 group-hover:scale-[1.04] border <?= $tierStyle[1] ?>">
                    
                    <!-- Champion Image Area -->
                    <div class="h-56 bg-gradient-to-br <?= $tierStyle[0] ?> flex items-center justify-center relative overflow-hidden">
                        <?php if (!empty($champion['image_url'])): ?>
                            <img src="<?= htmlspecialchars($champion['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($champion['name']) ?>"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-full h-full items-center justify-center text-8xl hidden">
                                <?= $champion['icon'] ?? '🛡️' ?>
                            </div>
                        <?php else: ?>
                            <span class="text-8xl"><?= $champion['icon'] ?? '🛡️' ?></span>
                        <?php endif; ?>
                        <!-- Tier Badge -->
                        <div class="absolute top-4 right-4">
                            <span class="tier-badge tier-<?= $champion['tier'] ?> px-4 py-1.5 text-xs font-medium shadow-lg">
                                <?= ucfirst($champion['tier']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-lg leading-tight"><?= htmlspecialchars($champion['name']) ?></h3>
                                <div class="text-white/50 text-sm">Lv.<?= $champion['level'] ?></div>
                            </div>
                            <div class="flex items-center gap-0.5 text-sm">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <?php if ($s <= ($champion['stars'] ?? 1)): ?>
                                        <span class="text-yellow-400">⭐</span>
                                    <?php else: ?>
                                        <span class="text-white/20">☆</span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="flex items-center gap-2 bg-white/5 rounded-lg px-2 py-1">
                                <span class="text-base">❤️</span>
                                <span class="font-bold text-emerald-400"><?= $champion['health'] ?></span>
                            </div>
                            <div class="flex items-center gap-2 bg-white/5 rounded-lg px-2 py-1">
                                <span class="text-base">⚔️</span>
                                <span class="font-bold text-rose-400"><?= $champion['attack'] ?></span>
                            </div>
                            <div class="flex items-center gap-2 bg-white/5 rounded-lg px-2 py-1">
                                <span class="text-base">🛡️</span>
                                <span class="font-bold text-amber-400"><?= $champion['defense'] ?></span>
                            </div>
                             <div class="flex items-center gap-2 bg-white/5 rounded-lg px-2 py-1">
                                <span class="text-base">⚡</span>
                                <span class="font-bold text-sky-400"><?= $champion['speed'] ?></span>
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
