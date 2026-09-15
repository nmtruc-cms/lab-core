<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierClassificationModel extends Model
{
    protected $table = 'ims_supplier_classification';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'class_name',
    ];
}
