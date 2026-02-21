# TODO-v2.md - Tactical Champions
**Verzija:** 2.5 (21.02.2026)  
**Status:** Production Ready - Phase 5 gotov (93%)  
**Cilj:** Napraviti igru zabavnom, socijalnom i spremnom za prvi veći playtest (cilj: 100+ igrača u prvom tjednu)

---

## ✅ STATUS NAKON TODO-v1

| Phase | Feature | Status |
|-------|---------|--------|
| Phase 1 | PvP Arena | ✅ Dovršeno (baza, modeli, controller, view) |
| Phase 2 | Shop | ✅ Dovršeno (shop_items, user_purchases, controller) |
| Phase 2 | Battle Pass | ✅ Dovršeno (sezone, leveli, rewards) |
| Phase 2 | Daily Login | ✅ Dovršeno (7-day streak) |
| Phase 3 | Friends | ✅ Dovršeno (friends tabela, controller) |
| Phase 3 | Guilds | ✅ Dovršeno (guilds, guild_members, controller) |
| Phase 3 | Leaderboards | ✅ Dovršeno (Arena + Admin global) |
| Phase 4 | Equipment | ✅ Dovršeno (equipment, user_equipment, 8 slotova) |

**Temelj je čvrst. Sada fokus na retention i engagement.**

---

## 🔥 PHASE 1 – PvP Arena (NAJVIŠI PRIORITET – 8–12 dana)

### 1.1 Database & Models (1 dan) ✅ ZAVRŠENO
- [x] Kreiraj migraciju `2025_02_19_0001_create_pvp_tables.sql`
  - `pvp_battles` (id, attacker_id, defender_id, winner_id, result, duration, rank_points_change, season_id, created_at)
  - `pvp_rankings` (user_id, season_id, rank_points, current_rank, wins, losses, win_streak)
  - `pvp_seasons` (id, name, start_date, end_date, is_active)
- [x] Kreiraj modele:
  - `App\Models\PvpBattle`
  - `App\Models\PvpRanking`
  - `App\Models\PvpSeason`
- [x] Dodaj u `User` model metode: `getPvpRank()`, `addRankPoints()`

### 1.2 Backend Logika (3–4 dana) ✅ ZAVRŠENO
- [x] Kreiraj `App\Services\PvpService`
  - `findMatch(int $userId)` – matchmaking po ranku (±150 bodova)
  - `challengeFriend(int $userId, int $friendId)`
  - `calculateRankChange(int $winnerId, int $loserId)`
  - `processBattleEnd(PvpBattle $battle, string $result)`
- [x] Kreiraj `PvPController` (nasljeđuje BaseController):
  - `index()` → `/pvp` (rank, season info, buttons)
  - `findMatch()` → POST
  - `challenge()` → POST
  - `battleStart()` → POST (koristi postojeći BattleEngine)
  - `battleAction()` → POST
  - `battleEnd()` → POST
- [x] Proširi `BattleEngine` sa `isPvp = true` (dodaj rank points u summary)
- [x] Dodaj u `BattleStateManager` podršku za PvP (različiti ključevi: `pvp_battle:{battleId}`)

### 1.3 Frontend & UI (2–3 dana) ✅ ZAVRŠENO
- [x] Nova stranica `/pvp` (`views/game/pvp.php`)
  - Rank badge + trenutni rank points
  - "Find Match" veliki button
  - "Challenge Friend" forma
  - Trenutna sezona + countdown
  - Top 10 leaderboard (ovaj season)
- [x] Prilagodi `battle-arena.php` za PvP:
  - Prikaz protivničkog imena na vrhu
  - Dodatni "Rank Change" tekst na kraju
- [x] Nova JS datoteka `public/js/pvp.js` (matchmaking polling svakih 3s)

### 1.4 Rewards & Season (1 dan) ✅ ZAVRŠENO
- [ ] Sezonske nagrade na kraju sezone (cron job)
- [x] Rank icons (Bronze → Diamond) sa bojama - RankHelper created
- [x] PvP rewards: 50–300 gold + 10–50 gems + rank points

---

## 📈 PHASE 2 – Economy & Retention (10–14 dana)

### 2.1 Shop ✅ ZAVRŠENO
- [x] Nova tabela `shop_items` + `user_purchases`
- [x] `ShopController` + `ShopService`
- [x] Stranica `/shop` sa 4 paketa (Small/Medium/Large + Special Offer)
- [x] "Buy with Gems" + "Buy with Real Money" placeholder (kasnije Stripe/PayPal)

