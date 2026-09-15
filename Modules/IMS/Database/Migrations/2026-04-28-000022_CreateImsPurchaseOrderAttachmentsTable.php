<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsPurchaseOrderAttachmentsTable extends Migration
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
            'purchase_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'attachment_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('purchase_order_id');
        $this->forge->addKey('attachment_type');
        $this->forge->addKey('uploaded_by');

        $this->forge->addForeignKey('purchase_order_id', 'ims_purchase_orders', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('uploaded_by',       'users',               'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_purchase_order_attachments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_purchase_order_attachments', true);
    }
}
