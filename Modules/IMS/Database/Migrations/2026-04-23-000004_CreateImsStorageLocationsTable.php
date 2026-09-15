<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsStorageLocationsTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'parent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'temperature_min' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'temperature_max' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'humidity_min' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'humidity_max' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'requires_restricted_access' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('parent_id');
        $this->forge->addUniqueKey('code');
        $this->forge->addForeignKey('parent_id', 'ims_storage_locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_storage_locations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_storage_locations', true);
    }
}
