## 🔥 KRITIČNI ZADACI (MORA SE POPRAVITI PRIJE PLAYTESTA)

### Dan 1 – Bugovi & Sigurnost (4–5h) ✅ ZAVRŠENO
- [x] Popraviti `sendResetLink()` – email logika nakon `redirect()` ✅
- [x] Ukloniti `serialize($battleEngine)` iz sessiona → koristi array state ✅
- [x] Dodati CSRF provjeru u **sve** POST/PUT akcije ✅
  - Admin: `toggleUser`, `createChampion`, `saveMission`, `toggleMission`
  - Lootbox: `open`
  - Battle: `start`, `action`, `forfeit`
- [x] Dodati security headers u `.htaccess` (CSP, X-Frame-Options, Permissions-Policy) ✅
- [x] Staviti rate limiting na `/register` ✅
- [x] Dodati error stranice (400, 401, 403, 404, 500) ✅
- [x] Dodati return nakon svih redirect() poziva ✅

### Dan 2 – BaseController (3–4h) ✅ ZAVRŠENO
- [x] Dodati helper metode u `Core\Controller.php` ✅
  - `backWithError()`, `backWithSuccess()`, `back()` ✅
  - `jsonSuccess()`, `jsonError()` ✅
  - `validateCsrf()`, `requireCsrf()` ✅
  - `redirectWithSuccess()`, `redirectWithError()` ✅
  - `validate()` ✅
- [x] Refaktorirati `Session::flash + redirect` pozive u AuthController ✅
- [x] Refaktorirati `Session::flash + redirect` pozive u AdminController ✅
- [x] Refaktorirati `Session::flash + redirect` pozive u BattleController ✅
- [x] Preostali controlleri (MissionController, GameController, ChampionController, LootboxController) ✅

### Dan 3 – Čišćenje Controller-a (5h) ✅ ZAVRŠENO
- [x] `BattleController` → izdvojiti logiku u `App\Services\BattleService` ✅ (~320→90 linija)
- [x] Obriši stari `MissionController::start()` legacy endpoint ✅
- [x] `AuthController` → refaktoriran sa helper metodama ✅
- [x] `AdminController` → refaktoriran sa helper metodama + pagination ✅

### Dan 4 – Dependency Injection (4h) ✅ ZAVRŠENO
- [x] Kreirati `Config` klasu (`Core\Config.php`) ✅
- [x] Kreirati vlastiti `Core\Container.php` (bez php-di) ✅
- [x] Kreirati `App\Providers\AppServiceProvider` ✅
- [x] Inicijalizacija containera u `index.php` ✅

### Dan 5 – Battle State (ključni dan) (5–6h) ✅ ZAVRŠENO
- [x] Instalirati `predis/predis` ✅
- [x] Kreirati `App\Services\BattleStateManager` (Redis + DB fallback) ✅
- [x] Zamijeniti session battle state sa novim sustavom ✅
- [x] Kreirati `battle_states` tablicu ✅
- [x] Ažurirati BattleService i BattleController ✅

### Dan 6 – Database & Migrations (4–5h) ✅ ZAVRŠENO
- [x] Kreirati `database/migrations/` folder ✅
- [x] Napisati `2025_02_18_000001_create_initial_tables.sql` ✅
- [x] Dodati `deleted_at` (soft deletes) svim modelima ✅
- [x] Kreirati `AuditLog` model za admin akcije ✅
- [x] Napraviti jednostavan migrator (`database/migrate.php`) ✅

### Dan 7 – Polishing + Prve nove feature (5h) ✅ ZAVRŠENO
- [x] Pagination na `/admin/users`, `/admin/champions`, `/admin/missions` ✅
- [x] Dodano `Model::paginate()` i `Model::count()` ✅
- [x] Stranica detalja šampiona `/champions/{id}` ✅
- [x] "Battle History" na dashboardu (posljednjih 5 bitaka) ✅
- [x] Daily login rewards + streak counter ✅

---

## 📋 SHORT-TERM (sljedeća 2 tjedna)

### Sigurnost & Performanse ✅ ZAVRŠENO
- [x] Integrirati AuditLog za sve admin akcije ✅
  - Kreiran `AuditService` wrapper
  - Logiranje: create, update, delete, toggle, login, logout
  - Admin dashboard s recent logs
