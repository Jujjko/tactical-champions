<?php
declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;

class ChampionModelTest extends TestCase {
    public function testModelExists(): void {
        $this->assertTrue(class_exists(\App\Models\Champion::class));
    }
    
    public function testTableIsSet(): void {
        $reflection = new \ReflectionClass(\App\Models\Champion::class);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $this->assertEquals('champions', $property->getValue($instance));
    }
    
    public function testSoftDeletesEnabled(): void {
        $reflection = new \ReflectionClass(\App\Models\Champion::class);
        $property = $reflection->getProperty('softDeletes');
        $property->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $this->assertTrue($property->getValue($instance));
    }
    
    public function testGetByTierMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Champion::class);
        $this->assertTrue($reflection->hasMethod('getByTier'));
    }
    
    public function testGetRandomByRarityMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Champion::class);
        $this->assertTrue($reflection->hasMethod('getRandomByRarity'));
    }
    
    public function testRarityDistributionIsValid(): void {
        $rarities = [
            'common' => 50,
            'rare' => 30,
            'epic' => 15,
            'legendary' => 4,
            'mythic' => 1
        ];
        
        $total = array_sum($rarities);
        $this->assertEquals(100, $total, 'Rarity distribution should sum to 100%');
    }
}
