<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierBrandModel extends Model
{
    protected $table = 'ims_supplier_brand';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'supplier_id',
        'brand_id',
    ];
}
