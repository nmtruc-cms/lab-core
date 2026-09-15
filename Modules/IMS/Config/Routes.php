<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('ims', [
    'namespace' => 'Modules\IMS\Controllers',
    'filter'    => ['module:ims', 'session'],
], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index', ['filter' => 'permission:ims.access']);
    $routes->get('master-data', 'CatalogController::masterData', ['filter' => 'permission:ims.items.view']);
    $routes->post('master-data/brands', 'CatalogController::createBrand', ['filter' => 'permission:ims.items.create']);
    $routes->post('master-data/brands/(:num)', 'CatalogController::updateBrand/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/brands/(:num)/delete', 'CatalogController::deleteBrand/$1', ['filter' => 'permission:ims.items.delete']);
    $routes->post('master-data/categories', 'CatalogController::createCategory', ['filter' => 'permission:ims.items.create']);
    $routes->post('master-data/categories/(:num)', 'CatalogController::updateCategory/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/categories/(:num)/delete', 'CatalogController::deleteCategory/$1', ['filter' => 'permission:ims.items.delete']);
    $routes->post('master-data/units', 'CatalogController::createUnit', ['filter' => 'permission:ims.items.create']);
    $routes->post('master-data/units/(:num)', 'CatalogController::updateUnit/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/units/(:num)/delete', 'CatalogController::deleteUnit/$1', ['filter' => 'permission:ims.items.delete']);
    $routes->post('master-data/checklists', 'CatalogController::createChecklist', ['filter' => 'permission:ims.items.create']);
    $routes->post('master-data/checklists/(:num)', 'CatalogController::updateChecklist/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/checklists/(:num)/delete', 'CatalogController::deleteChecklist/$1', ['filter' => 'permission:ims.items.delete']);
    $routes->post('master-data/checklists/(:num)/classifications', 'CatalogController::updateChecklistClassifications/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/checklists/(:num)/details', 'CatalogController::createChecklistDetail/$1', ['filter' => 'permission:ims.items.create']);
    $routes->post('master-data/checklists/(:num)/details/(:num)', 'CatalogController::updateChecklistDetail/$1/$2', ['filter' => 'permission:ims.items.update']);
    $routes->post('master-data/checklists/(:num)/details/(:num)/delete', 'CatalogController::deleteChecklistDetail/$1/$2', ['filter' => 'permission:ims.items.delete']);
    $routes->post('master-data/supplier-classifications/(:num)', 'CatalogController::updateSupplierClassification/$1', ['filter' => 'permission:ims.items.update']);
    $routes->get('items', 'ItemController::index', ['filter' => 'permission:ims.items.view']);
    $routes->get('items/create', 'ItemController::form', ['filter' => 'permission:ims.items.create']);
    $routes->get('items/(:num)/edit', 'ItemController::form/$1', ['filter' => 'permission:ims.items.update']);
    $routes->get('items/(:num)', 'ItemController::detail/$1', ['filter' => 'permission:ims.items.view']);
    $routes->post('items', 'ItemController::create', ['filter' => 'permission:ims.items.create']);
    $routes->post('items/(:num)', 'ItemController::update/$1', ['filter' => 'permission:ims.items.update']);
    $routes->post('items/(:num)/delete', 'ItemController::delete/$1', ['filter' => 'permission:ims.items.delete']);
    $routes->get('items/(:num)/attachments/(:num)', 'ItemController::serveAttachment/$1/$2', ['filter' => 'permission:ims.items.view']);
    $routes->post('items/(:num)/attachments', 'ItemController::uploadAttachment/$1', ['filter' => 'permission:ims.items.update']);
    $routes->get('items/(:num)/lots/create', 'ItemController::lotForm/$1', ['filter' => 'permission:ims.stock.receive']);
    $routes->get('items/(:num)/lots/(:num)/edit', 'ItemController::lotForm/$1/$2', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('items/(:num)/lots', 'ItemController::createLot/$1', ['filter' => 'permission:ims.stock.receive']);
    $routes->post('items/(:num)/lots/(:num)', 'ItemController::updateLot/$1/$2', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('items/(:num)/lots/(:num)/delete', 'ItemController::deleteLot/$1/$2', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('suppliers', 'SupplierController::index', ['filter' => 'permission:ims.suppliers.view']);
    $routes->get('suppliers/template', 'SupplierController::downloadTemplate', ['filter' => 'permission:ims.suppliers.view']);
    $routes->get('suppliers/(:num)', 'SupplierController::detail/$1', ['filter' => 'permission:ims.suppliers.view']);
    $routes->post('suppliers', 'SupplierController::create', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)', 'SupplierController::update/$1', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/delete', 'SupplierController::delete/$1', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/brand-mapping', 'SupplierController::syncBrands/$1', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/evaluations', 'SupplierController::createEvaluation/$1', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/evaluations/(:num)/confirm', 'SupplierController::confirmEvaluation/$1/$2', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/evaluations/(:num)/delete', 'SupplierController::deleteEvaluation/$1/$2', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->get('suppliers/(:num)/documents/(:num)', 'SupplierController::serveDocument/$1/$2', ['filter' => 'permission:ims.suppliers.view']);
    $routes->post('suppliers/(:num)/documents', 'SupplierController::uploadDocument/$1', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/documents/(:num)', 'SupplierController::updateDocument/$1/$2', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/(:num)/documents/(:num)/delete', 'SupplierController::deleteDocument/$1/$2', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->post('suppliers/import', 'SupplierController::import', ['filter' => 'permission:ims.suppliers.manage']);
    $routes->get('locations', 'CatalogController::locations', ['filter' => 'permission:ims.warehouses.view']);
    $routes->post('locations', 'CatalogController::createLocation', ['filter' => 'permission:ims.warehouses.manage']);
    $routes->post('locations/(:num)', 'CatalogController::updateLocation/$1', ['filter' => 'permission:ims.warehouses.manage']);
    $routes->post('locations/(:num)/delete', 'CatalogController::deleteLocation/$1', ['filter' => 'permission:ims.warehouses.manage']);
    $routes->get('lots', 'LotController::index', ['filter' => 'permission:ims.stock.view']);
    $routes->get('lots/(:num)/label', 'LotController::label/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->get('lots/(:num)', 'LotController::detail/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->get('lots/(:num)/attachments/(:num)', 'LotController::serveAttachment/$1/$2', ['filter' => 'permission:ims.stock.view']);
    $routes->post('lots/(:num)/attachments', 'LotController::uploadAttachment/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('lots/(:num)/transactions', 'LotController::createTransaction/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('transactions', 'TransactionController::index', ['filter' => 'permission:ims.stock.view']);
    $routes->post('transactions', 'TransactionController::create', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('transactions/(:num)', 'TransactionController::update/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('transactions/(:num)/delete', 'TransactionController::delete/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('purchase-orders', 'PurchaseOrderController::index', ['filter' => 'permission:ims.stock.view']);
    $routes->get('purchase-orders/(:num)/pdf', 'PurchaseOrderController::pdf/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->get('purchase-orders/(:num)', 'PurchaseOrderController::detail/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->post('purchase-orders', 'PurchaseOrderController::create', ['filter' => 'permission:ims.stock.receive']);
    $routes->post('purchase-orders/(:num)', 'PurchaseOrderController::update/$1', ['filter' => 'permission:ims.stock.receive']);
    $routes->post('purchase-orders/(:num)/delete', 'PurchaseOrderController::delete/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('purchase-orders/(:num)/approve', 'PurchaseOrderController::approve/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('purchase-orders/(:num)/order', 'PurchaseOrderController::markOrdered/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('purchase-orders/(:num)/cancel', 'PurchaseOrderController::cancel/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('purchase-orders/(:num)/items', 'PurchaseOrderController::addItem/$1', ['filter' => 'permission:ims.stock.receive']);
    $routes->post('purchase-orders/(:num)/items/(:num)', 'PurchaseOrderController::updateItem/$1/$2', ['filter' => 'permission:ims.stock.receive']);
    $routes->post('purchase-orders/(:num)/items/(:num)/delete', 'PurchaseOrderController::deleteItem/$1/$2', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('purchase-orders/(:num)/attachments/(:num)', 'PurchaseOrderController::serveAttachment/$1/$2', ['filter' => 'permission:ims.stock.view']);
    $routes->post('purchase-orders/(:num)/attachments', 'PurchaseOrderController::uploadAttachment/$1', ['filter' => 'permission:ims.stock.receive']);
    $routes->get('requests', 'CatalogController::requests', ['filter' => 'permission:ims.requests.view']);
    $routes->get('requests/(:num)', 'CatalogController::requestDetail/$1', ['filter' => 'permission:ims.requests.view']);
    $routes->post('requests', 'CatalogController::createRequest', ['filter' => 'permission:ims.requests.create']);
    $routes->post('requests/(:num)', 'CatalogController::updateRequest/$1', ['filter' => 'permission:ims.requests.update']);
    $routes->post('requests/(:num)/delete', 'CatalogController::deleteRequest/$1', ['filter' => 'permission:ims.requests.delete']);
    $routes->post('requests/(:num)/submit', 'CatalogController::submitRequest/$1', ['filter' => 'permission:ims.requests.submit']);
    $routes->post('requests/(:num)/approve', 'CatalogController::approveRequest/$1', ['filter' => 'permission:ims.requests.approve']);
    $routes->post('requests/(:num)/reject', 'CatalogController::rejectRequest/$1', ['filter' => 'permission:ims.requests.reject']);
    $routes->post('requests/(:num)/items', 'CatalogController::addRequestItem/$1', ['filter' => 'permission:ims.requests.update']);
    $routes->post('requests/(:num)/items/(:num)', 'CatalogController::updateRequestItem/$1/$2', ['filter' => 'permission:ims.requests.update']);
    $routes->post('requests/(:num)/items/(:num)/delete', 'CatalogController::deleteRequestItem/$1/$2', ['filter' => 'permission:ims.requests.update']);
    $routes->post('requests/(:num)/attachments', 'CatalogController::uploadRequestAttachment/$1', ['filter' => 'permission:ims.requests.update']);
    $routes->post('requests/(:num)/attachments/(:num)/delete', 'CatalogController::deleteRequestAttachment/$1/$2', ['filter' => 'permission:ims.requests.update']);
    $routes->get('formulations', 'FormulationController::index', ['filter' => 'permission:ims.stock.view']);
    $routes->get('formulations/create', 'FormulationController::form', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('formulations/(:num)/edit', 'FormulationController::form/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('formulations/(:num)/label', 'FormulationController::label/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->get('formulations/(:num)/label-3x2', 'FormulationController::label3x2/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->get('formulations/(:num)', 'FormulationController::detail/$1', ['filter' => 'permission:ims.stock.view']);
    $routes->post('formulations', 'FormulationController::create', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('formulations/(:num)', 'FormulationController::update/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('formulations/(:num)/delete', 'FormulationController::delete/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->get('formulations/(:num)/attachments/(:num)', 'FormulationController::serveAttachment/$1/$2', ['filter' => 'permission:ims.stock.view']);
    $routes->post('formulations/(:num)/attachments', 'FormulationController::uploadAttachment/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('formulations/(:num)/components', 'FormulationController::addComponent/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('formulations/(:num)/components/batch', 'FormulationController::batchUpdateComponents/$1', ['filter' => 'permission:ims.stock.adjust']);
    $routes->post('formulations/(:num)/components/(:num)/delete', 'FormulationController::deleteComponent/$1/$2', ['filter' => 'permission:ims.stock.adjust']);
});
