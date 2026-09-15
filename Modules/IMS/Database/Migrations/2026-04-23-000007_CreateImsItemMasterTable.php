<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsItemMasterTable extends Migration
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
            'item_code' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'item_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'alternate_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'category_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cas_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ec_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'default_unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'min_stock_level' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'max_stock_level' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'reorder_level' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'requires_expiry_tracking' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'requires_lot_tracking' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'requires_coa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'requires_sds' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'requires_special_storage' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_controlled_substance' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_flammable' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_corrosive' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_toxic' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_cmr' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'active',
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
        $this->forge->addKey('category_id');
        $this->forge->addKey('default_unit_id');
        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');
        $this->forge->addKey('status');
        $this->forge->addKey('is_active');
        $this->forge->addUniqueKey('item_code');
        $this->forge->addForeignKey('category_id', 'ims_categories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('default_unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_item_master', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_item_master', true);
    }
}
