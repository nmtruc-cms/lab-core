<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSupplierClassificationTable extends Migration
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
            'class_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addUniqueKey('class_name');
        $this->forge->createTable('ims_supplier_classification', true);

        $now = date('Y-m-d H:i:s');
        $classes = [
            'Approved Preferred',
            'Approved',
            'Conditional Approval',
            'Improvement Required',
            'Disqualified',
        ];

        foreach ($classes as $className) {
            $exists = $this->db->table('ims_supplier_classification')
                ->where('class_name', $className)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('ims_supplier_classification')->insert([
                'class_name' => $className,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_supplier_classification', true);
    }
}
