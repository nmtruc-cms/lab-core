<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'ims_suppliers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'supplier_name',
        'supplier_code',
        'address',
        'vat_code',
        'phone',
        'email',
        'contact_name',
        'approved_status',
        'is_active',
    ];
}
