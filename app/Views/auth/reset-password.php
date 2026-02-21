<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8 fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl text-5xl mb-4 neon-glow">⚔️</div>
            <h1 class="title-font text-4xl font-bold tracking-tight">Tactical Champions</h1>
            <p class="text-white/60 mt-2">Create a new password</p>
        </div>
        
        <div class="glass rounded-3xl p-8 fade-in">
            <h2 class="text-2xl font-semibold text-center mb-6">Reset Password</h2>
            
            <form method="POST" action="/reset-password">
                <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/80 mb-2">New Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="Min 8 characters" autocomplete="new-password" required minlength="8">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white/80 mb-2">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="password" name="confirm_password" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="Confirm your password" autocomplete="new-password" required>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 rounded-xl transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="key" class="w-5 h-5"></i>
                    Reset Password
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium transition">Back to Login</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>