- [x] Zamijeniti `mail()` sa PHPMailer ✅
  - Kreiran `MailService` s HTML template-ima
  - Password reset, welcome, verification email-ovi
  - Fallback na log kada je MAIL_ENABLED=false
- [x] Prebaciti rate limiter na Redis ✅
  - Kreiran `RedisRateLimiter` 
  - Redis primarno, file fallback
  - Isti API kao stari RateLimiter

### Core Game Loop ✅ ZAVRŠENO
- [x] Champion upgrade / level up ekran ✅
  - `ChampionService` - upgrade logic, XP calculation, gold cost
  - `ChampionController::upgrade()`, `doUpgrade()` 
  - `champion-upgrade.php` view
- [x] Equipment sistem (oružje, oklop) ✅
  - `Equipment` model + `UserEquipment` model
  - `EquipmentController` (index, show, equip, unequip)
  - Migration: `2025_02_19_000001_create_equipment_tables.sql`
  - Views: `equipment.php`, `equipment-detail.php`
  - Admin CRUD: `admin/equipment.php`
  - Equipment affects champion stats in detail view
- [x] My Battle History stranica ✅
  - `battle-history.php` view
  - Route: `GET /battle-history`
  - Stats summary (total battles, victories, win rate)

### Admin Panel ✅ ZAVRŠENO
- [x] Champion edit/delete ✅
  - `AdminController::getChampion()`, `updateChampion()`, `deleteChampion()`
  - Modal edit form in `admin/champions.php`
  - Soft delete with audit log
- [x] Mission edit/delete ✅
  - `AdminController::deleteMission()`
  - Delete button in `admin/missions.php`
  - Soft delete with audit log
- [x] Globalni leaderboard (admin view) ✅
  - `AdminController::leaderboard()`
  - `admin/leaderboard.php` view
  - Player rankings with stats (level, champions, battles, win rate)

---

## 🚀 MEDIUM-TERM (1–2 mjeseca) ✅ ZAVRŠENO

- [x] PvP Arena (izazovi + queue) ✅
  - `PvpRating`, `PvpChallenge` models
  - `ArenaController` with challenge/accept/decline
  - Views: `arena.php`, `arena-leaderboard.php`
  - Rating system with wins/losses tracking
- [x] Guilds / Clans ✅
  - `Guild`, `GuildMember` models
  - `GuildController` with create/join/leave
  - Views: `guilds.php`, `guild-detail.php`
  - Roles, treasury, member management
- [x] Achievement sistem ✅
  - `Achievement`, `UserAchievement` models
  - `AchievementController` with progress tracking
  - View: `achievements.php`
  - Categories: battle, champion, social, progression, special
- [x] In-game shop (gems paketi) ✅
  - `ShopItem`, `UserPurchase` models
  - `ShopController` with purchase system
  - View: `shop.php`
  - Categories: gems, gold, energy, special
- [x] Battle Pass ✅
  - `BattlePassSeason`, `UserBattlePass` models
  - `BattlePassController` with XP/level progression
  - View: `battle-pass.php`
  - Free and premium reward tracks
- [x] Referral sistem ✅
  - `Referral` model with code generation
  - `ReferralController` with use/claim
  - View: `referrals.php`
  - Tiered rewards based on referred player progress
- [x] Leaderboard (global + friends) ✅
  - `Friend` model with request system
  - `FriendController` with add/accept/remove
  - View: `friends.php`
  - Arena leaderboard for PvP rankings

---

## 🔮 LONG-TERM (3+ mjeseca)

- [ ] WebSocket real-time battle (Ratchet ili Laravel Echo stil)
- [ ] Mobile-friendly PWA
- [ ] Analytics (Google Analytics + custom event tracking)
- [ ] Monetizacija (ads + in-app purchases)

---

## ✅ ZAVRŠENO (Updated 2025-02-20)

### Core Framework
- [x] Osnovna MVC struktura
- [x] `Core\Config` klasa
- [x] `Core\Container` - vlastiti DI container
- [x] `Core\Validator` sa `unique:` i `exists:` rules
- [x] `Core\Model::paginate()`, `count()`, `where()`, soft deletes
- [x] Helper metode u `Core\Controller`
- [x] `App\Providers\AppServiceProvider`
- [x] `App\Helpers\RankHelper` - PvP rank system (Bronze→Grandmaster)

### Database & Migrations
- [x] `database/migrate.php` - jednostavan migrator
- [x] `database/migrations/2025_02_18_000001_create_initial_tables.sql`
- [x] Soft deletes na svim modelima (User, Champion, Mission, UserChampion, Battle)
- [x] `AuditLog` model

