<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsPurchaseOrderItemsTable extends Migration
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
            'purchase_order_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'request_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'catalog_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cas_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'grade' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'pack_size' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'brand_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'qty_ordered' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'unit_price' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'discount_percent' => [
                'type'       => 'FLOAT',
                'default'    => '0.00',
            ],
            'tax_rate' => [
                'type'       => 'FLOAT',
                'default'    => '0.00',
            ],
            'line_total' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'qty_received' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'notes' => [
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
        $this->forge->addKey('purchase_order_id');
        $this->forge->addKey('request_item_id');
        $this->forge->addKey('item_id');
        $this->forge->addKey('brand_id');
        $this->forge->addKey('unit_id');

        $this->forge->addForeignKey('purchase_order_id', 'ims_purchase_orders', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('request_item_id',   'ims_request_items',   'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('item_id',           'ims_item_master',      'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('brand_id',          'ims_brands',           'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('unit_id',           'ims_units',            'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_purchase_order_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_purchase_order_items', true);
    }
}
