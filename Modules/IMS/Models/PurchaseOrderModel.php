<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table      = 'ims_purchase_orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'po_number',
        'supplier_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        'payment_terms',
        'delivery_address',
        'notes',
        'created_by',
        'approved_by',
        'approved_date',
    ];
}
