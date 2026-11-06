<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\FiltroAlunos;
use App\Models\AlunoModel;
use Tests\Support\TesteComBanco;

final class GeradorHistoricoPdfTest extends TesteComBanco
{
    public function testGeraPdfDeUmAluno(): void
    {
        $aluno = model(AlunoModel::class)->porMatricula('20211EMC0001');

        $this->assertStringStartsWith('%PDF-', service('historicoPdf')->gerar([$aluno]));
    }

    public function testCadaAlunoDoRelatorioComecaEmNovaPagina(): void
    {
        $alunos = model(AlunoModel::class)->buscarTodos(FiltroAlunos::deArray([]));

        $paginas = preg_match_all('#/Type /Page\b#', service('historicoPdf')->gerar($alunos));

        $this->assertGreaterThanOrEqual(count($alunos), $paginas);
    }
}
