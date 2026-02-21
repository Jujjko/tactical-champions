<?php
declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\BattleEngine;

class BattleEngineTest extends TestCase {
    private BattleEngine $engine;
    
    protected function setUp(): void {
        $this->engine = new BattleEngine();
    }
    
    public function testInitializeBattleSetsUpTeams(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $state = $this->engine->getState();
        
        $this->assertCount(1, $state['player_team']);
        $this->assertCount(1, $state['enemy_team']);
        $this->assertEquals('Knight', $state['player_team'][0]['name']);
        $this->assertEquals('Goblin', $state['enemy_team'][0]['name']);
    }
    
    public function testChampionStartsWithFullHealth(): void {
        $champions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 150, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        
        $this->engine->initializeBattle($champions, []);
        $state = $this->engine->getState();
        
        $this->assertEquals(150, $state['player_team'][0]['current_health']);
        $this->assertEquals(150, $state['player_team'][0]['max_health']);
    }
    
    public function testExecuteTurnReturnsSuccess(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $result = $this->engine->executeTurn('attack', 1, 2);
        
        $this->assertArrayHasKey('turn', $result);
        $this->assertArrayHasKey('player_team', $result);
        $this->assertArrayHasKey('enemy_team', $result);
        $this->assertArrayHasKey('battle_log', $result);
    }
    
    public function testAttackReducesTargetHealth(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 50, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 100, 'attack' => 10, 'defense' => 0, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2);
        $state = $this->engine->getState();
        
