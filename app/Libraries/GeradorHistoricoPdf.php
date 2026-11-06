<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Entities\Aluno;
use App\Models\HistoricoModel;

/**
 * Gera o PDF de histórico de um ou mais alunos.
 */
class GeradorHistoricoPdf
{
    public function __construct(private readonly HistoricoModel $historicos)
    {
    }

    /**
     * @param iterable<Aluno> $alunos
     *
     * @return string Conteúdo binário do PDF
     */
    public function gerar(iterable $alunos): string
    {
        $documento = new DocumentoHistoricoPdf();

        foreach ($alunos as $aluno) {
            $registros = $this->historicos->doAluno($aluno->id);

            $documento->adicionarAluno(view('pdf/historico', [
                'aluno'     => $aluno,
                'registros' => $registros,
                'indices'   => IndicesAcademicos::calcular($registros),
            ]));
        }

        return $documento->Output('', 'S');
    }
}
