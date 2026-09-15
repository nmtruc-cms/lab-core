<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsSupplierEvaluationDetailTable extends Migration
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
            'ims_supplier_evaluation_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ims_checklist_detail_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'actual_rate' => [
                'type' => 'FLOAT',
            ],
            'evaluated_by' => [
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
        $this->forge->addKey('ims_supplier_evaluation_id');
        $this->forge->addKey('ims_checklist_detail_id');
        $this->forge->addKey('evaluated_by');

        $this->forge->addForeignKey('ims_supplier_evaluation_id', 'ims_supplier_evaluation', 'id', 'CASCADE', 'CASCADE', 'fk_isevd_eval');
        $this->forge->addForeignKey('ims_checklist_detail_id', 'ims_checklist_detail', 'id', 'RESTRICT', 'CASCADE', 'fk_isevd_check_detail');
        $this->forge->addForeignKey('evaluated_by', 'users', 'id', 'SET NULL', 'CASCADE', 'fk_isevd_evaluated_by');

        $this->forge->createTable('ims_supplier_evaluation_detail', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_supplier_evaluation_detail', true);
    }
}
