<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsUnitsTable extends Migration
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
            'unit_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'unit_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'base_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'conversion_factor' => [
                'type' => 'FLOAT',
                'default' => 1,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('base_unit_id');
        $this->forge->addUniqueKey(['unit_name', 'unit_type']);
        $this->forge->addForeignKey('base_unit_id', 'ims_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_units', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_units', true);
    }
}
