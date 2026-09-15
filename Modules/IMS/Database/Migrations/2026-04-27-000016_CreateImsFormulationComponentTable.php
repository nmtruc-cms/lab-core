<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsFormulationComponentTable extends Migration
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
            'formulation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'source_type' => [
                'type'       => 'ENUM',
                'constraint' => ['stock_lot', 'formulation'],
            ],
            'source_lot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'source_formulation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'used_qty' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'used_unit_id' => [
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
            'is_solvent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_markup' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'remark' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
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
        $this->forge->addKey('formulation_id');
        $this->forge->addKey('source_type');
        $this->forge->addKey('source_lot_id');
        $this->forge->addKey('source_formulation_id');
        $this->forge->addKey('used_unit_id');
        $this->forge->addKey('concentration_unit_id');
        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');

        $this->forge->addForeignKey('formulation_id', 'ims_formulation', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('source_lot_id', 'ims_stock_lots', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('source_formulation_id', 'ims_formulation', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('used_unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('concentration_unit_id', 'ims_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_formulation_component', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_formulation_component', true);
    }
}
