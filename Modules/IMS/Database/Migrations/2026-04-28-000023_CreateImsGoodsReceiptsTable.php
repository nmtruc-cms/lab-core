<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsGoodsReceiptsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'gr_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'purchase_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'confirmed'],
                'default'    => 'draft',
            ],
            'received_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'received_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('gr_number');
        $this->forge->addKey('purchase_order_id');
        $this->forge->addKey('status');
        $this->forge->addKey('received_by');

        $this->forge->addForeignKey('purchase_order_id', 'ims_purchase_orders', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('received_by',       'users',               'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_goods_receipts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_goods_receipts', true);
    }
}
