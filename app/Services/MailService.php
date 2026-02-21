<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Core\Config;

class MailService {
    private PHPMailer $mailer;
    private bool $enabled;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->enabled = Config::get('MAIL_ENABLED', 'false') === 'true';
        $this->configure();
    }
    
    private function configure(): void {
        if (!$this->enabled) {
            return;
        }
        
        $this->mailer->isSMTP();
        $this->mailer->Host = Config::get('MAIL_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = Config::get('MAIL_USERNAME', '');
        $this->mailer->Password = Config::get('MAIL_PASSWORD', '');
        $this->mailer->SMTPSecure = Config::get('MAIL_ENCRYPTION', 'tls');
        $this->mailer->Port = (int)Config::get('MAIL_PORT', 587);
        
        $this->mailer->setFrom(
            Config::get('MAIL_FROM_ADDRESS', 'noreply@tacticalchampions.com'),
            Config::get('MAIL_FROM_NAME', 'Tactical Champions')
        );
        
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }
    
    public function send(string $to, string $subject, string $body, string $altBody = ''): bool {
        if (!$this->enabled) {
            return $this->logEmail($to, $subject, $body);
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
    
    public function sendPasswordReset(string $to, string $resetLink): bool {
        $subject = 'Password Reset - Tactical Champions';
        $body = $this->renderTemplate('password-reset', [
            'reset_link' => $resetLink,
            'expires_hours' => 1
        ]);
        
        return $this->send($to, $subject, $body);
    }
    
    public function sendWelcome(string $to, string $username): bool {
        $subject = 'Welcome to Tactical Champions!';
        $body = $this->renderTemplate('welcome', [
            'username' => $username,
            'login_url' => Config::get('APP_URL', 'http://localhost') . '/login'
        ]);
        
        return $this->send($to, $subject, $body);
    }
    
    public function sendEmailVerification(string $to, string $username, string $verifyLink): bool {
        $subject = 'Verify Your Email - Tactical Champions';
        $body = $this->renderTemplate('email-verification', [
            'username' => $username,
            'verify_link' => $verifyLink
        ]);
        
        return $this->send($to, $subject, $body);
    }
    
    private function renderTemplate(string $template, array $data): string {
        $templates = [
            'password-reset' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background: #0a0818; color: #f1f5f9; padding: 40px; }
                        .container { max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 40px; }
                        .logo { text-align: center; font-size: 32px; margin-bottom: 30px; }
                        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: bold; }
                        .footer { margin-top: 30px; font-size: 12px; color: #94a3b8; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="logo">⚔️ Tactical Champions</div>
                        <h2>Password Reset Request</h2>
                        <p>You have requested to reset your password. Click the button below to set a new password:</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="{{reset_link}}" class="btn">Reset Password</a>
                        </p>
                        <p style="color: #94a3b8;">This link will expire in {{expires_hours}} hour(s).</p>
                        <p style="color: #94a3b8;">If you did not request this reset, please ignore this email.</p>
                        <div class="footer">
                            © 2025 Tactical Champions. All rights reserved.
                        </div>
                    </div>
                </body>
                </html>
            ',
            'welcome' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background: #0a0818; color: #f1f5f9; padding: 40px; }
                        .container { max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 40px; }
                        .logo { text-align: center; font-size: 32px; margin-bottom: 30px; }
                        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: bold; }
                        .footer { margin-top: 30px; font-size: 12px; color: #94a3b8; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="logo">⚔️ Tactical Champions</div>
                        <h2>Welcome, {{username}}!</h2>
                        <p>Your account has been created successfully. You are now ready to begin your journey!</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="{{login_url}}" class="btn">Start Playing</a>
                        </p>
                        <p>Start by opening lootboxes to get your first champions, then take on missions to earn rewards!</p>
                        <div class="footer">
                            © 2025 Tactical Champions. All rights reserved.
                        </div>
                    </div>
                </body>
                </html>
            ',
            'email-verification' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background: #0a0818; color: #f1f5f9; padding: 40px; }
                        .container { max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 40px; }
                        .logo { text-align: center; font-size: 32px; margin-bottom: 30px; }
                        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: bold; }
                        .footer { margin-top: 30px; font-size: 12px; color: #94a3b8; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="logo">⚔️ Tactical Champions</div>
                        <h2>Verify Your Email</h2>
                        <p>Hello {{username}},</p>
                        <p>Please verify your email address by clicking the button below:</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="{{verify_link}}" class="btn">Verify Email</a>
                        </p>
                        <div class="footer">
                            © 2025 Tactical Champions. All rights reserved.
                        </div>
                    </div>
                </body>
                </html>
            '
        ];
        
        $html = $templates[$template] ?? '';
        
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }
        
        return $html;
    }
    
    private function logEmail(string $to, string $subject, string $body): bool {
        error_log("[MAIL] To: {$to}, Subject: {$subject}");
        return true;
    }
    
    public function isEnabled(): bool {
        return $this->enabled;
    }
}
