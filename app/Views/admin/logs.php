<?php ob_start(); ?>
<div class="min-h-screen bg-[#0a0818] py-12 pt-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="title-font text-4xl font-bold">System Logs</h1>
                <p class="text-white/60 mt-2">Application logs and errors</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition">Back to Admin</a>
        </div>

        <!-- Log Files -->
        <div class="glass rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Log Files</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($files as $file): ?>
                <a href="?date=<?= str_replace('.log', '', $file['name']) ?>" 
                   class="px-4 py-2 rounded-xl transition <?= $selectedDate === str_replace('.log', '', $file['name']) ? 'bg-indigo-500' : 'bg-white/10 hover:bg-white/20' ?>">
                    <?= $file['name'] ?>
                    <span class="text-xs text-white/40 ml-2"><?= round($file['size'] / 1024, 1) ?>KB</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Log Entries -->
        <div class="glass rounded-2xl overflow-hidden">
            <?php if (empty($logs)): ?>
            <div class="p-12 text-center text-white/60">
                No logs found for <?= $selectedDate ?? 'today' ?>
            </div>
            <?php else: ?>
            <div class="max-h-[600px] overflow-y-auto">
                <?php foreach ($logs as $log): ?>
                <div class="border-b border-white/5 p-4 hover:bg-white/5">
                    <?php if (isset($log['level'])): ?>
                    <div class="flex items-start gap-4">
                        <div class="w-20 shrink-0">
                            <span class="px-2 py-1 rounded text-xs font-mono
                                <?= $log['level'] === 'ERRO' ? 'bg-red-500/20 text-red-400' : '' ?>
                                <?= $log['level'] === 'WARN' ? 'bg-yellow-500/20 text-yellow-400' : '' ?>
                                <?= $log['level'] === 'INFO' ? 'bg-blue-500/20 text-blue-400' : '' ?>
                                <?= $log['level'] === 'DEBU' ? 'bg-gray-500/20 text-gray-400' : '' ?>
                            ">
                                <?= $log['level'] ?>
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm text-white/60 mb-1"><?= $log['timestamp'] ?></div>
                            <div class="font-mono text-sm"><?= htmlspecialchars($log['message'] ?? '') ?></div>
                            <?php if (!empty($log['context'])): ?>
                            <details class="mt-2">
                                <summary class="text-xs text-white/40 cursor-pointer hover:text-white/60">Context</summary>
                                <pre class="text-xs text-white/60 mt-2 bg-black/20 p-2 rounded overflow-x-auto"><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT)) ?></pre>
                            </details>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="font-mono text-sm text-white/60"><?= htmlspecialchars($log['raw']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';