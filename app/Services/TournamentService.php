<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentParticipant;
use App\Models\Resource;
use Core\Database;
use Core\Session;

class TournamentService {
    private Database $db;
    private Tournament $tournamentModel;
    private TournamentMatch $matchModel;
    private TournamentParticipant $participantModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->tournamentModel = new Tournament();
        $this->matchModel = new TournamentMatch();
        $this->participantModel = new TournamentParticipant();
    }
    
    public function createTournament(array $data, int $createdBy): int {
        $maxPlayers = $data['max_players'] ?? 8;
        $roundTotal = (int)ceil(log($maxPlayers, 2));
        
        return $this->tournamentModel->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type'] ?? 'single_elimination',
            'max_players' => $maxPlayers,
            'entry_fee_gold' => $data['entry_fee_gold'] ?? 0,
            'entry_fee_gems' => $data['entry_fee_gems'] ?? 0,
            'min_rating' => $data['min_rating'] ?? 0,
            'status' => 'open',
            'start_time' => $data['start_time'] ?? null,
            'round_total' => $roundTotal,
            'created_by' => $createdBy,
        ]);
    }
    
    public function joinTournament(int $tournamentId, int $userId): array {
        $tournament = $this->tournamentModel->findById($tournamentId);
        
        if (!$tournament) {
            return ['success' => false, 'error' => 'Tournament not found'];
        }
        
        if ($tournament['status'] !== 'open') {
            return ['success' => false, 'error' => 'Tournament is not open for registration'];
        }
        
        if ($this->tournamentModel->isUserRegistered($tournamentId, $userId)) {
            return ['success' => false, 'error' => 'Already registered'];
        }
        
        $participantCount = $this->tournamentModel->getParticipantCount($tournamentId);
        if ($participantCount >= $tournament['max_players']) {
            return ['success' => false, 'error' => 'Tournament is full'];
        }
        
        $resource = new Resource();
        $userResources = $resource->getUserResources($userId);
        
        if (!$userResources) {
            return ['success' => false, 'error' => 'Resources not found'];
        }
        
        if ($tournament['entry_fee_gold'] > 0 && $userResources['gold'] < $tournament['entry_fee_gold']) {
            return ['success' => false, 'error' => 'Not enough gold'];
        }
        
        if ($tournament['entry_fee_gems'] > 0 && $userResources['gems'] < $tournament['entry_fee_gems']) {
            return ['success' => false, 'error' => 'Not enough gems'];
        }
        
        if ($tournament['min_rating'] > 0) {
            $pvpRating = (new PvpRating())->getOrCreateForUser($userId);
            if ($pvpRating['rating'] < $tournament['min_rating']) {
                return ['success' => false, 'error' => 'Rating too low'];
            }
        }
        
        if ($tournament['entry_fee_gold'] > 0) {
            $resource->deductGold($userId, $tournament['entry_fee_gold']);
        }
        
        if ($tournament['entry_fee_gems'] > 0) {
            $resource->deductGems($userId, $tournament['entry_fee_gems']);
        }
        
        $this->participantModel->create([
            'tournament_id' => $tournamentId,
            'user_id' => $userId,
            'seed' => random_int(1, 9999),
        ]);
        
        $newCount = $this->tournamentModel->getParticipantCount($tournamentId);
        if ($newCount >= $tournament['max_players']) {
            $this->tournamentModel->update($tournamentId, ['status' => 'full']);
        }
        
        return ['success' => true, 'message' => 'Successfully joined tournament'];
    }
    
    public function leaveTournament(int $tournamentId, int $userId): array {
        $tournament = $this->tournamentModel->findById($tournamentId);
        
        if (!$tournament) {
            return ['success' => false, 'error' => 'Tournament not found'];
        }
        
        if ($tournament['status'] !== 'open') {
            return ['success' => false, 'error' => 'Cannot leave started tournament'];
        }
        
        $stmt = $this->db->prepare("
            DELETE FROM tournament_participants 
            WHERE tournament_id = ? AND user_id = ?
        ");
        $stmt->execute([$tournamentId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            $tournament['status'] === 'full' && $this->tournamentModel->update($tournamentId, ['status' => 'open']);
            return ['success' => true, 'message' => 'Left tournament'];
        }
        
        return ['success' => false, 'error' => 'Not registered'];
    }
    
    public function generateBracket(int $tournamentId): bool {
        $tournament = $this->tournamentModel->findById($tournamentId);
        if (!$tournament || $tournament['status'] === 'ongoing') {
            return false;
        }
        
        $participants = $this->participantModel->getByTournament($tournamentId);
        
        if (count($participants) < 2) {
            return false;
        }
        
        usort($participants, fn($a, $b) => $a['seed'] <=> $b['seed']);
        
        $round = 1;
        $matchNumber = 1;
        $playerCount = count($participants);
        
        for ($i = 0; $i < $playerCount; $i += 2) {
            $player1Id = $participants[$i]['user_id'];
            $player2Id = $participants[$i + 1]['user_id'] ?? null;
            
            if ($player2Id === null) {
                $this->matchModel->create([
                    'tournament_id' => $tournamentId,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'player1_id' => $player1Id,
                    'player2_id' => null,
                    'status' => 'bye',
                    'winner_id' => $player1Id,
                ]);
            } else {
                $this->matchModel->create([
                    'tournament_id' => $tournamentId,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'player1_id' => $player1Id,
                    'player2_id' => $player2Id,
                    'status' => 'pending',
                ]);
            }
        }
        
        $this->tournamentModel->update($tournamentId, [
            'status' => 'ongoing',
            'round_current' => 1,
        ]);
        
        return true;
    }
    
    public function processMatchResult(int $matchId, int $winnerId): array {
        $match = $this->matchModel->findById($matchId);
        
        if (!$match) {
            return ['success' => false, 'error' => 'Match not found'];
        }
        
        if ($match['status'] === 'finished') {
            return ['success' => false, 'error' => 'Match already finished'];
        }
        
        $loserId = $winnerId === $match['player1_id'] ? $match['player2_id'] : $match['player1_id'];
        
        $this->matchModel->setWinner($matchId, $winnerId, $loserId);
        
        $winnerParticipant = $this->getParticipantByUser($match['tournament_id'], $winnerId);
        $loserParticipant = $this->getParticipantByUser($match['tournament_id'], $loserId);
        
        if ($winnerParticipant) {
            $this->participantModel->addWin($winnerParticipant['id']);
        }
        
        if ($loserParticipant) {
            $this->participantModel->addLoss($loserParticipant['id']);
            $this->participantModel->eliminate($loserParticipant['id'], $match['round']);
        }
        
        $this->advanceWinner($match, $winnerId);
        
        $this->checkTournamentProgress($match['tournament_id']);
        
        return ['success' => true, 'message' => 'Match result recorded'];
    }
    
    private function advanceWinner(array $match, int $winnerId): void {
        $tournamentId = $match['tournament_id'];
        $currentRound = $match['round'];
        $currentMatchNumber = $match['match_number'];
        
        $nextRound = $currentRound + 1;
        $nextMatchNumber = (int)ceil($currentMatchNumber / 2);
        
        $stmt = $this->db->prepare("
            SELECT * FROM tournament_matches 
            WHERE tournament_id = ? AND round = ? AND match_number = ?
        ");
        $stmt->execute([$tournamentId, $nextRound, $nextMatchNumber]);
        $nextMatch = $stmt->fetch();
        
        if ($nextMatch) {
            $updateField = $currentMatchNumber % 2 === 1 ? 'player1_id' : 'player2_id';
            $this->matchModel->update($nextMatch['id'], [$updateField => $winnerId]);
        } else {
            $this->matchModel->create([
                'tournament_id' => $tournamentId,
                'round' => $nextRound,
                'match_number' => $nextMatchNumber,
                'player1_id' => $currentMatchNumber % 2 === 1 ? $winnerId : null,
                'player2_id' => $currentMatchNumber % 2 === 0 ? $winnerId : null,
                'status' => 'pending',
            ]);
        }
    }
    
    private function checkTournamentProgress(int $tournamentId): void {
        $tournament = $this->tournamentModel->findById($tournamentId);
        $pendingMatches = $this->matchModel->countPendingMatches($tournamentId);
        
        if ($pendingMatches === 0) {
            $currentRound = $tournament['round_current'];
            
            $roundMatches = $this->matchModel->getByRound($tournamentId, $currentRound);
            $winners = array_filter(array_column($roundMatches, 'winner_id'));
            
            if (count($winners) === 1) {
                $winnerId = reset($winners);
                $this->endTournament($tournamentId, $winnerId);
            } else {
                $this->tournamentModel->update($tournamentId, [
                    'round_current' => $currentRound + 1,
                ]);
            }
        }
    }
    
    private function endTournament(int $tournamentId, int $winnerId): void {
        $this->tournamentModel->update($tournamentId, [
            'status' => 'finished',
            'winner_id' => $winnerId,
            'end_time' => date('Y-m-d H:i:s'),
        ]);
        
        $winnerParticipant = $this->getParticipantByUser($tournamentId, $winnerId);
        if ($winnerParticipant) {
            $this->participantModel->setFinalRank($winnerParticipant['id'], 1);
        }
        
        $this->distributeRewards($tournamentId);
    }
    
    private function distributeRewards(int $tournamentId): void {
        $stmt = $this->db->prepare("
            SELECT * FROM tournament_rewards WHERE tournament_id = ? ORDER BY place ASC
        ");
        $stmt->execute([$tournamentId]);
        $rewards = $stmt->fetchAll();
        
        foreach ($rewards as $reward) {
            $participant = $this->getParticipantByRank($tournamentId, $reward['place']);
            
            if (!$participant) continue;
            
            $resource = new Resource();
            
            if ($reward['gold'] > 0) {
                $resource->addGold($participant['user_id'], $reward['gold']);
            }
            
            if ($reward['gems'] > 0) {
                $resource->addGems($participant['user_id'], $reward['gems']);
            }
            
            if ($reward['lootbox_type'] && $reward['lootbox_count'] > 0) {
                for ($i = 0; $i < $reward['lootbox_count']; $i++) {
                    $resource->addLootbox($participant['user_id'], $reward['lootbox_type']);
                }
            }
        }
    }
    
    private function getParticipantByUser(int $tournamentId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM tournament_participants WHERE tournament_id = ? AND user_id = ?
        ");
        $stmt->execute([$tournamentId, $userId]);
        return $stmt->fetch() ?: null;
    }
    
    private function getParticipantByRank(int $tournamentId, int $rank): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM tournament_participants WHERE tournament_id = ? AND final_rank = ?
        ");
        $stmt->execute([$tournamentId, $rank]);
        return $stmt->fetch() ?: null;
    }
    
    public function getUserCurrentMatch(int $tournamentId, int $userId): ?array {
        return $this->matchModel->getUserCurrentMatch($tournamentId, $userId);
    }
    
    public function getTournamentBracket(int $tournamentId): ?array {
        return $this->tournamentModel->getWithMatches($tournamentId);
    }
}
