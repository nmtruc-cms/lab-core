<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSupplierBrandTable extends Migration
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
            'supplier_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'brand_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addKey('supplier_id');
        $this->forge->addKey('brand_id');
        $this->forge->addUniqueKey(['supplier_id', 'brand_id']);
        $this->forge->addForeignKey('supplier_id', 'ims_suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('brand_id', 'ims_brands', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ims_supplier_brand', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_supplier_brand', true);
    }
}
