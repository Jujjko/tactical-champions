<?php
declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;

class ChampionShardModelTest extends TestCase {
    public function testModelExists(): void {
        $this->assertTrue(class_exists(\App\Models\ChampionShard::class));
    }
    
    public function testTableIsSet(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $this->assertEquals('champion_shards', $property->getValue($instance));
    }
    
    public function testExtendsBaseModel(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $this->assertTrue($reflection->isSubclassOf(\Core\Model::class));
    }
    
    public function testGetAmountMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $this->assertTrue($reflection->hasMethod('getAmount'));
    }
    
    public function testAddShardsMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $this->assertTrue($reflection->hasMethod('addShards'));
    }
    
    public function testRemoveShardsMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $this->assertTrue($reflection->hasMethod('removeShards'));
    }
    
    public function testGetAllForUserMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $this->assertTrue($reflection->hasMethod('getAllForUser'));
    }
    
    public function testAddShardsReturnsBool(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $method = $reflection->getMethod('addShards');
        
        $this->assertEquals('bool', $method->getReturnType()->getName());
    }
    
    public function testRemoveShardsReturnsBool(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $method = $reflection->getMethod('removeShards');
        
        $this->assertEquals('bool', $method->getReturnType()->getName());
    }
    
    public function testGetAmountReturnsInt(): void {
        $reflection = new \ReflectionClass(\App\Models\ChampionShard::class);
        $method = $reflection->getMethod('getAmount');
        
        $this->assertEquals('int', $method->getReturnType()->getName());
    }
    
    public function testRemoveShardsUsesAtomicCheck(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/ChampionShard.php');
        
        $this->assertStringContainsString('amount >= ?', $content, 'removeShards should use atomic check');
    }
}
