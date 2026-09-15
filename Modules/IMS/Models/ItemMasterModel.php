<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class ItemMasterModel extends Model
{
    protected $table = 'ims_item_master';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'item_code',
        'item_name',
        'alternate_name',
        'category_id',
        'description',
        'cas_no',
        'ec_no',
        'default_unit_id',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'requires_expiry_tracking',
        'requires_lot_tracking',
        'requires_coa',
        'requires_sds',
        'requires_special_storage',
        'is_controlled_substance',
        'is_flammable',
        'is_corrosive',
        'is_toxic',
        'is_cmr',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];
}
