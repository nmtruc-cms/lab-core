<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierEvaluationModel extends Model
{
    protected $table = 'ims_supplier_evaluation';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'evaluation_date',
        'ims_supplier_id',
        'ims_checklist_id',
        'evaluated_by',
        'total_rate',
        'comment',
        'locked',
    ];
}
