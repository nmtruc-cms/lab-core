<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsTransactionsTable extends Migration
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
            'transaction_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'transaction_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'transaction_date' => [
                'type' => 'DATETIME',
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'stock_lot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'qty' => [
                'type' => 'FLOAT',
            ],
            'unit_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'from_location_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'to_location_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'performed_by' => [
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
        $this->forge->addKey('transaction_type');
        $this->forge->addKey('transaction_date');
        $this->forge->addKey('item_id');
        $this->forge->addKey('stock_lot_id');
        $this->forge->addKey('unit_id');
        $this->forge->addKey('from_location_id');
        $this->forge->addKey('to_location_id');
        $this->forge->addKey('performed_by');
        $this->forge->addUniqueKey('transaction_no');
        $this->forge->addForeignKey('item_id', 'ims_item_master', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('stock_lot_id', 'ims_stock_lots', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'ims_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('from_location_id', 'ims_storage_locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_location_id', 'ims_storage_locations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ims_transactions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_transactions', true);
    }
}
