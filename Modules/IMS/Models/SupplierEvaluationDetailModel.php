<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierEvaluationDetailModel extends Model
{
    protected $table = 'ims_supplier_evaluation_detail';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ims_supplier_evaluation_id',
        'ims_checklist_detail_id',
        'actual_rate',
        'evaluated_by',
    ];
}
