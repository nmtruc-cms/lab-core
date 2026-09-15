<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table = 'ims_brands';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'brand_name',
        'country',
        'website',
        'logo',
        'is_active',
    ];
}
