<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use CodeIgniter\Model;

class ModuleRegistryModel extends Model
{
    protected $table = 'core_modules';
    protected $primaryKey = 'code';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'code',
        'name',
        'enabled',
        'version',
        'installed_at',
        'updated_at',
    ];
    protected $useTimestamps = false;
}
