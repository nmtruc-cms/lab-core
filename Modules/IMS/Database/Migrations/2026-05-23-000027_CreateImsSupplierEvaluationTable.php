<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSupplierEvaluationTable extends Migration
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
            'evaluation_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ims_supplier_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ims_checklist_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'evaluated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'total_rate' => [
                'type' => 'FLOAT',
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'locked' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addKey('evaluation_date');
        $this->forge->addKey('ims_supplier_id');
        $this->forge->addKey('ims_checklist_id');
        $this->forge->addKey('evaluated_by');

        $this->forge->addForeignKey('ims_supplier_id', 'ims_suppliers', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('ims_checklist_id', 'ims_checklist', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('evaluated_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('ims_supplier_evaluation', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_supplier_evaluation', true);
    }
}
