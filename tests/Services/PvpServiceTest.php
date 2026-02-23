<?php
declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;

class PvpServiceTest extends TestCase {
    public function testServiceExists(): void {
        $this->assertTrue(class_exists(\App\Services\PvpService::class));
    }
    
    public function testRatingConstantsAreDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        
        $this->assertTrue($reflection->hasConstant('MIN_RATING_GAIN'));
        $this->assertTrue($reflection->hasConstant('MAX_RATING_GAIN'));
        $this->assertTrue($reflection->hasConstant('BASE_RATING_GAIN'));
        $this->assertTrue($reflection->hasConstant('RATING_FLOOR'));
    }
    
    public function testRewardConstantsAreDefined(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        
        $this->assertTrue($reflection->hasConstant('VICTORY_GOLD_MIN'));
        $this->assertTrue($reflection->hasConstant('VICTORY_GOLD_MAX'));
        $this->assertTrue($reflection->hasConstant('VICTORY_GEMS_MIN'));
        $this->assertTrue($reflection->hasConstant('VICTORY_GEMS_MAX'));
        $this->assertTrue($reflection->hasConstant('VICTORY_SHARD_CHANCE'));
    }
    
    public function testCalculateRatingChangeMethodExists(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $this->assertTrue($reflection->hasMethod('calculateRatingChange'));
    }
    
    public function testCalculateRatingChangeReturnsInt(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $method = $reflection->getMethod('calculateRatingChange');
        
        $this->assertEquals('int', $method->getReturnType()->getName());
    }
    
    public function testRatingFloorIsEnforced(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $floor = $reflection->getConstant('RATING_FLOOR');
        
        $this->assertEquals(800, $floor);
    }
    
    public function testVictoryGoldRangeIsValid(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $min = $reflection->getConstant('VICTORY_GOLD_MIN');
        $max = $reflection->getConstant('VICTORY_GOLD_MAX');
        
        $this->assertGreaterThan(0, $min);
        $this->assertGreaterThan($min, $max);
    }
    
    public function testDefeatGoldIsLessThanVictory(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $victoryMin = $reflection->getConstant('VICTORY_GOLD_MIN');
        $defeatMax = $reflection->getConstant('DEFEAT_GOLD_MAX');
        
        $this->assertLessThan($victoryMin, $defeatMax);
    }
    
    public function testMinRatingGainIsPositive(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $min = $reflection->getConstant('MIN_RATING_GAIN');
        
        $this->assertGreaterThan(0, $min);
    }
    
    public function testMaxRatingGainIsGreaterThanMin(): void {
        $reflection = new \ReflectionClass(\App\Services\PvpService::class);
        $min = $reflection->getConstant('MIN_RATING_GAIN');
        $max = $reflection->getConstant('MAX_RATING_GAIN');
        
        $this->assertGreaterThan($min, $max);
    }
}
