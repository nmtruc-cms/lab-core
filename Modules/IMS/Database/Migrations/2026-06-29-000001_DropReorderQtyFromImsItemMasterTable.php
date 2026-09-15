<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropReorderQtyFromImsItemMasterTable extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('reorder_qty', 'ims_item_master')) {
            $this->forge->dropColumn('ims_item_master', 'reorder_qty');
        }
    }

    public function down(): void
    {
        if (! $this->db->fieldExists('reorder_qty', 'ims_item_master')) {
            $this->forge->addColumn('ims_item_master', [
                'reorder_qty' => [
                    'type' => 'FLOAT',
                    'null' => true,
                    'after' => 'reorder_level',
                ],
            ]);
        }
    }
}
