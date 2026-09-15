<?php

declare(strict_types=1);

namespace Modules\IMS\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImsChecklistClassificationTable extends Migration
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
            'ims_checklist_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'rate_min' => [
                'type' => 'FLOAT',
            ],
            'rate_max' => [
                'type' => 'FLOAT',
            ],
            'ims_supplier_classification_id' => [
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
        $this->forge->addKey('ims_checklist_id');
        $this->forge->addKey('ims_supplier_classification_id');

        $this->forge->addForeignKey('ims_checklist_id', 'ims_checklist', 'id', 'CASCADE', 'CASCADE', 'fk_iclc_checklist');
        $this->forge->addForeignKey('ims_supplier_classification_id', 'ims_supplier_classification', 'id', 'RESTRICT', 'CASCADE', 'fk_iclc_supplier_class');

        $this->forge->createTable('ims_checklist_classification', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ims_checklist_classification', true);
    }
}
