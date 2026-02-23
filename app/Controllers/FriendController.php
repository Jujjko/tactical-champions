<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Friend;
use App\Models\User;

class FriendController extends Controller {
    private Friend $friendModel;
    private User $userModel;
    
    public function __construct() {
        $this->friendModel = new Friend();
        $this->userModel = new User();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $friends = $this->friendModel->getFriends($userId);
        $pendingRequests = $this->friendModel->getPendingRequests($userId);
        $sentRequests = $this->friendModel->getSentRequests($userId);
        $friendCount = $this->friendModel->getFriendCount($userId);
        
        $this->view('game/friends', [
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'sentRequests' => $sentRequests,
            'friendCount' => $friendCount
        ]);
    }
    
    public function search(): void {
        $userId = Session::userId();
        $query = trim($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->jsonSuccess(['users' => []]);
            return;
        }
        
        $users = $this->userModel->searchByName($query, $userId, 20);
        
        $this->jsonSuccess(['users' => $users]);
    }
    
    public function add(): void {
        $userId = Session::userId();
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $friendId = (int)($_POST['friend_id'] ?? 0);
        
        if ($friendId <= 0) {
            $this->jsonError('Invalid user', 400);
            return;
        }
        
        if ($this->friendModel->sendRequest($userId, $friendId)) {
            $this->jsonSuccess(['message' => 'Friend request sent!']);
        } else {
            $this->jsonError('Could not send friend request', 400);
        }
    }
    
    public function accept(string $id): void {
        $userId = Session::userId();
        $friendId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        if ($this->friendModel->acceptRequest($userId, $friendId)) {
            $this->jsonSuccess(['message' => 'Friend request accepted!']);
        } else {
            $this->jsonError('Could not accept request', 400);
        }
    }
    
    public function decline(string $id): void {
        $userId = Session::userId();
        $friendId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        if ($this->friendModel->declineRequest($userId, $friendId)) {
            $this->jsonSuccess(['message' => 'Friend request declined']);
        } else {
            $this->jsonError('Could not decline request', 400);
        }
    }
    
    public function remove(string $id): void {
        $userId = Session::userId();
        $friendId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        if ($this->friendModel->removeFriend($userId, $friendId)) {
            $this->jsonSuccess(['message' => 'Friend removed']);
        } else {
            $this->jsonError('Could not remove friend', 400);
        }
    }
}
