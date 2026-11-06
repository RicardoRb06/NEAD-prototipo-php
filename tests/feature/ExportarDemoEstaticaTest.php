<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Commands\ExportarDemoEstatica;
use Config\Services;
use Tests\Support\TesteComBanco;

final class ExportarDemoEstaticaTest extends TesteComBanco
{
    private string $destino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destino = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nead-demo-' . uniqid();
    }

    protected function tearDown(): void
    {
        helper('filesystem');
        delete_files($this->destino, true, false, true);
        rmdir($this->destino);
        Services::reset();

        parent::tearDown();
    }

    public function testGeraUmSiteQueNaoDependeDoServidor(): void
    {
        $comando = new ExportarDemoEstatica(service('logger'), service('commands'));

        $this->assertSame(EXIT_SUCCESS, $comando->run([$this->destino]));

        $login = (string) file_get_contents($this->destino . '/index.html');
        $busca = (string) file_get_contents($this->destino . '/busca.html');

        foreach ([$login, $busca] as $pagina) {
            $this->assertStringContainsString('Demonstração estática', $pagina);
            $this->assertStringContainsString('noindex', $pagina);
            $this->assertStringNotContainsString(base_url(), $pagina);
            $this->assertStringNotContainsString(csrf_token(), $pagina);
            $this->assertStringNotContainsString('method="post"', $pagina);
        }

        $this->assertStringNotContainsString('name="senha"', $login);
        $this->assertStringNotContainsString('formaction', $busca);

        $dados = json_decode((string) file_get_contents($this->destino . '/dados/alunos.json'), true);
        $this->assertCount(200, $dados);
        $this->assertSame('995.536.496-37', $dados[0]['cpf']);

        $this->assertFileExists($this->destino . '/assets/js/login.js');
        $this->assertFileExists($this->destino . '/pdf/Relatorio_Alunos_Todos.pdf');
        $this->assertCount(201, glob($this->destino . '/pdf/*.pdf'));
    }
}
