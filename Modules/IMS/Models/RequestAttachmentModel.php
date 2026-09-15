<?php

declare(strict_types=1);

namespace Modules\IMS\Models;

use CodeIgniter\Model;

class RequestAttachmentModel extends Model
{
    protected $table      = 'ims_request_attachments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'ims_item_request_list_id',
        'attachment_type',
        'file_name',
        'file_path',
        'remarks',
    ];
}
