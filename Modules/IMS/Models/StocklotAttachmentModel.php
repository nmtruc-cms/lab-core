<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class StocklotAttachmentModel extends Model
{
    protected $table = 'ims_stocklot_attachments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ims_stock_lot_id',
        'attachment_type',
        'file_name',
        'file_path',
        'remarks',
        'uploaded_by',
        'uploaded_at',
    ];
}
