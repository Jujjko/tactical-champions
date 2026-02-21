<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ShopItem;
use App\Models\UserPurchase;
use App\Models\Resource;
use App\Services\AuditService;

class ShopController extends Controller {
    private AuditService $auditService;
    
    public function __construct() {
        $this->auditService = new AuditService();
    }
    
    public function index(): void {
        $shopItemModel = new ShopItem();
        $userPurchaseModel = new UserPurchase();
        $resourceModel = new Resource();
        
        $userId = Session::userId();
        
        $items = $shopItemModel->getActiveItems();
        $featured = $shopItemModel->getFeaturedItems();
        $totalSpent = $userPurchaseModel->getTotalSpent($userId);
        $resources = $resourceModel->getUserResources($userId);
        
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
        
        $shopItemModel = new ShopItem();
        $item = $shopItemModel->findById($itemId);
        
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
        
        $resourceModel = new Resource();
        $resources = $resourceModel->getUserResources($userId);
        
        if ($resources['gems'] < $totalGems || $resources['gold'] < $totalGold) {
            $this->jsonError('Insufficient funds', 400);
            return;
        }
        
        if ($totalGems > 0) {
            $resourceModel->deductGems($userId, $totalGems);
        }
        if ($totalGold > 0) {
            $resourceModel->deductGold($userId, $totalGold);
        }
        
        $this->applyItem($userId, $item, $quantity);
        
        $userPurchaseModel = new UserPurchase();
        $userPurchaseModel->recordPurchase($userId, $itemId, $item['name'], $quantity, $totalGems, $totalGold);
        
        $shopItemModel->incrementSold($itemId);
        
        $this->auditService->logCreate('purchase', $itemId, ['item' => $item['name'], 'quantity' => $quantity]);
        
        $this->jsonSuccess(['message' => 'Purchase successful!', 'item' => $item['name']]);
    }
    
    private function applyItem(int $userId, array $item, int $quantity): void {
        $resourceModel = new Resource();
        
        switch ($item['item_type']) {
            case 'gold':
                $resourceModel->addGold($userId, $item['item_value'] * $quantity);
                break;
            case 'gems':
                $resourceModel->addGems($userId, $item['item_value'] * $quantity);
                break;
            case 'energy':
                $resourceModel->addEnergy($userId, $item['item_value'] * $quantity);
                break;
            case 'energy_refill':
                $resources = $resourceModel->getUserResources($userId);
                $resourceModel->setEnergy($userId, $resources['max_energy']);
                break;
            case 'lootbox_bronze':
            case 'lootbox_silver':
            case 'lootbox_gold':
                $lootboxType = str_replace('lootbox_', '', $item['item_type']);
                for ($i = 0; $i < $quantity; $i++) {
                    $resourceModel->addLootbox($userId, $lootboxType);
                }
                break;
        }
    }
}
