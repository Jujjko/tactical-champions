<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Services\RateLimiter;
use App\Services\DailyLoginService;
use App\Services\AuditService;
use App\Services\MailService;
use App\Services\PlayerSetupService;

class AuthController extends Controller {
    private User $userModel;
    private PasswordResetToken $tokenModel;
    private RateLimiter $rateLimiter;
    private DailyLoginService $dailyLoginService;
    private AuditService $auditService;
    private MailService $mailService;
    private PlayerSetupService $playerSetupService;
    private Validator $validator;
    
    public function __construct() {
        $this->userModel = new User();
        $this->tokenModel = new PasswordResetToken();
        $this->rateLimiter = new RateLimiter(5, 900);
        $this->dailyLoginService = new DailyLoginService();
        $this->auditService = new AuditService();
        $this->mailService = new MailService();
        $this->playerSetupService = new PlayerSetupService();
        $this->validator = new Validator();
    }
    
    public function showLogin(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('auth/login');
    }
    
    public function showRegister(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('auth/register');
    }
    
    public function showForgotPassword(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('auth/forgot-password');
    }
    
    public function showResetPassword(string $token): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $tokenData = $this->tokenModel->findValidToken($token);
        
        if (!$tokenData) {
            Session::flash('error', 'Invalid or expired reset link');
            $this->redirect('/forgot-password');
            return;
        }
        
        $this->view('auth/reset-password', ['token' => $token]);
    }
    
    public function sendResetLink(): void {
        $ipKey = 'reset_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if ($this->rateLimiter->tooManyAttempts($ipKey)) {
            Session::flash('error', 'Too many reset attempts. Please try again later.');
            $this->redirect('/forgot-password');
            return;
        }
        
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/forgot-password');
            return;
        }
        
        $isValid = $this->validator->validate($_POST, [
            'email' => 'required|email'
        ]);
        
        if (!$isValid) {
            Session::flash('error', 'Please enter a valid email');
            $this->redirect('/forgot-password');
            return;
        }
        
        $this->rateLimiter->hit($ipKey);
        
        $user = $this->userModel->findByEmail($_POST['email']);
        
        if ($user) {
            $token = $this->tokenModel->createToken($user['id']);
            $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost') . '/reset-password/' . $token;
            
            $this->mailService->sendPasswordReset($user['email'], $resetLink);
            $this->auditService->logPasswordReset($user['id'], true);
        }
        
        Session::flash('success', 'If the email exists, a reset link has been sent.');
        $this->redirect('/forgot-password');
    }
    
    public function resetPassword(): void {
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/forgot-password');
            return;
        }
        
        $isValid = $this->validator->validate($_POST, [
            'token' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);
        
        if (!$isValid) {
            Session::flash('error', 'Please check your input');
            $this->redirect('/reset-password/' . $_POST['token']);
            return;
        }
        
        if ($_POST['password'] !== $_POST['confirm_password']) {
            Session::flash('error', 'Passwords do not match');
            $this->redirect('/reset-password/' . $_POST['token']);
            return;
        }
        
        $tokenData = $this->tokenModel->findValidToken($_POST['token']);
        
        if (!$tokenData) {
            Session::flash('error', 'Invalid or expired reset link');
            $this->redirect('/forgot-password');
            return;
        }
        
        $this->userModel->updatePassword($tokenData['user_id'], $_POST['password']);
        $this->tokenModel->markAsUsed($_POST['token']);
        
        $this->auditService->logPasswordReset($tokenData['user_id'], false);
        
        Session::flash('success', 'Password has been reset. You can now login.');
        $this->redirect('/login');
    }
    
    public function login(): void {
        $ipKey = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if ($this->rateLimiter->tooManyAttempts($ipKey)) {
            $remaining = $this->rateLimiter->getRemainingLockoutTime($ipKey);
            Session::flash('error', "Too many login attempts. Please try again in {$remaining} seconds.");
            $this->redirect('/login');
            return;
        }
        
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/login');
            return;
        }
        
        $isValid = $this->validator->validate($_POST, [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!$isValid) {
            Session::flash('error', 'Please fill all fields');
            $this->redirect('/login');
            return;
        }
        
        $user = $this->userModel->findByUsername($_POST['username']);
        
        if (!$user || !$this->userModel->verifyPassword($_POST['password'], $user['password_hash'])) {
            $this->rateLimiter->hit($ipKey);
            $remaining = $this->rateLimiter->getRemainingAttempts($ipKey);
            
            if ($user) {
                $this->auditService->logLogin($user['id'], false, 'Invalid password');
            }
            
            Session::flash('error', "Invalid credentials. {$remaining} attempts remaining.");
            $this->redirect('/login');
            return;
        }
        
        if (!$user['is_active']) {
            $this->auditService->logLogin($user['id'], false, 'Account disabled');
            Session::flash('error', 'Account is disabled');
            $this->redirect('/login');
            return;
        }
        
        $this->rateLimiter->clear($ipKey);
        
        Session::regenerateId();
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('user_role', $user['role']);
        
        $this->userModel->updateLastLogin($user['id']);
        $this->auditService->logLogin($user['id'], true);
        
        $loginResult = $this->dailyLoginService->processDailyLogin($user['id']);
        
        if (!$loginResult['already_claimed'] && $loginResult['success']) {
            Session::set('daily_login_reward', $loginResult);
        }
        
        $this->redirect('/dashboard');
    }
    
    public function register(): void {
        $ipKey = 'register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if ($this->rateLimiter->tooManyAttempts($ipKey)) {
            $remaining = $this->rateLimiter->getRemainingLockoutTime($ipKey);
            Session::flash('error', "Too many registration attempts. Please try again in {$remaining} seconds.");
            $this->redirect('/register');
            return;
        }
        
        if (!$this->validateCsrf()) {
            $this->rateLimiter->hit($ipKey);
            Session::flash('error', 'Invalid request');
            $this->redirect('/register');
            return;
        }
        
        $isValid = $this->validator->validate($_POST, [
            'username' => 'required|min:3|max:50|alphanumeric|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);
        
        if (!$isValid) {
            $this->rateLimiter->hit($ipKey);
            Session::flash('error', $this->validator->firstError('username') ?? $this->validator->firstError('email') ?? 'Please check your input');
            $this->redirect('/register');
            return;
        }
        
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $this->rateLimiter->hit($ipKey);
            Session::flash('error', 'Passwords do not match');
            $this->redirect('/register');
            return;
        }
        
        $userId = $this->userModel->createUser(
            htmlspecialchars($_POST['username']),
            htmlspecialchars($_POST['email']),
            $_POST['password']
        );
        
        $this->playerSetupService->setupNewPlayer($userId);
        
        $this->rateLimiter->clear($ipKey);
        
        $this->auditService->logCreate('user', $userId, [
            'username' => $_POST['username'],
            'email' => $_POST['email']
        ]);
        
        $this->mailService->sendWelcome($_POST['email'], $_POST['username']);
        
        $this->redirectWithSuccess('/login', 'Registration successful! Please login.');
    }
    
    public function logout(): void {
        $userId = Session::userId();
        
        if ($userId) {
            $this->auditService->logLogout($userId);
        }
        
        Session::destroy();
        $this->redirect('/login');
    }
}
