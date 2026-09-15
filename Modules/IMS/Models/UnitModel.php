<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table = 'ims_units';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'unit_name',
        'unit_type',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];
}
