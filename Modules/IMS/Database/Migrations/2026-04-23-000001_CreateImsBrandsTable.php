<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsBrandsTable extends Migration
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
            'brand_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('brand_name');
        $this->forge->createTable('ims_brands', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_brands', true);
    }
}
