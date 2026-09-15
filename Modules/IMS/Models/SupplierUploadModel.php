<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class SupplierUploadModel extends Model
{
    protected $table = 'ims_supplier_upload';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ims_supplier_id',
        'attachment_type',
        'file_name',
        'file_path',
        'uploaded_by',
    ];
}