### Sigurnost
- [x] CSRF zaštita na svim POST endpointima
- [x] Security headers (CSP, X-Frame-Options, Permissions-Policy)
- [x] Rate limiting na login i register
- [x] Session ID regeneracija nakon login
- [x] `.env` u `.gitignore`
- [x] Error stranice (400, 401, 403, 404, 500)

### Bugfixevi
- [x] `sendResetLink()` - email se šalje prije redirecta
- [x] BattleEngine - sprema se kao array, ne kao serialized object
- [x] Champion level-up - ispravno računanje statova
- [x] Atomic energy update - nema race condition
- [x] Return statements nakon svih redirect() poziva

### Refaktoring
- [x] `BattleService` izdvojen iz BattleController
- [x] `BattleStateManager` - Redis/DB/session fallback sustav
- [x] `RateLimiter` servis izdvojen
- [x] `RedisRateLimiter` - Redis s file fallback
- [x] `DailyLoginService` - daily rewards i streak counter
- [x] `AuditService` - wrapper za audit logiranje
- [x] `MailService` - PHPMailer s HTML template-ima
- [x] `ChampionService` - champion upgrade logic
- [x] Uklonjen dupli CSS kod iz AdminController (~600 linija)
- [x] Uklonjen dupli HTML kod iz battle-prepare.php (~340 linija)
- [x] Legacy `MissionController::start()` uklonjen

### Game Features
- [x] Napredni BattleEngine (AI + specijalne sposobnosti)
- [x] Auth + Password Reset (forgot-password, reset-password)
- [x] Admin panel (sa pagination + audit log viewer)
- [x] Lootbox + Champion sistem
- [x] Energy regeneration
- [x] Champion detail page (`/champions/{id}`)
- [x] Battle history na dashboardu (posljednjih 5)
- [x] Rate limiting servis (Redis + file fallback)
- [x] Daily login rewards + streak counter (7-day cycle)
- [x] Audit logging (admin akcije, login/logout)
- [x] PHPMailer email servis
- [x] Champion upgrade / level up sistem
- [x] Equipment sistem (weapons, armor, accessories)
- [x] Full Battle History stranica
- [x] Admin Equipment Management (CRUD)
- [x] Admin Champion Management (edit/delete)
- [x] Admin Mission Management (edit/delete)
- [x] Admin Global Leaderboard
- [x] PvP Arena with challenges and ratings
- [x] Matchmaking queue (auto-matchmaking sa rating range)
- [x] Guilds/Clans system
- [x] Achievement system
- [x] In-game Shop
- [x] Battle Pass seasons
- [x] Referral system
- [x] Friends system
- [x] Quests system (daily/weekly with rewards)
- [x] Tutorial system (4-step onboarding)
- [x] Rank icons (Bronze→Grandmaster with visual badges)
- [x] Star System (1-5 stars with stat bonuses)
- [x] Fusion System (merge identical champions)
- [x] Season Rewards Cron (automatic end-of-season rewards)
- [x] Error Logging (Logger service with file rotation)
- [x] Analytics Events (page_view, battle_start, battle_end, etc.)
- [x] Battle Damage Animations (floating numbers, screen shake, particles)
- [x] Admin PvP Match History
- [x] Admin Bulk User Rewards
- [x] Admin Analytics Dashboard
- [x] Admin System Logs Viewer
- [x] Admin Season Management

