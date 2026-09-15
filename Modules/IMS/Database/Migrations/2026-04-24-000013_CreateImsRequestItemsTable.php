<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsRequestItemsTable extends Migration
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
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'catalog_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
            'grade' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'unit_price' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'pack_size' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'qty' => [
                'type' => 'FLOAT',
                'null' => true,
            ],
            'suggested_brand_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'suggested_supplier_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'default_unit_id' => [
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
        $this->forge->addKey('ims_item_request_list_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('suggested_brand_id');
        $this->forge->addKey('suggested_supplier_id');
        $this->forge->addKey('default_unit_id');
        $this->forge->addForeignKey('ims_item_request_list_id', 'ims_item_request_list', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'ims_categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('suggested_brand_id', 'ims_brands', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('suggested_supplier_id', 'ims_suppliers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('default_unit_id', 'ims_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_request_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_request_items', true);
    }
}
