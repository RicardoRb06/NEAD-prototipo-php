<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\RegistroHistorico;
use CodeIgniter\Model;

class HistoricoModel extends Model
{
    protected $table         = 'historicos';
    protected $returnType    = RegistroHistorico::class;
    protected $allowedFields = ['aluno_id', 'disciplina_id', 'periodo', 'nota', 'frequencia'];

    /**
     * Disciplinas cursadas pelo aluno, na ordem da grade.
     *
     * @return list<RegistroHistorico>
     */
    public function doAluno(int $alunoId): array
    {
        return $this->select('historicos.periodo, historicos.nota, historicos.frequencia, '
                . 'disciplinas.nome AS disciplina, disciplinas.carga_horaria, disciplinas.numero_periodo')
            ->join('disciplinas', 'disciplinas.id = historicos.disciplina_id')
            ->where('historicos.aluno_id', $alunoId)
            ->orderBy('disciplinas.numero_periodo')
            ->orderBy('disciplinas.id')
            ->findAll();
    }
}
