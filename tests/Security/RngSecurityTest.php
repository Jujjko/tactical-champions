<?php
declare(strict_types=1);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class RngSecurityTest extends TestCase {
    public function testRandomIntIsUsedInLootbox(): void {
        $reflection = new \ReflectionClass(\App\Models\Lootbox::class);
        $method = $reflection->getMethod('generateRewards');
        $content = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('random_int', $content);
        $this->assertStringNotContainsString('rand(50, 150)', $content);
        $this->assertStringNotContainsString('rand(5, 20)', $content);
    }
    
    public function testRandomIntIsUsedInChampion(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Champion.php');
        
        $this->assertStringContainsString('random_int(1, 100)', $content);
        $this->assertStringNotContainsString('rand(1, 100)', $content);
    }
    
    public function testRandomIntIsUsedInEquipment(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Equipment.php');
        
        $this->assertStringContainsString('random_int(1, 100)', $content);
        $this->assertStringNotContainsString('rand(1, 100)', $content);
    }
    
    public function testRandomIntIsUsedInMission(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Mission.php');
        
        $this->assertStringContainsString('random_int(1, 10000)', $content);
        $this->assertStringNotContainsString('rand(1, 10000)', $content);
    }
    
    public function testRandomIntIsUsedInPvpService(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Services/PvpService.php');
        
        $this->assertStringContainsString('random_int', $content);
    }
    
    public function testRandomIntIsUsedInBattleEngine(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Services/BattleEngine.php');
        
        $this->assertStringContainsString('random_int', $content);
    }
    
    public function testRandomIntIsUsedInTournamentService(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Services/TournamentService.php');
        
        $this->assertStringContainsString('random_int', $content);
    }
    
    public function testRandomIntIsUsedInRewardService(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Services/RewardService.php');
        
        $this->assertStringContainsString('random_int', $content);
    }
    
    public function testNoUnsafeRandInModelsDirectory(): void {
        $files = glob(dirname(__DIR__, 2) . '/app/Models/*.php');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            preg_match_all('/\brand\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE);
            
            foreach ($matches[0] ?? [] as $match) {
                $context = substr($content, max(0, $match[1] - 20), 40);
                if (!str_contains($context, 'array_rand')) {
                    $this->fail("Unsafe rand() found in $file at position {$match[1]}: $context");
                }
            }
        }
        
        $this->assertTrue(true, 'No unsafe rand() calls in Models directory');
    }
    
    public function testNoUnsafeRandInServicesDirectory(): void {
        $files = glob(dirname(__DIR__, 2) . '/app/Services/*.php');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            preg_match_all('/\brand\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE);
            
            foreach ($matches[0] ?? [] as $match) {
                $context = substr($content, max(0, $match[1] - 20), 40);
                if (!str_contains($context, 'array_rand')) {
                    $this->fail("Unsafe rand() found in $file at position {$match[1]}: $context");
                }
            }
        }
        
        $this->assertTrue(true, 'No unsafe rand() calls in Services directory');
    }
    
    public function testRngDistributionIsUniform(): void {
        $results = [];
        for ($i = 0; $i < 1000; $i++) {
            $value = random_int(1, 100);
            $bucket = (int)ceil($value / 10);
            $results[$bucket] = ($results[$bucket] ?? 0) + 1;
        }
        
        foreach ($results as $bucket => $count) {
            $this->assertGreaterThan(50, $count, "Bucket $bucket has too few values");
            $this->assertLessThan(150, $count, "Bucket $bucket has too many values");
        }
    }
    
    public function testRngProducesDifferentValues(): void {
        $values = [];
        for ($i = 0; $i < 100; $i++) {
            $values[] = random_int(1, 10000);
        }
        
        $unique = count(array_unique($values));
        $this->assertGreaterThan(90, $unique, 'RNG should produce mostly unique values');
    }
}
