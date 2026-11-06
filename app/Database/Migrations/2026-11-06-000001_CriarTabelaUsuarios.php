<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'usuario'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'senha_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nome'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'unidade'    => ['type' => 'VARCHAR', 'constraint' => 120],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('usuario');
        $this->forge->createTable('usuarios');
    }

    public function down(): void
    {
        $this->forge->dropTable('usuarios');
    }
}
