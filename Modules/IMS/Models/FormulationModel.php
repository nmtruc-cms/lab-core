<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class FormulationModel extends Model
{
    protected $table      = 'ims_formulation';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'formulation_lot',
        'name',
        'formulation_type',
        'qty',
        'unit_id',
        'concentration',
        'concentration_unit_id',
        'status',
        'storage_location_id',
        'prepared_date',
        'prepared_by',
        'approved_date',
        'approved_by',
        'expired_date',
        'retest_date',
        'retested_by',
        'retest_result',
        'retest_comment',
        'next_retest_date',
        'description',
        'created_by',
        'updated_by',
    ];
}
