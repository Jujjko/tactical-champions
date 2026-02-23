<?php
declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;

class ResourceModelTest extends TestCase {
    public function testModelExists(): void {
        $this->assertTrue(class_exists(\App\Models\Resource::class));
    }
    
    public function testTableIsSet(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        
        $instance = $reflection->newInstanceWithoutConstructor();
        $this->assertEquals('user_resources', $property->getValue($instance));
    }
    
    public function testDeductGoldMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        $this->assertTrue($reflection->hasMethod('deductGold'));
    }
    
    public function testDeductGemsMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        $this->assertTrue($reflection->hasMethod('deductGems'));
    }
    
    public function testAddGoldMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        $this->assertTrue($reflection->hasMethod('addGold'));
    }
    
    public function testAddGemsMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        $this->assertTrue($reflection->hasMethod('addGems'));
    }
    
    public function testDeductGoldIsAtomic(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Resource.php');
        $this->assertStringContainsString('gold >= ?', $content, 'deductGold should use atomic check');
    }
    
    public function testDeductGemsIsAtomic(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Resource.php');
        $this->assertStringContainsString('gems >= ?', $content, 'deductGems should use atomic check');
    }
    
    public function testDeductMethodsReturnBool(): void {
        $reflection = new \ReflectionClass(\App\Models\Resource::class);
        
        $deductGold = $reflection->getMethod('deductGold');
        $this->assertEquals('bool', $deductGold->getReturnType()->getName());
        
        $deductGems = $reflection->getMethod('deductGems');
        $this->assertEquals('bool', $deductGems->getReturnType()->getName());
    }
}
