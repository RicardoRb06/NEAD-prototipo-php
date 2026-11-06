<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Entities\RegistroHistorico;
use App\Libraries\FiltroAlunos;
use App\Libraries\IndicesAcademicos;
use App\Libraries\RegrasAcademicas;
use App\Libraries\Texto;
use CodeIgniter\Test\CIUnitTestCase;

final class DominioTest extends CIUnitTestCase
{
    public function testNormalizaTextoParaBusca(): void
    {
        $this->assertSame('ursula medeiros falcao', Texto::paraBusca('  Úrsula Medeiros FALCÃO '));
        $this->assertSame('12345678901', Texto::somenteDigitos('123.456.789-01'));
        $this->assertSame('123.456.789-01', Texto::formatarCpf('12345678901'));
    }

    public function testAprovacaoExigeNotaEFrequenciaMinimas(): void
    {
        $this->assertSame(RegrasAcademicas::APROVADO, RegrasAcademicas::situacao(60, 75));
        $this->assertSame(RegrasAcademicas::REPROVADO, RegrasAcademicas::situacao(59, 100));
        $this->assertSame(RegrasAcademicas::REPROVADO, RegrasAcademicas::situacao(100, 74));
    }

    public function testCalculaIndicesPonderadosPelaCargaHoraria(): void
    {
        $indices = IndicesAcademicos::calcular([
            new RegistroHistorico(['nota' => 80, 'frequencia' => 90, 'carga_horaria' => 90]),
            new RegistroHistorico(['nota' => 50, 'frequencia' => 90, 'carga_horaria' => 30]),
        ]);

        $this->assertSame(120, $indices->cargaHorariaCursada);
        $this->assertSame(90, $indices->cargaHorariaAprovada);
        $this->assertSame('72,5', $indices->mediaFormatada());
    }

    public function testIndicesSemDisciplinas(): void
    {
        $this->assertSame('-', IndicesAcademicos::calcular([])->mediaFormatada());
    }

    public function testAnoSemTipoEquivaleAAnoDeIngresso(): void
    {
        $filtro = FiltroAlunos::deArray(['ano' => '2023']);

        $this->assertSame(FiltroAlunos::TIPO_INGRESSO, $filtro->tipoAno);
        $this->assertSame(2023, $filtro->ano);
        $this->assertTrue($filtro->estaAtivo());
    }

    public function testFiltroIgnoraValoresMalformados(): void
    {
        $filtro = FiltroAlunos::deArray([
            'matricula' => ['injetado'],
            'curso'     => 'abc',
            'campus'    => '-1',
            'tipo_ano'  => 'qualquer',
            'ano'       => '99999',
            'cpf'       => 'sem digitos',
        ]);

        $this->assertSame('', $filtro->matricula);
        $this->assertNull($filtro->cursoId);
        $this->assertNull($filtro->campusId);
        $this->assertSame('', $filtro->tipoAno);
        $this->assertNull($filtro->ano);
        $this->assertFalse($filtro->estaAtivo());
    }
}
