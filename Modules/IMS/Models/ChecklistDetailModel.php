<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class ChecklistDetailModel extends Model
{
    protected $table = 'ims_checklist_detail';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ims_checklist_id',
        'check_item',
        'weight',
        'description',
    ];
}
