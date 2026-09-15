<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class ItemRequestListModel extends Model
{
    protected $table = 'ims_item_request_list';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'request_number',
        'request_name',
        'created_date',
        'created_by',
        'status',
        'approved_by',
        'approved_date',
        'remark',
    ];
}
