<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDependenciaTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('dependencia')) {
            return;
        }

        $this->forge->addField([
            'id_dependencia' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'dependencia' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
        ]);

        $this->forge->addKey('id_dependencia', true);
        $this->forge->createTable('dependencia');
    }

    public function down()
    {
        $this->forge->dropTable('dependencia', true);
    }
}
