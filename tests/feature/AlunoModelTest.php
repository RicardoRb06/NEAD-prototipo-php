<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\FiltroAlunos;
use App\Models\AlunoModel;
use App\Models\CampusModel;
use App\Models\CursoModel;
use App\Models\HistoricoModel;
use Tests\Support\TesteComBanco;

final class AlunoModelTest extends TesteComBanco
{

    /**
     * @param array<string, string> $criterios
     *
     * @return list<string>
     */
    private function matriculas(array $criterios): array
    {
        $alunos = model(AlunoModel::class)->buscarTodos(FiltroAlunos::deArray($criterios));

        return array_map(static fn ($aluno): string => $aluno->matricula, $alunos);
    }

    public function testSemFiltroTrazTodosNaOrdemOriginal(): void
    {
        $matriculas = $this->matriculas([]);

        $this->assertCount(200, $matriculas);
        $this->assertSame('20211EMC0001', $matriculas[0]);
        $this->assertSame('20232SIN0200', $matriculas[199]);
    }

    public function testNomeIgnoraAcentosEMaiusculas(): void
    {
        $this->assertContains('20182EVT0040', $this->matriculas(['nome' => 'URSULA']));
        $this->assertContains('20182EVT0040', $this->matriculas(['nome' => 'úrsula']));
    }

    public function testMatriculaEParcialEIgnoraMaiusculas(): void
    {
        $this->assertSame(['20211EMC0001'], $this->matriculas(['matricula' => 'emc0001']));
    }

    public function testCpfAceitaMascaraEParteDosDigitos(): void
    {
        $this->assertSame(['20211EMC0001'], $this->matriculas(['cpf' => '995.536.496-37']));
        $this->assertContains('20211EMC0001', $this->matriculas(['cpf' => '995.536']));
    }

    public function testCaracteresCuringaSaoTratadosComoTexto(): void
    {
        $this->assertSame([], $this->matriculas(['nome' => '%']));
        $this->assertSame([], $this->matriculas(['nome' => "' OR 1=1 --"]));
    }

    public function testFiltraPorCursoECampus(): void
    {
        $cursos = array_flip(model(CursoModel::class)->opcoes());
        $campi  = array_flip(model(CampusModel::class)->opcoes());

        $alunos = model(AlunoModel::class)->buscarTodos(FiltroAlunos::deArray([
            'curso'  => (string) $cursos['Eventos'],
            'campus' => (string) $campi['Manhuaçu'],
        ]));

        $this->assertNotEmpty($alunos);

        foreach ($alunos as $aluno) {
            $this->assertSame('Eventos', $aluno->curso);
            $this->assertSame('Manhuaçu', $aluno->campus);
        }
    }

    public function testFiltraPorAnoDeIngressoOuDeFormacao(): void
    {
        $ingresso2016 = model(AlunoModel::class)->buscarTodos(FiltroAlunos::deArray(['tipo_ano' => 'ingresso', 'ano' => '2016']));
        $this->assertNotEmpty($ingresso2016);

        foreach ($ingresso2016 as $aluno) {
            $this->assertSame(2016, $aluno->ano_ingresso);
        }

        $this->assertSame([], $this->matriculas(['tipo_ano' => 'formacao', 'ano' => '1990']));

        $formado = model(AlunoModel::class)->where('vinculo', 'Formado')->first();
        $this->assertContains(
            $formado->matricula,
            $this->matriculas(['tipo_ano' => 'formacao', 'ano' => (string) $formado->ano_formacao]),
        );
    }

    public function testPaginaAlemDaUltimaMostraAUltima(): void
    {
        $model  = model(AlunoModel::class);
        $alunos = $model->buscarPaginado(FiltroAlunos::deArray([]), 999);

        $this->assertCount(20, $alunos);
        $this->assertSame('20232SIN0200', $alunos[19]->matricula);
        $this->assertSame(10, $model->pager->getPageCount());
        $this->assertSame(200, $model->pager->getTotal());
    }

    public function testHistoricoVemNaOrdemDaGradeComSituacaoDerivada(): void
    {
        $aluno     = model(AlunoModel::class)->porMatricula('20211EMC0001');
        $registros = model(HistoricoModel::class)->doAluno($aluno->id);

        $this->assertSame('Engenharia Mecatrônica', $aluno->curso);
        $this->assertSame('Cálculo I', $registros[0]->disciplina);
        $this->assertSame(90, $registros[0]->carga_horaria);
        $this->assertSame('2021.1', $registros[0]->periodo);
        $this->assertSame('Aprovado', $registros[0]->situacao());
    }
}
