<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8 fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl text-5xl mb-4 neon-glow">⚔️</div>
            <h1 class="title-font text-4xl font-bold tracking-tight">Tactical Champions</h1>
            <p class="text-white/60 mt-2">Begin your journey</p>
        </div>
        
        <div class="glass rounded-3xl p-8 fade-in">
            <h2 class="text-2xl font-semibold text-center mb-6">Create Account</h2>
            
            <form method="POST" action="/register">
                <input type="hidden" name="csrf_token" value="<?= \Core\Session::csrfToken() ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/80 mb-2">Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="text" name="username" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" autocomplete="username" placeholder="Choose a username" required minlength="3">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/80 mb-2">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="email" name="email" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" autocomplete="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/80 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" autocomplete="new-password" placeholder="Min 8 characters" required minlength="8">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white/80 mb-2">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40"></i>
                        <input type="password" name="confirm_password" class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" autocomplete="new-password" placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 rounded-xl transition transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <span class="text-white/60">Already have an account?</span>
                <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium ml-2 transition">Login</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
