<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use CodeIgniter\Model;

class CompanyProfileModel extends Model
{
    protected $table = 'company_profile';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'name_header',
        'name',
        'address',
        'vat_code',
        'email',
        'logo',
        'phone',
        'web',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
