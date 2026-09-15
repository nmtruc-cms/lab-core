<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use CodeIgniter\Model;

class UserDepartmentModel extends Model
{
    protected $table = 'core_user_departments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'department_id',
        'is_primary',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
