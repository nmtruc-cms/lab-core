<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class RequestItemModel extends Model
{
    protected $table      = 'ims_request_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'ims_item_request_list_id',
        'item_name',
        'alternate_name',
        'category_id',
        'description',
        'catalog_no',
        'cas_no',
        'ec_no',
        'grade',
        'unit_price',
        'pack_size',
        'qty',
        'suggested_brand_id',
        'suggested_supplier_id',
        'default_unit_id',
    ];
}
