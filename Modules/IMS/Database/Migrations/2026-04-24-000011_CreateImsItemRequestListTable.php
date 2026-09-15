<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsItemRequestListTable extends Migration
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
            'request_number' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'request_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'draft',
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'approved_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'remark' => [
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
        $this->forge->addUniqueKey('request_number', 'uq_ims_item_request_list_number');
        $this->forge->addKey('created_by');
        $this->forge->addKey('approved_by');
        $this->forge->addKey('status');
        $this->forge->addKey('created_date');
        $this->forge->addKey('approved_date');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_item_request_list', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_item_request_list', true);
    }
}
