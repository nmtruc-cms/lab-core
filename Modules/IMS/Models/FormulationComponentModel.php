<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class FormulationComponentModel extends Model
{
    protected $table      = 'ims_formulation_component';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'formulation_id',
        'source_type',
        'source_lot_id',
        'source_formulation_id',
        'used_qty',
        'used_unit_id',
        'concentration',
        'concentration_unit_id',
        'is_solvent',
        'is_markup',
        'sort_order',
        'remark',
        'created_by',
        'updated_by',
    ];
}
