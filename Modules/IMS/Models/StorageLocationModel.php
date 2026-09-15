<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class StorageLocationModel extends Model
{
    protected $table = 'ims_storage_locations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'code',
        'parent_id',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'requires_restricted_access',
        'is_active',
        'description',
    ];
}
