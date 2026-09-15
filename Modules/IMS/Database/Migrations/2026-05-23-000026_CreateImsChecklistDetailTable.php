<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsChecklistDetailTable extends Migration
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
            'ims_checklist_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'check_item' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'weight' => [
                'type' => 'FLOAT',
            ],
            'description' => [
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
        $this->forge->addKey('ims_checklist_id');
        $this->forge->addForeignKey('ims_checklist_id', 'ims_checklist', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ims_checklist_detail', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_checklist_detail', true);
    }
}
