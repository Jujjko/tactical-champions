<?php
declare(strict_types=1);

namespace App\Services;

class BattleEngine {
    private array $battleLog = [];
    private array $playerTeam = [];
    private array $enemyTeam = [];
    private int $turn = 0;
    private bool $isPvp = false;
    private int $opponentId = 0;
    private string $opponentName = '';
    
    public function setAsPvp(int $opponentId = 0, string $opponentName = 'Opponent'): void {
        $this->isPvp = true;
        $this->opponentId = $opponentId;
        $this->opponentName = $opponentName;
    }
    
    public function isPvp(): bool {
        return $this->isPvp;
    }
    
    public function getOpponentId(): int {
        return $this->opponentId;
    }
    
    public function getOpponentName(): string {
        return $this->opponentName;
    }
    
    /**
     * Initialize a new battle
     */
    public function initializeBattle(array $playerChampions, array $enemyChampions): void {
        $this->battleLog = [];
        $this->turn = 0;
        
        foreach ($playerChampions as $champion) {
            $this->playerTeam[] = [
                'id' => $champion['id'] ?? 0,
                'name' => $champion['name'] ?? 'Unknown Champion',
                'max_health' => $champion['health'] ?? 100,
                'current_health' => $champion['health'] ?? 100,
                'attack' => $champion['attack'] ?? 10,
                'defense' => $champion['defense'] ?? 5,
                'speed' => $champion['speed'] ?? 50,
                'special_ability' => $champion['special_ability'] ?? null,
                'ability_cooldown' => 0,
                'stunned' => false,
                'frozen' => false,
                'is_player' => true,
                'alive' => true,
                'icon' => $champion['icon'] ?? '🛡️',
                'image_url' => $champion['image_url'] ?? ''
            ];
        }
        
        foreach ($enemyChampions as $champion) {
            $this->enemyTeam[] = [
                'id' => $champion['id'] ?? 'enemy_0',
                'name' => $champion['name'] ?? 'Unknown Enemy',
                'max_health' => $champion['health'] ?? 100,
                'current_health' => $champion['health'] ?? 100,
                'attack' => $champion['attack'] ?? 10,
                'defense' => $champion['defense'] ?? 5,
                'speed' => $champion['speed'] ?? 50,
                'special_ability' => $champion['special_ability'] ?? null,
                'ability_cooldown' => 0,
                'stunned' => false,
                'frozen' => false,
                'is_player' => false,
                'alive' => true,
                'icon' => $champion['icon'] ?? '👹',
                'image_url' => $champion['image_url'] ?? ''
            ];
        }
    }
    
    /**
     * Execute a turn of combat
     */
    public function executeTurn(string $action, string|int|null $attackerId, string|int|null $targetId, bool $useAbility = false): array {
        $this->turn++;
        
        // Get attacker and target
        $attacker = $this->getChampionById((string)$attackerId);
        $target = $this->getChampionById((string)$targetId);
        
        if (!$attacker || !$target || !$attacker['alive'] || !$target['alive']) {
            return [
                'success' => false,
                'message' => 'Invalid attacker or target'
            ];
        }
        
        // Check if stunned or frozen
        if ($attacker['stunned']) {
            $this->logAction("{$attacker['name']} is stunned and cannot act!");
            $this->updateChampion($attackerId, ['stunned' => false]);
            
            $battleResult = $this->checkBattleEnd();
            return array_merge($this->getState(), [
                'battle_ended' => $battleResult !== null,
                'winner' => $battleResult
            ]);
        }
        
        if ($attacker['frozen']) {
            $this->logAction("{$attacker['name']} is frozen and cannot act!");
            // 50% chance to break free
            if (rand(1, 100) <= 50) {
                $this->updateChampion($attackerId, ['frozen' => false]);
                $this->logAction("{$attacker['name']} broke free from freeze!");
            }
            
            $battleResult = $this->checkBattleEnd();
            return array_merge($this->getState(), [
                'battle_ended' => $battleResult !== null,
                'winner' => $battleResult
            ]);
        }
        
        // Execute action
        if ($useAbility && $attacker['ability_cooldown'] <= 0) {
            $result = $this->useSpecialAbility($attacker, $target);
        } else {
            $result = $this->executeAttack($attacker, $target);
        }
        
        // Update cooldowns
        $this->updateCooldowns();
        
        // Check battle end
        $battleResult = $this->checkBattleEnd();
        
        return array_merge($this->getState(), [
            'battle_ended' => $battleResult !== null,
            'winner' => $battleResult
        ]);
    }
    
