<?php
declare(strict_types=1);

namespace App\Services;

use Core\Config;

class Logger
{
    private string $logPath;
    private string $logLevel;
    private array $levels = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7,
    ];
    
    public function __construct(?string $logPath = null, string $logLevel = 'debug')
    {
        $this->logPath = $logPath ?? Config::get('LOG_PATH', dirname(__DIR__, 3) . '/storage/logs');
        $this->logLevel = Config::get('LOG_LEVEL', $logLevel);
        
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }
    
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }
    
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }
    
    public function log(string $level, string $message, array $context = []): void
    {
        if (!isset($this->levels[$level])) {
            $level = 'info';
        }
        
        if ($this->levels[$level] < $this->levels[$this->logLevel]) {
            return;
        }
        
        $entry = $this->formatEntry($level, $message, $context);
        $filename = $this->logPath . '/' . date('Y-m-d') . '.log';
        
        file_put_contents($filename, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    public function exception(\Throwable $e, array $context = []): void
    {
        $this->error($e->getMessage(), array_merge($context, [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));
    }
    
    private function formatEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s.v');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        
        return sprintf(
            "[%s] %s.%s: %s%s",
            $timestamp,
            strtoupper(substr($level, 0, 4)),
            str_pad(strtoupper($level), 8),
            $message,
            $contextStr
        );
    }
    
    public function getLogs(string $date = null, int $lines = 100): array
    {
        $filename = $this->logPath . '/' . ($date ?? date('Y-m-d')) . '.log';
        
        if (!file_exists($filename)) {
            return [];
        }
        
        $output = [];
        $fp = fopen($filename, 'r');
        
        if ($fp) {
            $lineCount = 0;
            while (!feof($fp) && $lineCount < $lines) {
                $line = fgets($fp);
                if ($line !== false) {
                    $output[] = $this->parseLine(trim($line));
                    $lineCount++;
                }
            }
            fclose($fp);
        }
        
        return array_reverse($output);
    }
    
    private function parseLine(string $line): array
    {
        if (preg_match('/^\[([^\]]+)\]\s+(\w+):\s+(.+?)(?:\s+(\{.*\}))?$/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => $matches[3],
                'context' => isset($matches[4]) ? json_decode($matches[4], true) : [],
                'raw' => $line,
            ];
        }
        
        return ['raw' => $line];
    }
    
    public function getLogFiles(): array
    {
        $files = glob($this->logPath . '/*.log');
        $result = [];
        
        foreach ($files as $file) {
            $result[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
            ];
        }
        
        usort($result, fn($a, $b) => $b['modified'] - $a['modified']);
        
        return $result;
    }
    
    public function clearOldLogs(int $daysToKeep = 30): int
    {
        $files = glob($this->logPath . '/*.log');
        $cutoff = time() - ($daysToKeep * 86400);
        $deleted = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}