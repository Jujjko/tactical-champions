<?php
declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;

class AscensionServiceTest extends TestCase {
    public function testServiceExists(): void {
        $this->assertTrue(class_exists(\App\Services\AscensionService::class));
    }
    
    public function testTierOrderIsDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $this->assertTrue($reflection->hasConstant('TIER_ORDER'));
        
        $tiers = $reflection->getConstant('TIER_ORDER');
        $this->assertEquals(['white', 'blue', 'red', 'gold'], $tiers);
    }
    
    public function testMaxStarsIsFive(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $this->assertTrue($reflection->hasConstant('MAX_STARS'));
        
        $max = $reflection->getConstant('MAX_STARS');
        $this->assertEquals(5, $max);
    }
    
    public function testShardRequirementsAreDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $this->assertTrue($reflection->hasConstant('SHARD_REQUIREMENTS'));
        
        $req = $reflection->getConstant('SHARD_REQUIREMENTS');
        
        $this->assertArrayHasKey('common', $req);
        $this->assertArrayHasKey('rare', $req);
        $this->assertArrayHasKey('epic', $req);
        $this->assertArrayHasKey('legendary', $req);
        $this->assertArrayHasKey('mythic', $req);
    }
    
    public function testTierMultipliersAreDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $this->assertTrue($reflection->hasConstant('TIER_MULTIPLIERS'));
        
        $multipliers = $reflection->getConstant('TIER_MULTIPLIERS');
        
        $this->assertEquals(1.0, $multipliers['white']);
        $this->assertEquals(1.5, $multipliers['blue']);
        $this->assertEquals(2.2, $multipliers['red']);
        $this->assertEquals(3.0, $multipliers['gold']);
    }
    
    public function testTierUpgradeCostIsDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $this->assertTrue($reflection->hasConstant('TIER_UPGRADE_COST'));
        
        $costs = $reflection->getConstant('TIER_UPGRADE_COST');
        
        $this->assertArrayHasKey('white', $costs);
        $this->assertArrayHasKey('blue', $costs);
        $this->assertArrayHasKey('red', $costs);
    }
    
    public function testWhiteToBlueRequires100Shards(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $costs = $reflection->getConstant('TIER_UPGRADE_COST');
        
        $this->assertEquals(100, $costs['white']['blue']);
    }
    
    public function testBlueToRedRequires200Shards(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $costs = $reflection->getConstant('TIER_UPGRADE_COST');
        
        $this->assertEquals(200, $costs['blue']['red']);
    }
    
    public function testRedToGoldRequires350Shards(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $costs = $reflection->getConstant('TIER_UPGRADE_COST');
        
        $this->assertEquals(350, $costs['red']['gold']);
    }
    
    public function testMythicRequiresMostShards(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $req = $reflection->getConstant('SHARD_REQUIREMENTS');
        
        $mythicTotal = array_sum($req['mythic']);
        $commonTotal = array_sum($req['common']);
        
        $this->assertGreaterThan($commonTotal, $mythicTotal);
    }
    
    public function testShardRequirementsIncreaseWithStars(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $req = $reflection->getConstant('SHARD_REQUIREMENTS');
        
        foreach ($req as $tier => $costs) {
            $prev = 0;
            foreach ($costs as $stars => $cost) {
                if ($stars > 1 && $stars < 5) {
                    $this->assertGreaterThan($prev, $cost, "Cost should increase for {$tier} at {$stars} stars");
                }
                $prev = $cost;
            }
        }
    }
    
    public function testTotalLevelsForWhite1StarIs1(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('getTotalLevel');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($service, ['star_tier' => 'white', 'stars' => 1]);
        
        $this->assertEquals(1, $result);
    }
    
    public function testTotalLevelsForBlue5StarIs10(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('getTotalLevel');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($service, ['star_tier' => 'blue', 'stars' => 5]);
        
        $this->assertEquals(10, $result);
    }
    
    public function testTotalLevelsForRed3StarIs13(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('getTotalLevel');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($service, ['star_tier' => 'red', 'stars' => 3]);
        
        $this->assertEquals(13, $result);
    }
    
    public function testTotalLevelsForGold5StarIs20(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('getTotalLevel');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($service, ['star_tier' => 'gold', 'stars' => 5]);
        
        $this->assertEquals(20, $result);
    }
    
    public function testGetNextTierReturnsCorrectTier(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('getNextTier');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        
        $this->assertEquals('blue', $method->invoke($service, 'white'));
        $this->assertEquals('red', $method->invoke($service, 'blue'));
        $this->assertEquals('gold', $method->invoke($service, 'red'));
        $this->assertNull($method->invoke($service, 'gold'));
    }
    
    public function testIsMaxedReturnsFalseForNonMaxed(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('isMaxed');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        
        $this->assertFalse($method->invoke($service, ['star_tier' => 'white', 'stars' => 3]));
        $this->assertFalse($method->invoke($service, ['star_tier' => 'blue', 'stars' => 5]));
        $this->assertFalse($method->invoke($service, ['star_tier' => 'red', 'stars' => 2]));
        $this->assertFalse($method->invoke($service, ['star_tier' => 'white', 'stars' => 5]));
        $this->assertFalse($method->invoke($service, ['star_tier' => 'blue', 'stars' => 5]));
        $this->assertFalse($method->invoke($service, ['star_tier' => 'gold', 'stars' => 4]));
    }
    
    public function testIsMaxedReturnsTrueForGold5Stars(): void {
        $reflection = new \ReflectionClass(\App\Services\AscensionService::class);
        $method = $reflection->getMethod('isMaxed');
        $method->setAccessible(true);
        
        $service = $reflection->newInstanceWithoutConstructor();
        
        $this->assertTrue($method->invoke($service, ['star_tier' => 'gold', 'stars' => 5]));
    }
}
