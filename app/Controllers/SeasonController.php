<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\PvpSeason;
use App\Models\SeasonReward;
use App\Services\SeasonService;

class SeasonController extends Controller {
    private SeasonService $seasonService;
    private PvpSeason $pvpSeasonModel;
    private SeasonReward $seasonRewardModel;
    
    public function __construct() {
        $this->seasonService = new SeasonService();
        $this->pvpSeasonModel = new PvpSeason();
        $this->seasonRewardModel = new SeasonReward();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $activeSeason = $this->seasonService->getActiveSeason();
        $timeRemaining = $activeSeason ? $this->seasonService->getTimeRemaining($activeSeason['id']) : null;
        $userRewards = $this->seasonService->getUserSeasonRewards($userId);
        $seasons = $this->seasonService->getSeasons(10);
        
        $this->view('game/season', [
            'activeSeason' => $activeSeason,
            'timeRemaining' => $timeRemaining,
            'userRewards' => $userRewards,
            'seasons' => $seasons,
        ]);
    }
    
    public function claimReward(string $id): void {
        $userId = Session::userId();
        $rewardId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $result = $this->seasonService->claimSeasonReward($userId, $rewardId);
        
        if ($result['success']) {
            $this->jsonSuccess($result);
        } else {
            $this->jsonError($result['error'], 400);
        }
    }
}
