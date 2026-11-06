<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cursos, campi e a grade curricular (disciplinas de cada curso).
 */
class CriarTabelasCatalogo extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nome'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'codigo' => ['type' => 'CHAR', 'constraint' => 3],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('nome');
        $this->forge->addUniqueKey('codigo');
        $this->forge->createTable('cursos');

        $this->forge->addField([
            'id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 120],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('nome');
        $this->forge->createTable('campi');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'curso_id'       => ['type' => 'INT', 'unsigned' => true],
            'numero_periodo' => ['type' => 'TINYINT', 'unsigned' => true],
            'nome'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'carga_horaria'  => ['type' => 'SMALLINT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['curso_id', 'nome']);
        $this->forge->addForeignKey('curso_id', 'cursos', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('disciplinas');
    }

    public function down(): void
    {
        $this->forge->dropTable('disciplinas');
        $this->forge->dropTable('campi');
        $this->forge->dropTable('cursos');
    }
}
