<?php
declare(strict_types=1);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class SqlInjectionTest extends TestCase {
    public function testResourceModelUsesPreparedStatements(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Resource.php');
        
        $this->assertStringContainsString('prepare(', $content);
        $this->assertStringContainsString('execute(', $content);
    }
    
    public function testUserModelUsesPreparedStatements(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/User.php');
        
        $this->assertStringContainsString('prepare(', $content);
        $this->assertStringContainsString('execute(', $content);
    }
    
    public function testReferralModelUsesPreparedStatements(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Models/Referral.php');
        
        $this->assertStringContainsString('prepare(', $content);
        $this->assertStringContainsString('execute(', $content);
    }
    
    public function testShopControllerUsesTransaction(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/ShopController.php');
        
        $this->assertStringContainsString('beginTransaction', $content);
        $this->assertStringContainsString('commit', $content);
        $this->assertStringContainsString('rollBack', $content);
    }
    
    public function testNoDirectSqlInControllers(): void {
        $controllerFiles = glob(dirname(__DIR__, 2) . '/app/Controllers/*.php');
        
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);
            
            if ($filename === 'AdminController.php') {
                continue;
            }
            
            $hasDirectQuery = preg_match('/\$db->query\s*\(/', $content);
            $hasDirectExec = preg_match('/\$db->exec\s*\(/', $content);
            
            $this->assertFalse(
                $hasDirectQuery || $hasDirectExec,
                "{$filename} should not use direct query() or exec() - use prepared statements"
            );
        }
    }
    
    public function testInputSanitizationInAdminController(): void {
        $content = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/AdminController.php');
        
        $this->assertStringContainsString('htmlspecialchars', $content);
        $this->assertStringContainsString('(int)', $content);
    }
    
    public function testCsrfValidationInControllers(): void {
        $controllerFiles = glob(dirname(__DIR__, 2) . '/app/Controllers/*.php');
        
        $controllersWithCsrf = 0;
        
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            
            if (str_contains($content, 'validateCsrf') || str_contains($content, 'csrf_token')) {
                $controllersWithCsrf++;
            }
        }
        
        $this->assertGreaterThan(10, $controllersWithCsrf, 'Most controllers should validate CSRF');
    }
}
