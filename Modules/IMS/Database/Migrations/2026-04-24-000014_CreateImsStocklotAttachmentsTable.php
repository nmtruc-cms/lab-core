<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsStocklotAttachmentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ims_stock_lot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'attachment_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'uploaded_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('ims_stock_lot_id');
        $this->forge->addKey('attachment_type');
        $this->forge->addKey('uploaded_by');
        $this->forge->addKey('uploaded_at');
        $this->forge->addForeignKey('ims_stock_lot_id', 'ims_stock_lots', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_stocklot_attachments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_stocklot_attachments', true);
    }
}