### Nove datoteke
- `app/Core/Container.php`
- `app/Core/Config.php`
- `app/Services/BattleService.php`
- `app/Services/BattleStateManager.php`
- `app/Services/RateLimiter.php`
- `app/Services/RedisRateLimiter.php`
- `app/Services/DailyLoginService.php`
- `app/Services/AuditService.php`
- `app/Services/MailService.php`
- `app/Services/ChampionService.php`
- `app/Models/AuditLog.php`
- `app/Models/PasswordResetToken.php`
- `app/Models/Equipment.php`
- `app/Models/UserEquipment.php`
- `app/Models/PvpRating.php`
- `app/Models/PvpChallenge.php`
- `app/Models/Guild.php`
- `app/Models/GuildMember.php`
- `app/Models/Achievement.php`
- `app/Models/UserAchievement.php`
- `app/Models/ShopItem.php`
- `app/Models/UserPurchase.php`
- `app/Models/BattlePassSeason.php`
- `app/Models/UserBattlePass.php`
- `app/Models/Referral.php`
- `app/Models/Friend.php`
- `app/Models/Quest.php`
- `app/Models/UserQuest.php`
- `app/Models/ArenaQueue.php`
- `app/Models/Tutorial.php`
- `app/Controllers/EquipmentController.php`
- `app/Controllers/ArenaController.php`
- `app/Controllers/GuildController.php`
- `app/Controllers/AchievementController.php`
- `app/Controllers/ShopController.php`
- `app/Controllers/BattlePassController.php`
- `app/Controllers/ReferralController.php`
- `app/Controllers/FriendController.php`
- `app/Controllers/QuestController.php`
- `app/Controllers/TutorialController.php`
- `app/Controllers/SeasonController.php`
- `app/Services/QuestService.php`
- `app/Services/FusionService.php`
- `app/Services/SeasonService.php`
- `app/Services/AnalyticsService.php`
- `app/Services/Logger.php`
- `app/Helpers/RankHelper.php`
- `app/Models/PvpBattle.php`
- `database/migrate.php`
- `database/migrations/2025_02_18_000001_create_initial_tables.sql`
- `database/migrations/2025_02_18_000002_create_battle_states_table.sql`
- `database/migrations/2025_02_18_000003_add_daily_login_rewards.sql`
- `database/migrations/2025_02_19_000001_create_equipment_tables.sql`
- `database/migrations/2025_02_19_000002_create_medium_term_features.sql`
- `database/migrations/2025_02_19_000003_create_quests_tables.sql`
- `database/migrations/2025_02_20_000001_create_tutorials_table.sql`
- `database/migrations/2025_02_20_000002_add_star_system.sql`
- `database/migrations/2025_02_21_000001_add_season_analytics_tables.sql`
- `public/errors/400.php` - `500.php`
- `app/Views/auth/forgot-password.php`
- `app/Views/auth/reset-password.php`
- `app/Views/game/champion-detail.php`
- `app/Views/game/champion-upgrade.php`
- `app/Views/game/battle-history.php`
- `app/Views/game/equipment.php`
- `app/Views/game/equipment-detail.php`
- `app/Views/game/arena.php`
- `app/Views/game/arena-leaderboard.php`
- `app/Views/game/guilds.php`
- `app/Views/game/guild-detail.php`
- `app/Views/game/achievements.php`
- `app/Views/game/shop.php`
- `app/Views/game/battle-pass.php`
- `app/Views/game/referrals.php`
- `app/Views/game/friends.php`
- `app/Views/game/quests.php`
- `app/Views/game/tutorial.php`
- `app/Views/game/champion-fusion.php`
- `app/Views/game/season.php`
- `app/Views/admin/leaderboard.php`
- `app/Views/admin/pvp-history.php`
- `app/Views/admin/bulk-rewards.php`
- `app/Views/admin/analytics.php`
- `app/Views/admin/logs.php`
- `app/Views/admin/seasons.php`
- `cron/season-rewards.php`
- `cron/daily-cleanup.php`
- `app/Views/admin/equipment.php`
- `.gitignore`

---

## 📊 STATISTIKA

| Kategorija | Završeno | Preostalo |
|------------|----------|-----------|
| Dan 1 - Sigurnost | 7/7 | 0 |
| Dan 2 - BaseController | 5/5 | 0 |
| Dan 3 - Controller Cleanup | 4/4 | 0 |
| Dan 4 - DI | 4/4 | 0 |
| Dan 5 - Battle State | 5/5 | 0 |
| Dan 6 - Migrations | 5/5 | 0 |
| Dan 7 - Polishing | 5/5 | 0 |
| Core Game Loop | 3/3 | 0 |
| Admin Panel | 3/3 | 0 |
| Medium-Term | 7/7 | 0 |

**Ukupno kritičnih zadataka završeno: 35/35 (100%)** 🎉
**Ukupno short-term zadataka završeno: 6/6 (100%)** 🎉
**Ukupno medium-term zadataka završeno: 7/7 (100%)** 🎉

---

## 🔮 LONG-TERM (3+ mjeseca)

- [ ] WebSocket real-time battle (Ratchet ili Laravel Echo stil)
- [ ] Mobile-friendly PWA
- [ ] Analytics (Google Analytics + custom event tracking)
- [ ] Monetizacija (ads + in-app purchases)