    /**
     * Execute AI turn
     */
    public function executeAITurn(): array {
        // Find alive enemy
        $aliveEnemies = array_filter($this->enemyTeam, fn($c) => $c['alive']);
        if (empty($aliveEnemies)) {
            return $this->getState();
        }
        
        // Sort by speed to determine order
        usort($aliveEnemies, fn($a, $b) => $b['speed'] <=> $a['speed']);
        $attacker = $aliveEnemies[0];
        
        // Find alive player champion
        $alivePlayer = array_filter($this->playerTeam, fn($c) => $c['alive']);
        if (empty($alivePlayer)) {
            return $this->getState();
        }
        
        // AI decision making
        $target = $this->selectAITarget($attacker, $alivePlayer);
        $useAbility = $this->shouldUseAbility($attacker);
        
        return $this->executeTurn('attack', $attacker['id'], $target['id'], $useAbility);
    }
    
    /**
     * AI target selection
     */
    private function selectAITarget(array $attacker, array $playerTeam): array {
        // Different AI behaviors
        $behavior = $this->getAIBehavior($attacker);
        
        switch ($behavior) {
            case 'aggressive':
                // Target lowest HP
                usort($playerTeam, fn($a, $b) => $a['current_health'] <=> $b['current_health']);
                return $playerTeam[0];
                
            case 'defensive':
                // Target highest attack
                usort($playerTeam, fn($a, $b) => $b['attack'] <=> $a['attack']);
                return $playerTeam[0];
                
            case 'tactical':
                // Target based on threat level (HP + Attack)
                usort($playerTeam, fn($a, $b) => 
                    ($a['current_health'] + $a['attack']) <=> 
                    ($b['current_health'] + $b['attack'])
                );
                return $playerTeam[0];
                
            default:
                // Random target
                return $playerTeam[array_rand($playerTeam)];
        }
    }
    
    /**
     * Determine AI behavior based on champion tier
     */
    private function getAIBehavior(array $champion): string {
        $hash = crc32($champion['name']);
        $behaviors = ['aggressive', 'defensive', 'tactical', 'random'];
        return $behaviors[$hash % count($behaviors)];
    }
    
    /**
     * Decide if AI should use special ability
     */
    private function shouldUseAbility(array $attacker): bool {
        if ($attacker['ability_cooldown'] > 0 || !$attacker['special_ability']) {
            return false;
        }
        
        // 30% chance to use ability when available
        return rand(1, 100) <= 30;
    }
    
    /**
     * Execute basic attack
     */
    private function executeAttack(array $attacker, array $target): array {
        // Calculate damage with variance (80-120%)
        $variance = rand(80, 120) / 100;
        $baseDamage = $attacker['attack'] * $variance;
        
        // Apply defense reduction (defense reduces damage by %)
        $damageReduction = min($target['defense'] / ($target['defense'] + 100), 0.75);
        $finalDamage = (int)($baseDamage * (1 - $damageReduction));
        
        // Critical hit chance (10%)
        $isCritical = rand(1, 100) <= 10;
        if ($isCritical) {
            $finalDamage = (int)($finalDamage * 1.5);
        }
        
        // Apply damage
        $newHealth = max(0, $target['current_health'] - $finalDamage);
        $this->updateChampion($target['id'], ['current_health' => $newHealth]);
        
        if ($newHealth <= 0) {
            $this->updateChampion($target['id'], ['alive' => false]);
            $this->logAction("{$attacker['name']} attacked {$target['name']} for {$finalDamage} damage" . 
                ($isCritical ? " (CRITICAL!)" : "") . " - {$target['name']} was defeated!");
        } else {
            $this->logAction("{$attacker['name']} attacked {$target['name']} for {$finalDamage} damage" . 
                ($isCritical ? " (CRITICAL!)" : "") . " ({$newHealth}/{$target['max_health']} HP remaining)");
        }
        
        return [
            'damage' => $finalDamage,
            'critical' => $isCritical,
            'killed' => $newHealth <= 0
        ];
    }
    
