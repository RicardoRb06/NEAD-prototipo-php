<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\TesteComBanco;

final class AcessoTest extends TesteComBanco
{
    use FeatureTestTrait;

    private const LOGADO = ['usuario' => ['id' => 1, 'nome' => 'Ana Paula Souza', 'unidade' => 'IF Sudeste MG']];

    /**
     * Envia um POST com o token CSRF válido da sessão.
     *
     * @param array<string, string> $dados
     * @param array<string, mixed>  $sessao
     */
    private function postarComCsrf(string $uri, array $dados = [], array $sessao = [])
    {
        $token = csrf_hash();

        return $this->withSession($sessao + [csrf_token() => $token])
            ->post($uri, $dados + [csrf_token() => $token]);
    }

    public function testPaginasProtegidasExigemLogin(): void
    {
        foreach (['alunos', 'alunos/relatorio', 'alunos/20211EMC0001/historico'] as $uri) {
            $this->withSession([])->get($uri)->assertRedirectTo(url_to('login'));
        }
    }

    public function testRaizLevaParaABusca(): void
    {
        $this->assertStringEndsWith('/alunos', $this->withSession([])->get('/')->getRedirectUrl());
    }

    public function testTelaDeLoginMostraCredenciaisDoPrototipo(): void
    {
        $resposta = $this->withSession([])->get('login');

        $resposta->assertOK();
        $resposta->assertSee('Entrar no Sistema');
        $resposta->assertSee('Acesso ao protótipo');
    }

    public function testQuemJaEstaLogadoNaoVeOLogin(): void
    {
        $this->withSession(self::LOGADO)->get('login')->assertRedirectTo(url_to('alunos'));
    }

    public function testLoginValidoAutentica(): void
    {
        $resposta = $this->postarComCsrf('login', ['usuario' => ' admin ', 'senha' => 'admin']);

        $resposta->assertRedirectTo(url_to('alunos'));
        $this->assertSame('Ana Paula Souza', session('usuario')['nome']);
    }

    public function testSenhaIncorretaNaoAutenticaNemGuardaASenha(): void
    {
        $resposta = $this->postarComCsrf('login', ['usuario' => 'admin', 'senha' => 'errada']);

        $resposta->assertRedirectTo(url_to('login'));
        $this->assertNull(session('usuario'));
        $this->assertStringContainsString('Usuário ou senha incorretos', session('erro'));
        $this->assertSame('admin', session('usuario_digitado'));
        $this->assertStringNotContainsString('errada', serialize($_SESSION));
    }

    public function testCamposVaziosPedemPreenchimento(): void
    {
        $this->postarComCsrf('login', ['usuario' => '', 'senha' => ''])->assertRedirectTo(url_to('login'));

        $this->assertSame('Informe o usuário e a senha para entrar.', session('erro'));
    }

    public function testPostSemTokenCsrfERejeitado(): void
    {
        $this->expectException(SecurityException::class);

        $this->withSession([])->post('login', ['usuario' => 'admin', 'senha' => 'admin']);
    }

    public function testLogoutNaoAceitaGet(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->withSession(self::LOGADO)->get('logout');
    }

    public function testBuscaMostraUsuarioFiltrosEPrimeiraPagina(): void
    {
        $resposta = $this->withSession(self::LOGADO)->get('alunos');

        $resposta->assertOK();
        $resposta->assertSee('Ana Paula Souza');
        $resposta->assertSee('Lucas Andrade Ribeiro');
        $resposta->assertSee('995.536.496-37');
        $resposta->assertSee('200 alunos encontrados');
        $resposta->assertSee('Página 1 de 10');
        $resposta->assertSee('Sistema de Informação');
        $resposta->assertDontSee('Osmar Cardoso Soares');
        $resposta->assertHeader('X-Frame-Options');
    }

    public function testRequisicaoAjaxDevolveSoOsResultados(): void
    {
        $resposta = $this->withSession(self::LOGADO)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get('alunos', ['nome' => 'osmar']);

        $resposta->assertOK();
        $resposta->assertSee('Osmar Cardoso Soares');
        $resposta->assertSee('1 aluno encontrado');
        $this->assertStringNotContainsString('<html', $resposta->response()->getBody());
        $resposta->assertDontSee('Página 1 de');
    }

    public function testBuscaSemResultadosInformaEEscapaAEntrada(): void
    {
        $resposta = $this->withSession(self::LOGADO)->get('alunos', ['nome' => '"><script>alert(1)</script>']);

        $resposta->assertOK();
        $resposta->assertSee('Nenhum aluno encontrado com os critérios informados.');
        $resposta->assertDontSee('<script>alert(1)');
    }

    /**
     * @param array<string, string> $parametros
     *
     * @return array{disposicao: string, tipo: string}
     */
    private function baixar(string $uri, array $parametros = []): array
    {
        $download = $this->withSession(self::LOGADO)->get($uri, $parametros)->response();

        $this->assertInstanceOf(DownloadResponse::class, $download);
        $download->buildHeaders();

        return [
            'disposicao' => $download->getHeaderLine('Content-Disposition'),
            'tipo'       => $download->getHeaderLine('Content-Type'),
        ];
    }

    public function testRelatorioCompletoTemNomeProprio(): void
    {
        $this->assertStringContainsString('Relatorio_Alunos_Todos.pdf', $this->baixar('alunos/relatorio')['disposicao']);
    }

    public function testHistoricoIndividualBaixaPdf(): void
    {
        $download = $this->baixar('alunos/20211EMC0001/historico');

        $this->assertStringContainsString('Historico_20211EMC0001.pdf', $download['disposicao']);
        $this->assertStringContainsString('application/pdf', $download['tipo']);
    }

    public function testMatriculaInexistenteDa404(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->withSession(self::LOGADO)->get('alunos/NAOEXISTE/historico');
    }

    public function testRelatorioFiltradoTemNomeProprio(): void
    {
        $this->assertStringContainsString(
            'Relatorio_Alunos_Filtrados.pdf',
            $this->baixar('alunos/relatorio', ['nome' => 'osmar'])['disposicao'],
        );
    }

    public function testRelatorioVazioVoltaParaABuscaComAviso(): void
    {
        $resposta = $this->withSession(self::LOGADO)->get('alunos/relatorio', ['nome' => 'inexistente']);

        $resposta->assertRedirect();
        $this->assertStringContainsString('/alunos?nome=inexistente', $resposta->getRedirectUrl());
        $this->assertStringContainsString('Não há alunos para incluir no PDF', session('erro'));
    }
}
