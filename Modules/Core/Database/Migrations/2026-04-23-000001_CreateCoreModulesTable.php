<?php

declare(strict_types=1);

namespace Modules\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreModulesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'installed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('code', true);
        $this->forge->createTable('core_modules', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('core_modules', true);
    }
}
