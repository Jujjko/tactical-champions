# Tactical Champions

A tactical turn-based browser game built with PHP 8.4, featuring champion battles, mission progression, and PvP arena combat.

## Features

- **Battle System**: Turn-based tactical combat with AI opponents
- **Champion Collection**: 5 rarity tiers (Common, Rare, Epic, Legendary, Mythic)
- **Mission Progression**: Difficulty-based missions with rewards
- **PvP Arena**: Real-time player vs player battles with seasons
- **Equipment System**: Gear your champions for stat boosts
- **Energy System**: Regenerating energy for gameplay balance
- **Lootboxes**: Random rewards based on rarity chances
- **Quest System**: Daily and weekly challenges
- **Tutorial System**: Onboarding for new players

## Requirements

- PHP 8.4+
- MySQL 8.0+ / MariaDB 10.5+
- Redis (optional, for caching and battle state)
- Composer

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/tactical-champions.git
cd tactical-champions
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
DB_HOST=localhost
DB_NAME=tactical_champions
DB_USER=your_username
DB_PASS=your_password

# Generate a secure encryption key
ENCRYPTION_KEY=your-32-character-encryption-key-here

# Enable Redis for better performance
REDIS_ENABLED=true
REDIS_HOST=127.0.0.1
```

Generate encryption key:
```bash
php -r "echo bin2hex(random_bytes(16));"
```

### 4. Database Setup

Create the database:
```bash
mysql -u root -p -e "CREATE DATABASE tactical_champions CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Run migrations:
```bash
php database/migrate.php
```

Or import the schema directly:
```bash
mysql -u root -p tactical_champions < database/schema.sql
```

### 5. Web Server Configuration

#### Apache

Point your virtual host to the `public/` directory:

```apache
<VirtualHost *:80>
    ServerName tactical-champions.test
    DocumentRoot /path/to/tactical-champions/public
    
    <Directory /path/to/tactical-champions/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name tactical-champions.test;
    root /path/to/tactical-champions/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### PHP Built-in Server (Development)

```bash
php -S localhost:8000 -t public
```

### 6. Create Admin User

Register through the application, then manually set `is_admin = 1` in the database:

```sql
UPDATE users SET is_admin = 1 WHERE username = 'your_username';
```

## Configuration

### Game Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `ENERGY_REGEN_RATE` | Energy gained per interval | 10 |
| `ENERGY_REGEN_INTERVAL` | Seconds between regen | 600 |
| `MAX_ENERGY` | Maximum energy capacity | 100 |
| `STARTING_GOLD` | New player gold | 100 |
| `BATTLE_TIMEOUT` | Battle state expiry (seconds) | 300 |

### Rarity Drop Rates

| Rarity | Default Rate |
|--------|-------------|
| Common | 50% |
| Rare | 30% |
| Epic | 15% |
| Legendary | 4% |
| Mythic | 1% |

### Feature Flags

```env
FEATURE_PVP_ENABLED=true
FEATURE_TRADING_ENABLED=false
FEATURE_GUILDS_ENABLED=false
FEATURE_CHAT_ENABLED=false
```

## Project Structure

```
tactical-champions/
├── app/
│   ├── Controllers/     # Request handlers
│   ├── Models/          # Database models
│   ├── Services/        # Business logic
│   ├── Core/            # Framework core
│   ├── Middleware/      # HTTP middleware
│   └── Providers/       # Service providers
├── config/
│   └── routes.php       # Route definitions
├── database/
│   ├── migrations/      # SQL migrations
│   ├── schema.sql       # Full schema
│   └── migrate.php      # Migration runner
├── public/
│   ├── index.php        # Entry point
│   ├── js/              # Frontend JavaScript
│   └── css/             # Stylesheets
├── views/               # PHP templates
├── .env.example         # Environment template
└── composer.json
```

## Architecture

### MVC Pattern

- **Controllers**: Thin controllers handle HTTP requests/responses
- **Models**: Eloquent-style models with soft deletes
- **Services**: Business logic (BattleEngine, CacheService, etc.)

### Key Services

| Service | Purpose |
|---------|---------|
| `BattleEngine` | Combat logic and AI |
| `BattleStateManager` | Redis/DB battle state |
| `CacheService` | Redis caching layer |
| `RateLimiter` | Request throttling |
| `SeasonService` | PvP season management |

### Security Features

- CSRF protection on all forms
- Rate limiting on auth endpoints
- Prepared statements (SQL injection prevention)
- XSS protection via htmlspecialchars
- CSP headers
- Secure session configuration

## Testing

```bash
# Run all tests
composer test

# Or directly
./vendor/bin/phpunit
```

## Maintenance Mode

Enable maintenance mode:
```env
MAINTENANCE_MODE=true
MAINTENANCE_MESSAGE="Under maintenance, back soon!"
```

Admins can still access the application during maintenance.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home page |
| GET | `/login` | Login page |
| POST | `/login` | Authenticate |
| GET | `/register` | Registration page |
| POST | `/register` | Create account |
| GET | `/dashboard` | Player dashboard |
| GET | `/battle` | Battle arena |
| POST | `/battle/start` | Start battle |
| POST | `/battle/action` | Execute action |
| GET | `/champions` | Champion roster |
| GET | `/missions` | Available missions |
| GET | `/pvp` | PvP arena |

## Performance Optimization

1. **Enable Redis**: Set `REDIS_ENABLED=true` for caching and battle state
2. **Cache TTL**: Adjust `CACHE_TTL` based on your needs
3. **OPcache**: Enable OPcache in production

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## License

MIT License - see [LICENSE](LICENSE) for details.
