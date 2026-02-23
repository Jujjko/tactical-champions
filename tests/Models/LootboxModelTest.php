<?php
declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;

class LootboxModelTest extends TestCase {
    public function testModelExists(): void {
        $this->assertTrue(class_exists(\App\Models\Lootbox::class));
    }
    
    public function testTableIsSet(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $this->assertEquals('user_lootboxes', $property->getValue($instance));
    }
    
    public function testGenerateRewardsMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $this->assertTrue($reflection->hasMethod('generateRewards'));
    }
    
    public function testGenerateRewardsIsPrivate(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        
        $this->assertTrue($method->isPrivate());
    }
    
    public function testGenerateRewardsReturnsArray(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($instance, 'bronze');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('gold', $result);
        $this->assertArrayHasKey('gems', $result);
        $this->assertArrayHasKey('champion', $result);
    }
    
    public function testBronzeLootboxReturnsValidRewards(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($instance, 'bronze');
        
        $this->assertGreaterThanOrEqual(50, $result['gold']);
        $this->assertLessThanOrEqual(150, $result['gold']);
        $this->assertGreaterThanOrEqual(5, $result['gems']);
        $this->assertLessThanOrEqual(20, $result['gems']);
        $this->assertIsBool($result['champion']);
    }
    
    public function testSilverLootboxHasDoubleMultiplier(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        
        $totalBronze = 0;
        $totalSilver = 0;
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $bronze = $method->invoke($instance, 'bronze');
            $silver = $method->invoke($instance, 'silver');
            $totalBronze += $bronze['gold'];
            $totalSilver += $silver['gold'];
        }
        
        $avgBronze = $totalBronze / $iterations;
        $avgSilver = $totalSilver / $iterations;
        
        $this->assertGreaterThan($avgBronze, $avgSilver, 'Silver should have higher average gold than bronze');
    }
    
    public function testGoldLootboxHasTripleMultiplier(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        
        $totalBronze = 0;
        $totalGold = 0;
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $bronze = $method->invoke($instance, 'bronze');
            $gold = $method->invoke($instance, 'gold');
            $totalBronze += $bronze['gold'];
            $totalGold += $gold['gold'];
        }
        
        $avgBronze = $totalBronze / $iterations;
        $avgGold = $totalGold / $iterations;
        
        $this->assertGreaterThan($avgBronze, $avgGold, 'Gold should have higher average gold than bronze');
    }
    
    public function testDiamondLootboxHasHighestMultiplier(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $diamond = $method->invoke($instance, 'diamond');
        
        $this->assertGreaterThanOrEqual(250, $diamond['gold']);
        $this->assertGreaterThanOrEqual(25, $diamond['gems']);
    }
    
    public function testUnknownTypeDefaultsToBronze(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $result = $method->invoke($instance, 'unknown');
        
        $this->assertGreaterThanOrEqual(50, $result['gold']);
        $this->assertLessThanOrEqual(150, $result['gold']);
    }
    
    public function testChampionDropRateIsReasonable(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $method->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $championDrops = 0;
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $result = $method->invoke($instance, 'diamond');
            if ($result['champion']) {
                $championDrops++;
            }
        }
        
        $this->assertGreaterThan(50, $championDrops, 'Diamond lootbox should have ~100% champion drop rate');
    }
}
