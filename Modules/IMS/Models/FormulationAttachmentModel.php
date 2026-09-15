<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class FormulationAttachmentModel extends Model
{
    protected $table      = 'ims_formulation_attachments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'formulation_id',
        'attachment_type',
        'file_name',
        'file_path',
        'remarks',
        'uploaded_by',
        'uploaded_at',
    ];
}
