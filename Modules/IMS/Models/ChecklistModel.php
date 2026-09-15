<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class ChecklistModel extends Model
{
    protected $table = 'ims_checklist';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'checklist_name',
        'created_by',
        'description',
    ];
}
