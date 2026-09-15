<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Modules\IMS\Models\ItemMasterModel;
use Modules\IMS\Models\ItemRequestListModel;
use Modules\IMS\Models\StockLotModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\SupplierModel;
use Modules\IMS\Models\TransactionModel;

class DashboardController extends BaseImsController
{
    public function index(): string
    {
        $db = Database::connect();

        $stats = [
            [
                'label' => lang('IMS.dashboard.stats.items'),
                'value' => model(ItemMasterModel::class)->countAllResults(),
                'accent' => 'navy',
            ],
            [
                'label' => lang('IMS.dashboard.stats.suppliers'),
                'value' => model(SupplierModel::class)->countAllResults(),
                'accent' => 'orange',
            ],
            [
                'label' => lang('IMS.dashboard.stats.storageLocations'),
                'value' => model(StorageLocationModel::class)->countAllResults(),
                'accent' => 'blue',
            ],
            [
                'label' => lang('IMS.dashboard.stats.openRequests'),
                'value' => $db->table('ims_item_request_list')->whereIn('status', ['draft', 'submitted', 'pending'])->countAllResults(),
                'accent' => 'slate',
            ],
        ];

        $recentTransactions = $db->table('ims_transactions it')
            ->select('it.transaction_no, it.transaction_type, it.transaction_date, it.qty, iim.item_name, iu.unit_name')
            ->join('ims_item_master iim', 'iim.id = it.item_id', 'left')
            ->join('ims_units iu', 'iu.id = it.unit_id', 'left')
            ->orderBy('it.transaction_date', 'desc')
            ->get(5)
            ->getResultArray();

        $lotAlerts = $db->table('ims_stock_lots isl')
            ->select('isl.internal_lot_no, isl.expiry_date, isl.current_qty, iim.item_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->where('isl.expiry_date IS NOT NULL', null, false)
            ->orderBy('isl.expiry_date', 'asc')
            ->get(5)
            ->getResultArray();

        $requestSummary = model(ItemRequestListModel::class)
            ->orderBy('created_date', 'desc')
            ->findAll(5);

        return $this->render('Modules\IMS\Views\dashboard\index', [
            'pageTitle' => lang('IMS.dashboard.title'),
            'pageSubtitle' => lang('IMS.dashboard.subtitle'),
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'lotAlerts' => $lotAlerts,
            'requestSummary' => $requestSummary,
        ]);
    }
}
