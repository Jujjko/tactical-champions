<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;
use App\Models\ShopItem;
use App\Models\UserPurchase;
use App\Models\Resource;
use App\Services\AuditService;

class ShopController extends Controller {
    private ShopItem $shopItemModel;
    private UserPurchase $userPurchaseModel;
    private Resource $resourceModel;
    private AuditService $auditService;
    
    public function __construct() {
        $this->shopItemModel = new ShopItem();
        $this->userPurchaseModel = new UserPurchase();
        $this->resourceModel = new Resource();
        $this->auditService = new AuditService();
    }
    
    public function index(): void {
        $userId = Session::userId();
        
        $items = $this->shopItemModel->getActiveItems();
        $featured = $this->shopItemModel->getFeaturedItems();
        $totalSpent = $this->userPurchaseModel->getTotalSpent($userId);
        $resources = $this->resourceModel->getUserResources($userId);
        
        $byCategory = [];
        foreach ($items as $item) {
            $byCategory[$item['category']][] = $item;
        }
        
        $this->view('game/shop', [
            'itemsByCategory' => $byCategory,
            'featuredItems' => $featured,
            'totalSpent' => $totalSpent,
            'resources' => $resources
        ]);
    }
    
    public function purchase(string $id): void {
        $userId = Session::userId();
        $itemId = (int)$id;
        
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request', 403);
            return;
        }
        
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        $item = $this->shopItemModel->findById($itemId);
        
        if (!$item || !$item['is_active']) {
            $this->jsonError('Item not available', 404);
            return;
        }
        
        if ($item['is_limited'] && $item['sold_count'] >= $item['limited_quantity']) {
            $this->jsonError('Item sold out', 400);
            return;
        }
        
        $totalGems = $item['price_gems'] * $quantity;
        $totalGold = $item['price_gold'] * $quantity;
        
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            if ($totalGems > 0 && !$this->resourceModel->deductGems($userId, $totalGems)) {
                $db->rollBack();
                $this->jsonError('Insufficient gems', 400);
                return;
            }
            
            if ($totalGold > 0 && !$this->resourceModel->deductGold($userId, $totalGold)) {
                $db->rollBack();
                if ($totalGems > 0) {
                    $this->resourceModel->addGems($userId, $totalGems);
                }
                $this->jsonError('Insufficient gold', 400);
                return;
            }
            
            $this->applyItem($userId, $item, $quantity);
            
            $this->userPurchaseModel->recordPurchase($userId, $itemId, $item['name'], $quantity, $totalGems, $totalGold);
            
            $this->shopItemModel->incrementSold($itemId);
            
            $this->auditService->logCreate('purchase', $itemId, ['item' => $item['name'], 'quantity' => $quantity]);
            
            $db->commit();
            
            $this->jsonSuccess(['message' => 'Purchase successful!', 'item' => $item['name']]);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonError('Purchase failed', 500);
        }
    }
    
    private function applyItem(int $userId, array $item, int $quantity): void {
        switch ($item['item_type']) {
            case 'gold':
                $this->resourceModel->addGold($userId, $item['item_value'] * $quantity);
                break;
            case 'gems':
                $this->resourceModel->addGems($userId, $item['item_value'] * $quantity);
                break;
            case 'energy':
                $this->resourceModel->addEnergy($userId, $item['item_value'] * $quantity);
                break;
            case 'energy_refill':
                $resources = $this->resourceModel->getUserResources($userId);
                $this->resourceModel->setEnergy($userId, $resources['max_energy']);
                break;
            case 'lootbox_bronze':
            case 'lootbox_silver':
            case 'lootbox_gold':
                $lootboxType = str_replace('lootbox_', '', $item['item_type']);
                for ($i = 0; $i < $quantity; $i++) {
                    $this->resourceModel->addLootbox($userId, $lootboxType);
                }
                break;
        }
    }
}
