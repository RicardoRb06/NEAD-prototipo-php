<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\Seeds\Demo\GeradorAlunos;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Os valores esperados vêm da execução do db.js do protótipo original.
 */
final class GeradorAlunosTest extends CIUnitTestCase
{
    public function testGeraOsMesmosAlunosDoPrototipoOriginal(): void
    {
        $alunos = (new GeradorAlunos())->gerar();

        $this->assertCount(200, $alunos);
        $this->assertSame(4020, array_sum(array_map(static fn (array $a): int => count($a['historico']), $alunos)));

        $primeiro = $alunos[0];
        $this->assertSame('20211EMC0001', $primeiro['matricula']);
        $this->assertSame('99553649637', $primeiro['cpf']);
        $this->assertSame('lucas.ribeiro@estudante.ifsudestemg.edu.br', $primeiro['email']);
        $this->assertSame('(32) 98176-2367', $primeiro['telefone']);
        $this->assertSame('Juiz de Fora', $primeiro['campus']);
        $this->assertSame(93, $primeiro['historico'][0]['nota']);
        $this->assertSame(97, $primeiro['historico'][0]['frequencia']);

        $ultimo = $alunos[199];
        $this->assertSame('20232SIN0200', $ultimo['matricula']);
        $this->assertSame('84675739105', $ultimo['cpf']);
        $this->assertSame('(32) 98419-8347', $ultimo['telefone']);
        $this->assertSame('Santos Dumont', $ultimo['campus']);
    }

    public function testApenasUmAlunoConcluiuSemReprovacoes(): void
    {
        $formados = array_filter((new GeradorAlunos())->gerar(), static fn (array $a): bool => $a['vinculo'] === 'Formado');

        $this->assertCount(1, $formados);
        $this->assertNotNull(array_values($formados)[0]['anoFormacao']);
    }
}