### 2.2 Battle Pass ✅ ZAVRŠENO
- [x] Tabele: `battle_passes`, `battle_pass_levels`, `user_battle_pass`
- [x] 50 levela (Free + Premium track)
- [ ] Daily/Weekly quests koji daju Battle Pass XP
- [x] Stranica `/battle-pass`

### 2.3 Quests ✅ ZAVRŠENO
- [x] Tabele: `quests`, `user_quests`
- [x] 8 daily + 7 weekly questova (seeded)
- [x] Quest tipi: battles, battles_won, missions_completed, champion_upgrades, equipment_changes...
- [x] QuestController + QuestService

### 2.4 Poboljšani Daily Login ✅ ZAVRŠENO
- [x] 7-dnevni streak sa rastućim nagradama (dan 7 = rare champion shard)

---

## 👥 PHASE 3 – Social Features (nakon PvP-a, 10–12 dana) ✅ ZAVRŠENO

- [x] **Friends sistem**
  - Tabela `friends` (user_id, friend_id, status)
  - `FriendsController` (add, accept, list, remove)
- [x] **Guilds / Clans** (osnovna verzija)
  - Tabele: `guilds`, `guild_members`, `guild_logs`
  - Kreiranje guilda (50 gems), invite, kick
- [x] **Globalni + Friends + Guild Leaderboards**

---

## ⚔️ PHASE 4 – Champion Progression (paralelno, 7–10 dana)

### Equipment ✅ ZAVRŠENO
- [x] Tabele: `equipment`, `user_equipment`
- [x] 8 slotova po championu (Weapon, Armor, Accessory, Helmet, Chest, Gloves, Boots, Ring, Amulet)

### Star System ✅ ZAVRŠENO
- [x] Star System (1–5 zvjezdica) - user_champions.stars column
- [x] Fusion (spajanje 2 ista championa → +1 star) - FusionService + champion-fusion view
- [x] Champion detalji stranica sa "Upgrade", "Fusion" i "Equip" buttonima
- [x] Star bonuses: 0%, 10%, 25%, 45%, 70% stat multiplier

---

## 🛠️ PHASE 5 – Technical Polish & Readiness for Launch (5–7 dana)

**Performance**
- [x] Redis cache za: user resources, rankings, active matches (infra spreman)
- [ ] Queue za emailove (PHPMailer + Redis queue)

**Frontend**
- [ ] Prebaci sve view-ove na Tailwind CSS + Alpine.js
- [x] Dodaj toast notifikacije (success/error)
- [x] Battle damage numbers sa animacijama
- [ ] PWA manifest + service worker

**Admin**
- [x] Admin može kreirati limited-time events
- [x] Bulk user rewards
- [x] PvP match history pregled
- [x] Analytics dashboard
- [x] System logs viewer
- [x] Season management

**Ostalo**
- [x] Tutorial za nove igrače (3–4 koraka) - TutorialController + 4-step tutorial
- [x] Error logging (Logger service)
- [x] Analytics events (page_view, battle_start, battle_end...)
- [x] Season rewards cron

---

## 🎯 PRIORITETI ZA PLAYTEST (100+ igrača)

### 1. Quests ✅ ZAVRŠENO
Daily/weekly zadaci drže igrače aktivnima i daju im razlog da se vrate svaki dan.

### 2. Matchmaking queue ✅ ZAVRŠENO
Matchmaking queue implementirana sa ArenaQueue modelom i automatskim matchmakingom.

### 3. Tutorial ✅ ZAVRŠENO
Novi igrači imaju 4-step tutorial sa rewardima.

### 4. Rank icons ✅ ZAVRŠENO
RankHelper sa 7 rankova (Bronze → Grandmaster) i vizualnim badgevima.

### 5. Ostalo ❌
- Season rewards cron
- Redis cache
- PWA manifest
- Analytics

---

## 📊 UKUPAN PROGRESS

| Kategorija | Završeno | Ukupno | Postotak |
|------------|----------|--------|----------|
| Phase 1 - PvP | 12/12 | 12 | 100% |
| Phase 2 - Economy | 9/9 | 9 | 100% |
| Phase 3 - Social | 6/6 | 6 | 100% |
| Phase 4 - Champions | 5/5 | 5 | 100% |
| Phase 5 - Polish | 11/14 | 14 | 79% |

**Ukupno: 43/46 zadatka (93%)** 🎯
