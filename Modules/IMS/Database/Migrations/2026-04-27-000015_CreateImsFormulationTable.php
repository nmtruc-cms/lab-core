<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsFormulationTable extends Migration
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
            'formulation_lot' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'formulation_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Reagent mixture', 'Stock Solution', 'Working solution', 'Calibrator'],
            ],
            'qty' => [
                'type'       => 'FLOAT',
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'concentration' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'concentration_unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'approved', 'depleted', 'expired'],
                'default'    => 'active',
            ],
            'storage_location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'prepared_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'prepared_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'expired_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'retest_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'retested_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'retest_result' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'retest_comment' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'next_retest_date' => [
                'type' => 'DATE',
                'null' => true,
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
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('formulation_lot');
        $this->forge->addKey('unit_id');
        $this->forge->addKey('concentration_unit_id');
        $this->forge->addKey('status');
        $this->forge->addKey('storage_location_id');
        $this->forge->addKey('prepared_by');
        $this->forge->addKey('approved_by');
        $this->forge->addKey('retested_by');
        $this->forge->addKey('expired_date');
        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');

        $this->forge->addForeignKey('unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('concentration_unit_id', 'ims_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('storage_location_id', 'ims_storage_locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('prepared_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('retested_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_formulation', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_formulation', true);
    }
}
