<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class PurchaseOrderItemModel extends Model
{
    protected $table      = 'ims_purchase_order_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'purchase_order_id',
        'request_item_id',
        'item_id',
        'item_name',
        'catalog_no',
        'cas_no',
        'grade',
        'pack_size',
        'brand_id',
        'qty_ordered',
        'unit_id',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'line_total',
        'qty_received',
        'notes',
    ];
}
