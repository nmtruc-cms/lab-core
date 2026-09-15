<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class ChecklistClassificationModel extends Model
{
    protected $table = 'ims_checklist_classification';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ims_checklist_id',
        'rate_min',
        'rate_max',
        'ims_supplier_classification_id',
    ];
}
