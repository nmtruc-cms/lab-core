<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSupplierUploadTable extends Migration
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
            'ims_supplier_id' => [
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
            'uploaded_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addKey('ims_supplier_id');
        $this->forge->addKey('attachment_type');
        $this->forge->addKey('uploaded_by');

        $this->forge->addForeignKey('ims_supplier_id', 'ims_suppliers', 'id', 'CASCADE', 'CASCADE', 'fk_ims_supplier_upload_supplier');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'SET NULL', 'CASCADE', 'fk_ims_supplier_upload_user');

        $this->forge->createTable('ims_supplier_upload', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_supplier_upload', true);
    }
}
