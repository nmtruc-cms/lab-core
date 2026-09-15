<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsGoodsReceiptItemsTable extends Migration
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
            'goods_receipt_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'po_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'qty_received' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'lot_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'storage_location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'linked_stock_lot_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('goods_receipt_id');
        $this->forge->addKey('po_item_id');
        $this->forge->addKey('unit_id');
        $this->forge->addKey('storage_location_id');
        $this->forge->addKey('linked_stock_lot_id');

        $this->forge->addForeignKey('goods_receipt_id',    'ims_goods_receipts',        'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('po_item_id',          'ims_purchase_order_items',   'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('unit_id',             'ims_units',                  'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('storage_location_id', 'ims_storage_locations',      'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('linked_stock_lot_id', 'ims_stock_lots',             'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_goods_receipt_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_goods_receipt_items', true);
    }
}
