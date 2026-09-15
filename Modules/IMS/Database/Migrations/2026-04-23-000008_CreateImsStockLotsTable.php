<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsStockLotsTable extends Migration
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
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'lot_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'supplier_lot_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'internal_lot_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'serial_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'received_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'manufacture_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'opened_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'opened_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'retest_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'retest_result' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'retest_comment' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'supplier_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'catalog_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'grade' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'brand_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'pack_size' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'initial_qty' => [
                'type' => 'FLOAT',
            ],
            'initial_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'current_qty' => [
                'type' => 'FLOAT',
            ],
            'current_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'concentration_value' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'concentration_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'purity_value' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'storage_location_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'ownership_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'owned',
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
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('supplier_id');
        $this->forge->addKey('brand_id');
        $this->forge->addKey('initial_unit_id');
        $this->forge->addKey('current_unit_id');
        $this->forge->addKey('concentration_unit_id');
        $this->forge->addKey('storage_location_id');
        $this->forge->addKey('opened_by');
        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');
        $this->forge->addKey('ownership_status');
        $this->forge->addKey('expiry_date');
        $this->forge->addUniqueKey('internal_lot_no');
        $this->forge->addForeignKey('item_id', 'ims_item_master', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'ims_suppliers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('brand_id', 'ims_brands', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('initial_unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('current_unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('concentration_unit_id', 'ims_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('storage_location_id', 'ims_storage_locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('opened_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_stock_lots', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_stock_lots', true);
    }
}
