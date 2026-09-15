<?php

declare(strict_types=1);

namespace Modules\Core\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'username',
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'first_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['first_name', 'last_name']);
    }
}