        $this->assertLessThan(100, $state['enemy_team'][0]['current_health']);
    }
    
    public function testDefenseReducesDamage(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 100, 'defense' => 10, 'speed' => 50]
        ];
        $enemyNoDefense = [
            ['id' => 2, 'name' => 'Target', 'health' => 1000, 'attack' => 10, 'defense' => 0, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyNoDefense);
        $this->engine->executeTurn('attack', 1, 2);
        $state = $this->engine->getState();
        $damage = 1000 - $state['enemy_team'][0]['current_health'];
        
        $this->assertGreaterThan(0, $damage);
        $this->assertLessThan(200, $damage);
    }
    
    public function testBattleEndsWhenOneTeamDefeated(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 1000, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 10, 'attack' => 10, 'defense' => 0, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $result = $this->engine->executeTurn('attack', 1, 2);
        
        $this->assertTrue($result['battle_ended']);
        $this->assertEquals('victory', $result['winner']);
    }
    
    public function testDefeatWhenPlayerTeamDefeated(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 10, 'attack' => 10, 'defense' => 0, 'speed' => 10]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Boss', 'health' => 1000, 'attack' => 1000, 'defense' => 100, 'speed' => 50]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $result = $this->engine->executeAITurn();
        
        $this->assertTrue($result['battle_ended']);
        $this->assertEquals('defeat', $result['winner']);
    }
    
    public function testInvalidAttackerReturnsError(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $result = $this->engine->executeTurn('attack', 999, 2);
        
        $this->assertFalse($result['success']);
    }
    
    public function testDeadChampionCannotAct(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $state = $this->engine->getState();
        $state['player_team'][0]['alive'] = false;
        $state['player_team'][0]['current_health'] = 0;
        $this->engine->setState($state);
        
        $result = $this->engine->executeTurn('attack', 1, 2);
        
        $this->assertFalse($result['success']);
    }
    
    public function testSpecialAbilityHasCooldown(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Mage', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50, 'special_ability' => 'Critical Strike']
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 500, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2, true);
        $state = $this->engine->getState();
        
        $this->assertGreaterThan(0, $state['player_team'][0]['ability_cooldown']);
    }
    
    public function testCannotUseAbilityOnCooldown(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Mage', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50, 'special_ability' => 'Critical Strike']
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 500, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2, true);
        $state1 = $this->engine->getState();
        $cooldownAfterUse = $state1['player_team'][0]['ability_cooldown'];
        
        $this->engine->executeTurn('attack', 1, 2, true);
        $state2 = $this->engine->getState();
        
        $this->assertEquals($cooldownAfterUse - 1, $state2['player_team'][0]['ability_cooldown']);
    }
    
    public function testCooldownDecreasesEachTurn(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Mage', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50, 'special_ability' => 'Critical Strike']
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 500, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2, true);
        $state = $this->engine->getState();
        $initialCooldown = $state['player_team'][0]['ability_cooldown'];
        
        $this->engine->executeTurn('attack', 1, 2);
        $state = $this->engine->getState();
        
        $this->assertEquals($initialCooldown - 1, $state['player_team'][0]['ability_cooldown']);
    }
    
    public function testStunPreventsAction(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 500, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $state = $this->engine->getState();
        $state['player_team'][0]['stunned'] = true;
        $this->engine->setState($state);
        
        $result = $this->engine->executeTurn('attack', 1, 2);
        
        $this->assertStringContainsString('stunned', strtolower($result['battle_log'][0]['message']));
    }
    
    public function testBattleLogRecordsActions(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2);
        $state = $this->engine->getState();
        
        $this->assertCount(1, $state['battle_log']);
        $this->assertEquals(1, $state['battle_log'][0]['turn']);
    }
    
    public function testStateCanBeSavedAndRestored(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2);
        $savedState = $this->engine->getState();
        
        $newEngine = new BattleEngine();
        $newEngine->setState($savedState);
        $restoredState = $newEngine->getState();
        
        $this->assertEquals($savedState['turn'], $restoredState['turn']);
        $this->assertEquals($savedState['player_team'], $restoredState['player_team']);
        $this->assertEquals($savedState['enemy_team'], $restoredState['enemy_team']);
    }
    
    public function testMultipleChampions(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50],
            ['id' => 2, 'name' => 'Archer', 'health' => 80, 'attack' => 30, 'defense' => 5, 'speed' => 60],
        ];
        $enemyChampions = [
            ['id' => 3, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30],
            ['id' => 4, 'name' => 'Orc', 'health' => 100, 'attack' => 15, 'defense' => 10, 'speed' => 20],
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $state = $this->engine->getState();
        
        $this->assertCount(2, $state['player_team']);
        $this->assertCount(2, $state['enemy_team']);
    }
    
    public function testAITurnExecutes(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $result = $this->engine->executeAITurn();
        
        $this->assertArrayHasKey('turn', $result);
        $this->assertArrayHasKey('battle_log', $result);
    }
    
    public function testGetBattleSummary(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight', 'health' => 100, 'attack' => 20, 'defense' => 10, 'speed' => 50]
        ];
        $enemyChampions = [
            ['id' => 2, 'name' => 'Goblin', 'health' => 50, 'attack' => 10, 'defense' => 5, 'speed' => 30]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $this->engine->executeTurn('attack', 1, 2);
        $summary = $this->engine->getBattleSummary();
        
        $this->assertArrayHasKey('total_turns', $summary);
        $this->assertArrayHasKey('battle_log', $summary);
        $this->assertArrayHasKey('player_survivors', $summary);
        $this->assertArrayHasKey('enemy_survivors', $summary);
    }
    
    public function testDefaultValuesForMissingStats(): void {
        $playerChampions = [
            ['id' => 1, 'name' => 'Knight']
        ];
        $enemyChampions = [
            ['id' => 2]
        ];
        
        $this->engine->initializeBattle($playerChampions, $enemyChampions);
        $state = $this->engine->getState();
        
        $this->assertEquals(100, $state['player_team'][0]['max_health']);
        $this->assertEquals(100, $state['player_team'][0]['current_health']);
        $this->assertEquals(10, $state['player_team'][0]['attack']);
        $this->assertEquals(5, $state['player_team'][0]['defense']);
        $this->assertEquals(50, $state['player_team'][0]['speed']);
        $this->assertEquals('Unknown Enemy', $state['enemy_team'][0]['name']);
    }
}
