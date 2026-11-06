<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Database\Seeds\Demo\GeradorAlunos;
use App\Libraries\Texto;
use CodeIgniter\Database\Seeder;

/**
 * Depende de CatalogoSeeder. Grava os alunos na ordem do protótipo original,
 * que é também a ordem de exibição na busca.
 */
class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $cursos      = array_column($this->db->table('cursos')->get()->getResultArray(), 'id', 'nome');
        $campi       = array_column($this->db->table('campi')->get()->getResultArray(), 'id', 'nome');
        $disciplinas = [];

        foreach ($this->db->table('disciplinas')->get()->getResultArray() as $disciplina) {
            $disciplinas[$disciplina['curso_id']][$disciplina['nome']] = $disciplina['id'];
        }

        $this->db->transStart();

        foreach ((new GeradorAlunos())->gerar() as $aluno) {
            $cursoId = $cursos[$aluno['curso']];

            $this->db->table('alunos')->insert([
                'matricula'         => $aluno['matricula'],
                'cpf'               => $aluno['cpf'],
                'nome'              => $aluno['nome'],
                'nome_busca'        => Texto::paraBusca($aluno['nome']),
                'email'             => $aluno['email'],
                'telefone'          => $aluno['telefone'],
                'nascimento'        => $aluno['nascimento'],
                'curso_id'          => $cursoId,
                'campus_id'         => $campi[$aluno['campus']],
                'ano_ingresso'      => $aluno['anoIngresso'],
                'semestre_ingresso' => $aluno['semestreIngresso'],
                'ano_formacao'      => $aluno['anoFormacao'],
                'vinculo'           => $aluno['vinculo'],
            ]);
            $alunoId = $this->db->insertID();

            $registros = array_map(static fn (array $registro): array => [
                'aluno_id'      => $alunoId,
                'disciplina_id' => $disciplinas[$cursoId][$registro['disciplina']],
                'periodo'       => $registro['periodo'],
                'nota'          => $registro['nota'],
                'frequencia'    => $registro['frequencia'],
            ], $aluno['historico']);

            $this->db->table('historicos')->insertBatch($registros);
        }

        $this->db->transComplete();
    }
}
