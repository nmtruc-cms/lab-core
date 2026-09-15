<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class StockLotModel extends Model
{
    protected $table = 'ims_stock_lots';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'item_id',
        'lot_no',
        'supplier_lot_no',
        'internal_lot_no',
        'serial_no',
        'type',
        'received_date',
        'manufacture_date',
        'expiry_date',
        'opened_date',
        'opened_by',
        'retest_date',
        'retest_result',
        'retest_comment',
        'supplier_id',
        'catalog_no',
        'grade',
        'brand_id',
        'pack_size',
        'initial_qty',
        'initial_unit_id',
        'current_qty',
        'current_unit_id',
        'concentration_value',
        'concentration_unit_id',
        'purity_value',
        'storage_location_id',
        'ownership_status',
        'remarks',
        'created_by',
        'updated_by',
    ];
}
