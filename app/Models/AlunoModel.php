<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Aluno;
use App\Libraries\FiltroAlunos;
use App\Libraries\Texto;
use CodeIgniter\Model;

class AlunoModel extends Model
{
    protected $table         = 'alunos';
    protected $returnType    = Aluno::class;
    protected $allowedFields = [
        'matricula', 'cpf', 'nome', 'nome_busca', 'email', 'telefone', 'nascimento',
        'curso_id', 'campus_id', 'ano_ingresso', 'semestre_ingresso', 'ano_formacao', 'vinculo',
    ];

    /**
     * Página de resultados. Uma página além da última mostra a última.
     *
     * @return list<Aluno>
     */
    public function buscarPaginado(FiltroAlunos $filtro, int $pagina): array
    {
        $porPagina = config('Pager')->perPage;
        $total     = $this->filtrar($filtro)->countAllResults();
        $ultima    = max(1, (int) ceil($total / $porPagina));

        return $this->filtrar($filtro)->paginate($porPagina, 'default', min(max(1, $pagina), $ultima));
    }

    /**
     * @return list<Aluno>
     */
    public function buscarTodos(FiltroAlunos $filtro): array
    {
        return $this->filtrar($filtro)->findAll();
    }

    public function porMatricula(string $matricula): ?Aluno
    {
        return $this->comCursoECampus()->where('alunos.matricula', $matricula)->first();
    }

    private function filtrar(FiltroAlunos $filtro): self
    {
        $this->comCursoECampus()->orderBy('alunos.id');

        if ($filtro->matricula !== '') {
            // Matrículas só têm letras maiúsculas e dígitos.
            $this->contem('alunos.matricula', mb_strtoupper(Texto::paraBusca($filtro->matricula)));
        }

        if ($filtro->cpfDigitos() !== '') {
            $this->contem('alunos.cpf', $filtro->cpfDigitos());
        }

        if ($filtro->nome !== '') {
            $this->contem('alunos.nome_busca', Texto::paraBusca($filtro->nome));
        }

        if ($filtro->cursoId !== null) {
            $this->where('alunos.curso_id', $filtro->cursoId);
        }

        if ($filtro->campusId !== null) {
            $this->where('alunos.campus_id', $filtro->campusId);
        }

        if ($filtro->ano !== null) {
            $coluna = $filtro->tipoAno === FiltroAlunos::TIPO_FORMACAO ? 'alunos.ano_formacao' : 'alunos.ano_ingresso';
            $this->where($coluna, $filtro->ano);
        }

        return $this;
    }

    /**
     * Busca por trecho. O like() não escapa curingas: sem isso, "%" traria todos.
     */
    private function contem(string $coluna, string $trecho): void
    {
        $this->like($coluna, $this->db->escapeLikeString($trecho));
    }

    private function comCursoECampus(): self
    {
        $this->select('alunos.*, cursos.nome AS curso, campi.nome AS campus')
            ->join('cursos', 'cursos.id = alunos.curso_id')
            ->join('campi', 'campi.id = alunos.campus_id');

        return $this;
    }
}
