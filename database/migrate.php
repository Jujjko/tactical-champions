<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Database;
use Core\Config;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Migrator {
    private PDO $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->migrationsPath = __DIR__ . '/migrations';
        $this->createMigrationsTable();
    }
    
    public function run(): void {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "No pending migrations.\n";
            return;
        }
        
        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
        
        echo "Migration complete. " . count($migrations) . " migrations executed.\n";
    }
    
    public function rollback(int $steps = 1): void {
        $batch = $this->getLastBatch();
        
        if (empty($batch)) {
            echo "Nothing to rollback.\n";
            return;
        }
        
        $toRollback = array_slice(array_reverse($batch), 0, $steps);
        
        foreach ($toRollback as $migration) {
            $this->rollbackMigration($migration);
        }
        
        echo "Rollback complete. " . count($toRollback) . " migrations rolled back.\n";
    }
    
    public function status(): void {
        $ran = $this->getRanMigrations();
        $all = $this->getAllMigrationFiles();
        
        echo "Migration Status:\n";
        echo str_repeat('-', 60) . "\n";
        
        foreach ($all as $file) {
            $name = basename($file);
            $status = in_array($name, $ran) ? '✓ RAN' : '○ PENDING';
            echo sprintf("%-50s %s\n", $name, $status);
        }
    }
    
    private function createMigrationsTable(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $this->db->exec($sql);
        } catch (PDOException $e) {
            echo "Warning: Could not create migrations table: " . $e->getMessage() . "\n";
        }
    }
    
    private function getPendingMigrations(): array {
        $ran = $this->getRanMigrations();
        $all = $this->getAllMigrationFiles();
        
        $pending = [];
        foreach ($all as $file) {
            $name = basename($file);
            if (!in_array($name, $ran)) {
                $pending[] = $file;
            }
        }
        
        return $pending;
    }
    
    private function getRanMigrations(): array {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getAllMigrationFiles(): array {
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);
        return $files;
    }
    
    private function runMigration(string $file): void {
        $name = basename($file);
        $sql = file_get_contents($file);
        
        echo "Running: {$name}\n";
        
        try {
            $this->db->exec($sql);
            
            $batch = $this->getNextBatchNumber();
            $stmt = $this->db->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
            $stmt->execute([$name, $batch]);
            
            echo "  ✓ Success\n";
        } catch (PDOException $e) {
            echo "  ✗ Failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function rollbackMigration(string $migration): void {
        $file = $this->migrationsPath . '/' . $migration;
        
        if (!file_exists($file)) {
            echo "Migration file not found: {$migration}\n";
            return;
        }
        
        echo "Rolling back: {$migration}\n";
        
        $content = file_get_contents($file);
        
        if (preg_match('/-- DOWN\s+(.*)/s', $content, $matches)) {
            $downSql = trim($matches[1]);
            if (!empty($downSql)) {
                try {
                    $this->db->exec($downSql);
                } catch (PDOException $e) {
                    echo "  Warning: " . $e->getMessage() . "\n";
                }
            }
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
        
        echo "  ✓ Rolled back\n";
    }
    
    private function getLastBatch(): array {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} WHERE batch = (SELECT MAX(batch) FROM {$this->migrationsTable}) ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getNextBatchNumber(): int {
        try {
            $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
            $max = $stmt->fetchColumn();
            return ($max ?? 0) + 1;
        } catch (PDOException $e) {
            return 1;
        }
    }
}

// CLI handler
$command = $argv[1] ?? 'run';
$migrator = new Migrator();

match ($command) {
    'run', 'migrate' => $migrator->run(),
    'rollback' => $migrator->rollback((int)($argv[2] ?? 1)),
    'status' => $migrator->status(),
    default => print("Usage: php migrate.php [run|rollback|status]\n")
};