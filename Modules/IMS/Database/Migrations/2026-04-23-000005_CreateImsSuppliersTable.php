<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSuppliersTable extends Migration
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
            'supplier_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'supplier_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'vat_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'contact_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'approved_status' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('supplier_code');
        $this->forge->addUniqueKey('supplier_name');
        $this->forge->addKey('approved_status');
        $this->forge->addForeignKey('approved_status', 'ims_supplier_classification', 'id', 'CASCADE', 'SET NULL', 'fk_ims_suppliers_approved_status');
        $this->forge->createTable('ims_suppliers', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_suppliers', true);
    }
}