    /**
     * Use special ability
     */
    private function useSpecialAbility(array $attacker, array $target): array {
        $ability = strtolower($attacker['special_ability'] ?? '');
        
        // Parse ability name to determine type
        if (str_contains($ability, 'stun') || str_contains($ability, 'bash')) {
            return $this->abilityStun($attacker, $target);
        } elseif (str_contains($ability, 'critical') || str_contains($ability, 'strike')) {
            return $this->abilityCriticalStrike($attacker, $target);
        } elseif (str_contains($ability, 'freeze') || str_contains($ability, 'frost') || str_contains($ability, 'ice')) {
            return $this->abilityFreeze($attacker, $target);
        } elseif (str_contains($ability, 'aoe') || str_contains($ability, 'breath') || str_contains($ability, 'nova')) {
            return $this->abilityAOE($attacker);
        } elseif (str_contains($ability, 'heal') || str_contains($ability, 'rebirth')) {
            return $this->abilityHeal($attacker);
        } else {
            // Default: power attack
            return $this->abilityPowerAttack($attacker, $target);
        }
    }
    
    /**
     * Stun ability
     */
    private function abilityStun(array $attacker, array $target): array {
        $damage = (int)($attacker['attack'] * 0.8);
        $newHealth = max(0, $target['current_health'] - $damage);
        
        $this->updateChampion($target['id'], [
            'current_health' => $newHealth,
            'stunned' => true,
            'alive' => $newHealth > 0
        ]);
        $this->updateChampion($attacker['id'], ['ability_cooldown' => 3]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Dealt {$damage} damage and stunned {$target['name']}!");
        
        return ['damage' => $damage, 'effect' => 'stun'];
    }
    
    /**
     * Critical Strike ability
     */
    private function abilityCriticalStrike(array $attacker, array $target): array {
        $damage = (int)($attacker['attack'] * 2.5);
        $newHealth = max(0, $target['current_health'] - $damage);
        
        $this->updateChampion($target['id'], [
            'current_health' => $newHealth,
            'alive' => $newHealth > 0
        ]);
        $this->updateChampion($attacker['id'], ['ability_cooldown' => 4]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Massive {$damage} damage to {$target['name']}!");
        
        return ['damage' => $damage, 'effect' => 'critical'];
    }
    
    /**
     * Freeze ability
     */
    private function abilityFreeze(array $attacker, array $target): array {
        $teams = $attacker['is_player'] ? $this->enemyTeam : $this->playerTeam;
        $frozenCount = 0;
        
        foreach ($teams as $key => $champion) {
            if ($champion['alive']) {
                $teams[$key]['frozen'] = true;
                $frozenCount++;
            }
        }
        
        if ($attacker['is_player']) {
            $this->enemyTeam = $teams;
        } else {
            $this->playerTeam = $teams;
        }
        
        $this->updateChampion($attacker['id'], ['ability_cooldown' => 5]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Froze {$frozenCount} enemies!");
        
        return ['damage' => 0, 'effect' => 'freeze', 'targets' => $frozenCount];
    }
    
    /**
     * AOE ability
     */
    private function abilityAOE(array $attacker): array {
        $teams = $attacker['is_player'] ? $this->enemyTeam : $this->playerTeam;
        $damage = (int)($attacker['attack'] * 1.2);
        $totalDamage = 0;
        
        foreach ($teams as $key => $champion) {
            if ($champion['alive']) {
                $newHealth = max(0, $champion['current_health'] - $damage);
                $teams[$key]['current_health'] = $newHealth;
                if ($newHealth <= 0) {
                    $teams[$key]['alive'] = false;
                }
                $totalDamage += $damage;
            }
        }
        
        if ($attacker['is_player']) {
            $this->enemyTeam = $teams;
        } else {
            $this->playerTeam = $teams;
        }
        
        $this->updateChampion($attacker['id'], ['ability_cooldown' => 4]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Dealt {$damage} damage to all enemies!");
        
        return ['damage' => $totalDamage, 'effect' => 'aoe'];
    }
    
    /**
     * Heal ability
     */
    private function abilityHeal(array $attacker): array {
        $healAmount = (int)($attacker['max_health'] * 0.5);
        $newHealth = min($attacker['max_health'], $attacker['current_health'] + $healAmount);
        
        $this->updateChampion($attacker['id'], [
            'current_health' => $newHealth,
            'ability_cooldown' => 5
        ]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Healed for {$healAmount} HP!");
        
        return ['damage' => 0, 'effect' => 'heal', 'amount' => $healAmount];
    }
    
    /**
     * Power Attack ability
     */
    private function abilityPowerAttack(array $attacker, array $target): array {
        $damage = (int)($attacker['attack'] * 2);
        $newHealth = max(0, $target['current_health'] - $damage);
        
        $this->updateChampion($target['id'], [
            'current_health' => $newHealth,
            'alive' => $newHealth > 0
        ]);
        $this->updateChampion($attacker['id'], ['ability_cooldown' => 3]);
        
        $this->logAction("{$attacker['name']} used {$attacker['special_ability']}! " .
            "Dealt {$damage} damage!");
        
        return ['damage' => $damage, 'effect' => 'power'];
    }
    
    /**
     * Update cooldowns
     */
    private function updateCooldowns(): void {
        foreach ($this->playerTeam as $key => $champion) {
            if ($champion['ability_cooldown'] > 0) {
                $this->playerTeam[$key]['ability_cooldown']--;
            }
        }
        
        foreach ($this->enemyTeam as $key => $champion) {
            if ($champion['ability_cooldown'] > 0) {
                $this->enemyTeam[$key]['ability_cooldown']--;
            }
        }
    }
    
    /**
     * Check if battle has ended
     */
    private function checkBattleEnd(): ?string {
        $playerAlive = array_filter($this->playerTeam, fn($c) => $c['alive']);
        $enemyAlive = array_filter($this->enemyTeam, fn($c) => $c['alive']);
        
        if (empty($playerAlive)) {
            return 'defeat';
        }
        
        if (empty($enemyAlive)) {
            return 'victory';
        }
        
        return null;
    }
    
    /**
     * Get champion by ID
     */
    private function getChampionById(string|int|null $id): ?array {
        if ($id === null) {
            return null;
        }
        
        $id = (string)$id;
        
        foreach ($this->playerTeam as $champion) {
            if ($champion['id'] == $id) {
                return $champion;
            }
        }
        
        foreach ($this->enemyTeam as $champion) {
            if ($champion['id'] == $id) {
                return $champion;
            }
        }
        
        return null;
    }
    
    /**
     * Update champion stats
     */
    private function updateChampion(string|int $id, array $updates): void {
        $id = (string)$id;
        
        foreach ($this->playerTeam as &$champion) {
            if ($champion['id'] == $id) {
                $champion = array_merge($champion, $updates);
                return;
            }
        }
        unset($champion);
        
        foreach ($this->enemyTeam as &$champion) {
            if ($champion['id'] == $id) {
                $champion = array_merge($champion, $updates);
                return;
            }
        }
        unset($champion);
    }
    
    /**
     * Log battle action
     */
    private function logAction(string $message): void {
        $this->battleLog[] = [
            'turn' => $this->turn,
            'message' => $message,
            'timestamp' => time()
        ];
    }
    
/**
     * Get current battle state
     */
    public function getState(): array {
        return [
            'turn' => $this->turn,
            'player_team' => $this->playerTeam,
            'enemy_team' => $this->enemyTeam,
            'battle_log' => $this->battleLog,
            'is_pvp' => $this->isPvp,
            'opponent_id' => $this->opponentId,
            'opponent_name' => $this->opponentName,
        ];
    }
    
    /**
     * Restore battle state from array
     */
    public function setState(array $state): void {
        $this->turn = $state['turn'] ?? 0;
        $this->playerTeam = $state['player_team'] ?? [];
        $this->enemyTeam = $state['enemy_team'] ?? [];
        $this->battleLog = $state['battle_log'] ?? [];
        $this->isPvp = $state['is_pvp'] ?? false;
        $this->opponentId = $state['opponent_id'] ?? 0;
        $this->opponentName = $state['opponent_name'] ?? '';
    }
    
    /**
     * Get battle summary
     */
    public function getBattleSummary(): array {
        return [
            'total_turns' => $this->turn,
            'battle_log' => $this->battleLog,
            'player_survivors' => array_filter($this->playerTeam, fn($c) => $c['alive']),
            'enemy_survivors' => array_filter($this->enemyTeam, fn($c) => $c['alive'])
        ];
    }
}