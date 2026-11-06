<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Alunos e seus históricos.
 *
 * A situação (aprovado/reprovado) não é gravada: deriva de nota e frequência
 * em RegrasAcademicas, evitando que os dois valores divirjam.
 */
class CriarTabelasAlunos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'matricula'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'cpf'               => ['type' => 'CHAR', 'constraint' => 11, 'comment' => 'Somente dígitos'],
            'nome'              => ['type' => 'VARCHAR', 'constraint' => 120],
            'nome_busca'        => ['type' => 'VARCHAR', 'constraint' => 120, 'comment' => 'Minúsculas e sem acentos'],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'telefone'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'nascimento'        => ['type' => 'DATE'],
            'curso_id'          => ['type' => 'INT', 'unsigned' => true],
            'campus_id'         => ['type' => 'INT', 'unsigned' => true],
            'ano_ingresso'      => ['type' => 'SMALLINT', 'unsigned' => true],
            'semestre_ingresso' => ['type' => 'TINYINT', 'unsigned' => true],
            'ano_formacao'      => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'vinculo'           => ['type' => 'VARCHAR', 'constraint' => 20],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('matricula');
        $this->forge->addUniqueKey('cpf');
        $this->forge->addKey('ano_ingresso');
        $this->forge->addKey('ano_formacao');
        $this->forge->addForeignKey('curso_id', 'cursos', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('campus_id', 'campi', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('alunos');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'aluno_id'      => ['type' => 'INT', 'unsigned' => true],
            'disciplina_id' => ['type' => 'INT', 'unsigned' => true],
            'periodo'       => ['type' => 'CHAR', 'constraint' => 6, 'comment' => 'Ano e semestre, ex.: 2023.1'],
            'nota'          => ['type' => 'TINYINT', 'unsigned' => true],
            'frequencia'    => ['type' => 'TINYINT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['aluno_id', 'disciplina_id']);
        $this->forge->addForeignKey('aluno_id', 'alunos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('disciplina_id', 'disciplinas', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('historicos');
    }

    public function down(): void
    {
        $this->forge->dropTable('historicos');
        $this->forge->dropTable('alunos');
    }
}
