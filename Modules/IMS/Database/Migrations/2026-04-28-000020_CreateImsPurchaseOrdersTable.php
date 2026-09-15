<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsPurchaseOrdersTable extends Migration
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
            'po_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'approved', 'ordered', 'partially_received', 'received', 'cancelled'],
                'default'    => 'draft',
            ],
            'order_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expected_delivery_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'subtotal' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'tax_amount' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'shipping_cost' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'grand_total' => [
                'type'       => 'FLOAT',
                'default'    => '0.0000',
            ],
            'payment_terms' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'delivery_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_date' => [
                'type' => 'DATETIME',
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
        $this->forge->addUniqueKey('po_number');
        $this->forge->addKey('supplier_id');
        $this->forge->addKey('status');
        $this->forge->addKey('order_date');
        $this->forge->addKey('created_by');
        $this->forge->addKey('approved_by');

        $this->forge->addForeignKey('supplier_id', 'ims_suppliers', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('created_by',  'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_purchase_orders', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_purchase_orders', true);
    }
}
