<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsRequestAttachmentsTable extends Migration
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
            'ims_item_request_list_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'attachment_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'remarks' => [
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
        $this->forge->addKey('ims_item_request_list_id');
        $this->forge->addKey('attachment_type');
        $this->forge->addForeignKey('ims_item_request_list_id', 'ims_item_request_list', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ims_request_attachments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_request_attachments', true);
    }
}
