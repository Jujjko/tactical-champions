<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Tutorial;
use App\Models\Resource;

class TutorialController extends Controller {
    private Tutorial $tutorialModel;
    private Resource $resourceModel;
    
    private const TUTORIAL_STEPS = [
        'welcome' => [
            'title' => 'Welcome to Tactical Champions!',
            'description' => 'Build your team of champions, battle enemies, and become the ultimate tactician.',
            'icon' => '🎮',
            'reward_gold' => 100,
        ],
        'champion' => [
            'title' => 'Your First Champion',
            'description' => 'Each champion has unique abilities. Go to Champions to view your team.',
            'icon' => '⚔️',
            'reward_gold' => 100,
            'action_url' => '/champions',
            'action_text' => 'View Champions',
        ],
        'battle' => [
            'title' => 'Enter Battle',
            'description' => 'Test your skills in battle! Select a mission and fight enemies to earn rewards.',
            'icon' => '🗡️',
            'reward_gold' => 150,
            'action_url' => '/missions',
            'action_text' => 'Go to Missions',
        ],
        'upgrade' => [
            'title' => 'Power Up',
            'description' => 'Upgrade your champions to make them stronger. Spend gold to level up!',
            'icon' => '⬆️',
            'reward_gold' => 200,
            'action_url' => '/champions',
            'action_text' => 'Upgrade Champion',
        ],
    ];

    public function __construct() {
        $this->tutorialModel = new Tutorial();
        $this->resourceModel = new Resource();
    }

    public function index(): void {
        $userId = Session::userId();
        
        $progress = $this->tutorialModel->getProgress($userId);
        $currentStep = $this->tutorialModel->getNextStep($userId);
        $completionPercent = $this->tutorialModel->getCompletionPercent($userId);
        $hasCompletedAll = $this->tutorialModel->hasCompletedAll($userId);
        
        $steps = [];
        foreach (self::TUTORIAL_STEPS as $key => $step) {
            $steps[$key] = array_merge($step, [
                'key' => $key,
                'completed' => isset($progress[$key]),
            ]);
        }
        
        $this->view('game/tutorial', [
            'steps' => $steps,
            'currentStep' => $currentStep,
            'completionPercent' => $completionPercent,
            'hasCompletedAll' => $hasCompletedAll,
        ]);
    }

    public function complete(string $step): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        if (!isset(self::TUTORIAL_STEPS[$step])) {
            $this->jsonError('Invalid tutorial step', 400);
            return;
        }
        
        if ($this->tutorialModel->isCompleted($userId, $step)) {
            $this->jsonSuccess(['message' => 'Already completed']);
            return;
        }
        
        $this->tutorialModel->completeStep($userId, $step);
        
        $reward = self::TUTORIAL_STEPS[$step]['reward_gold'] ?? 0;
        if ($reward > 0) {
            $this->resourceModel->addGold($userId, $reward);
        }
        
        $nextStep = $this->tutorialModel->getNextStep($userId);
        $completionPercent = $this->tutorialModel->getCompletionPercent($userId);
        $hasCompletedAll = $this->tutorialModel->hasCompletedAll($userId);
        
        $this->jsonSuccess([
            'message' => 'Step completed!',
            'reward_gold' => $reward,
            'next_step' => $nextStep,
            'completion_percent' => $completionPercent,
            'all_completed' => $hasCompletedAll,
        ]);
    }

    public function skip(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        foreach (array_keys(self::TUTORIAL_STEPS) as $step) {
            if (!$this->tutorialModel->isCompleted($userId, $step)) {
                $this->tutorialModel->completeStep($userId, $step);
            }
        }
        
        $this->jsonSuccess([
            'message' => 'Tutorial skipped',
            'all_completed' => true,
        ]);
    }

    public function status(): void {
        $userId = Session::userId();
        
        $nextStep = $this->tutorialModel->getNextStep($userId);
        $completionPercent = $this->tutorialModel->getCompletionPercent($userId);
        $hasCompletedAll = $this->tutorialModel->hasCompletedAll($userId);
        
        $this->jsonSuccess([
            'next_step' => $nextStep,
            'completion_percent' => $completionPercent,
            'all_completed' => $hasCompletedAll,
        ]);
    }
}
