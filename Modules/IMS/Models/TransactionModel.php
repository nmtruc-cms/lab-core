<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'ims_transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'transaction_no',
        'transaction_type',
        'transaction_date',
        'item_id',
        'stock_lot_id',
        'qty',
        'unit_id',
        'from_location_id',
        'to_location_id',
        'reason',
        'performed_by',
    ];
}
