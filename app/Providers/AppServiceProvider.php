<?php
declare(strict_types=1);

namespace App\Providers;

use Core\Container;
use Core\Database;
use Core\Config;
use App\Models\User;
use App\Models\Champion;
use App\Models\UserChampion;
use App\Models\Mission;
use App\Models\Battle;
use App\Models\Resource;
use App\Models\Lootbox;
use App\Models\PasswordResetToken;
use App\Services\RateLimiter;
use App\Services\BattleEngine;
use App\Services\BattleService;

class AppServiceProvider {
    public function register(Container $container): void {
        $this->registerModels($container);
        $this->registerServices($container);
    }
    
    private function registerModels(Container $container): void {
        $container->singleton(User::class, fn() => new User());
        $container->singleton(Champion::class, fn() => new Champion());
        $container->singleton(UserChampion::class, fn() => new UserChampion());
        $container->singleton(Mission::class, fn() => new Mission());
        $container->singleton(Battle::class, fn() => new Battle());
        $container->singleton(Resource::class, fn() => new Resource());
        $container->singleton(Lootbox::class, fn() => new Lootbox());
        $container->singleton(PasswordResetToken::class, fn() => new PasswordResetToken());
    }
    
    private function registerServices(Container $container): void {
        $container->singleton(RateLimiter::class, fn() => new RateLimiter(
            Config::maxLoginAttempts(),
            Config::lockoutTime()
        ));
        
        $container->singleton(BattleEngine::class, fn() => new BattleEngine());
        
        $container->singleton(BattleService::class, function ($c) {
            return new BattleService();
        });
    }
}