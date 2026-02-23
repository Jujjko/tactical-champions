<?php
declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;

class DependencyInjectionTest extends TestCase {
    private array $controllers = [
        'AdminController',
        'ArenaController',
        'AuthController',
        'AchievementController',
        'BattleController',
        'BattlePassController',
        'ChampionController',
        'EquipmentController',
        'FriendController',
        'GameController',
        'GuildController',
        'LeaderboardController',
        'LootboxController',
        'MissionController',
        'PvpController',
        'QuestController',
        'ReferralController',
        'SeasonController',
        'ShopController',
        'TournamentController',
        'TutorialController',
    ];
    
    public function testAllControllersHaveConstructor(): void {
        foreach ($this->controllers as $controllerName) {
            $className = "App\\Controllers\\{$controllerName}";
            $reflection = new \ReflectionClass($className);
            
            $this->assertTrue(
                $reflection->hasMethod('__construct'),
                "{$controllerName} should have a constructor"
            );
        }
    }
    
    public function testAllControllersInjectDependencies(): void {
        foreach ($this->controllers as $controllerName) {
            $className = "App\\Controllers\\{$controllerName}";
            $reflection = new \ReflectionClass($className);
            
            $constructor = $reflection->getConstructor();
            $this->assertNotNull($constructor, "{$controllerName} constructor should not be null");
            
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
            $this->assertGreaterThan(0, count($properties), "{$controllerName} should have private properties for DI");
        }
    }
    
    public function testNoInlineInstantiationInIndexMethods(): void {
        foreach ($this->controllers as $controllerName) {
            $className = "App\\Controllers\\{$controllerName}";
            $reflection = new \ReflectionClass($className);
            $filename = $reflection->getFileName();
            $content = file_get_contents($filename);
            
            if ($reflection->hasMethod('index')) {
                $method = $reflection->getMethod('index');
                $methodContent = $this->getMethodContent($content, 'index');
                
                preg_match_all('/new\s+\\\\?App\\\\(Models|Services)\\\\\w+/', $methodContent, $matches);
                
                $this->assertEmpty(
                    $matches[0],
                    "{$controllerName}::index() should not instantiate dependencies inline"
                );
            }
        }
    }
    
    public function testControllersExtendBaseController(): void {
        foreach ($this->controllers as $controllerName) {
            $className = "App\\Controllers\\{$controllerName}";
            $reflection = new \ReflectionClass($className);
            
            $this->assertTrue(
                $reflection->isSubclassOf('Core\\Controller'),
                "{$controllerName} should extend Core\\Controller"
            );
        }
    }
    
    public function testAdminControllerHasRequiredDependencies(): void {
        $reflection = new \ReflectionClass(\App\Controllers\AdminController::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $propertyNames = array_map(fn($p) => $p->getName(), $properties);
        
        $required = ['userModel', 'championModel', 'missionModel', 'auditService'];
        
        foreach ($required as $dep) {
            $this->assertContains($dep, $propertyNames, "AdminController should have \${$dep}");
        }
    }
    
    public function testShopControllerHasRequiredDependencies(): void {
        $reflection = new \ReflectionClass(\App\Controllers\ShopController::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $propertyNames = array_map(fn($p) => $p->getName(), $properties);
        
        $required = ['shopItemModel', 'userPurchaseModel', 'resourceModel', 'auditService'];
        
        foreach ($required as $dep) {
            $this->assertContains($dep, $propertyNames, "ShopController should have \${$dep}");
        }
    }
    
    public function testAuthControllerHasRequiredDependencies(): void {
        $reflection = new \ReflectionClass(\App\Controllers\AuthController::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $propertyNames = array_map(fn($p) => $p->getName(), $properties);
        
        $required = ['userModel', 'rateLimiter', 'auditService'];
        
        foreach ($required as $dep) {
            $this->assertContains($dep, $propertyNames, "AuthController should have \${$dep}");
        }
    }
    
    public function testPvpControllerHasRequiredDependencies(): void {
        $reflection = new \ReflectionClass(\App\Controllers\PvpController::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $propertyNames = array_map(fn($p) => $p->getName(), $properties);
        
        $required = ['matchmaking', 'pvpService', 'userChampionModel'];
        
        foreach ($required as $dep) {
            $this->assertContains($dep, $propertyNames, "PvpController should have \${$dep}");
        }
    }
    
    private function getMethodContent(string $fileContent, string $methodName): string {
        $pattern = '/public\s+function\s+' . $methodName . '\s*\([^)]*\)\s*:\s*void\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/s';
        preg_match($pattern, $fileContent, $matches);
        return $matches[1] ?? '';
    }
}
