<?php

declare(strict_types=1);

namespace App\Commands;

use App\Entities\Aluno;
use App\Libraries\FiltroAlunos;
use App\Models\AlunoModel;
use App\Models\CampusModel;
use App\Models\CursoModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Gera uma demonstração estática para hospedagens sem PHP, como o GitHub Pages.
 *
 * Reaproveita as views e os PDFs reais da aplicação. O que depende do servidor
 * (login, busca e relatório filtrado) é simulado pelos scripts da pasta demo/.
 */
class ExportarDemoEstatica extends BaseCommand
{
    protected $group       = 'NEAD';
    protected $name        = 'demo:exportar';
    protected $description = 'Gera o site estático de demonstração a partir do banco e das views.';
    protected $usage       = 'demo:exportar [destino]';
    protected $arguments   = ['destino' => 'Pasta de saída (padrão: build/pages). O conteúdo existente é apagado.'];

    private const USUARIO_DEMO = ['id' => 0, 'nome' => 'Ana Paula Souza', 'unidade' => 'IF Sudeste MG'];

    public function run(array $params)
    {
        $destino = rtrim($params[0] ?? ROOTPATH . 'build/pages', '/\\');
        $alunos  = model(AlunoModel::class)->buscarTodos(FiltroAlunos::deArray([]));

        if ($alunos === []) {
            CLI::error('O banco não tem alunos: rode as migrations e o seeder antes de exportar.');

            return EXIT_ERROR;
        }

        // As views usam serviços que não aceitam a requisição de linha de comando.
        Services::injectMock('request', Services::incomingrequest(null, false));

        $this->recriarPasta($destino);
        $this->copiarAssets($destino);

        file_put_contents($destino . '/.nojekyll', '');
        file_put_contents($destino . '/index.html', $this->paginaLogin());
        file_put_contents($destino . '/busca.html', $this->paginaBusca());
        file_put_contents($destino . '/dados/alunos.json', $this->dadosDaBusca($alunos));

        foreach ($alunos as $aluno) {
            file_put_contents(
                $destino . '/pdf/Historico_' . $aluno->matricula . '.pdf',
                service('historicoPdf')->gerar([$aluno]),
            );
        }

        file_put_contents($destino . '/pdf/Relatorio_Alunos_Todos.pdf', service('historicoPdf')->gerar($alunos));

        CLI::write(sprintf('Demonstração com %d alunos gerada em %s', count($alunos), $destino), 'green');

        return EXIT_SUCCESS;
    }

    private function recriarPasta(string $destino): void
    {
        if (is_dir($destino)) {
            helper('filesystem');
            delete_files($destino, true, false, true);
        }

        foreach (['', '/assets/css', '/assets/js', '/dados', '/pdf'] as $pasta) {
            if (! is_dir($destino . $pasta)) {
                mkdir($destino . $pasta, 0775, true);
            }
        }
    }

    private function copiarAssets(string $destino): void
    {
        $arquivos = [
            FCPATH . 'assets/css/style.css'    => '/assets/css/style.css',
            ROOTPATH . 'demo/assets/css/demo.css' => '/assets/css/demo.css',
            ROOTPATH . 'demo/assets/js/login.js'  => '/assets/js/login.js',
            ROOTPATH . 'demo/assets/js/busca.js'  => '/assets/js/busca.js',
        ];

        foreach ($arquivos as $origem => $alvo) {
            copy($origem, $destino . $alvo);
        }
    }

    private function paginaLogin(): string
    {
        $html = view('login/formulario', [
            'erro'              => null,
            'usuarioDigitado'   => '',
            'exibirCredenciais' => true,
        ]);

        return $this->adaptarPagina($html, 'assets/js/login.js', [
            'action="login"' => 'action="busca.html" id="form-login"',
            ' name="usuario"' => '',
            ' name="senha"'   => '',
        ]);
    }

    private function paginaBusca(): string
    {
        $alunoModel = model(AlunoModel::class);

        $html = view('alunos/index', [
            'filtro'  => FiltroAlunos::deArray([]),
            'cursos'  => model(CursoModel::class)->opcoes(),
            'campi'   => model(CampusModel::class)->opcoes(),
            'usuario' => self::USUARIO_DEMO,
            'alunos'  => $alunoModel->buscarPaginado(FiltroAlunos::deArray([]), 1),
            'pager'   => $alunoModel->pager,
            'erro'    => null,
        ]);

        // Quem preenche os resultados é o script: os links do servidor não existem aqui.
        $html = preg_replace(
            '#(<div id="area-resultados">).*?(</div>\s*</section>)#s',
            '$1<p class="vazio">Carregando...</p>$2',
            $html,
        ) ?? $html;

        return $this->adaptarPagina($html, null, [
            'action="logout"'               => 'action="index.html" id="form-sair"',
            'action="alunos"'               => 'action="busca.html"',
            ' formaction="alunos/relatorio"' => '',
            'href="alunos"'                 => 'href="busca.html"',
        ]);
    }

    /**
     * Torna relativas as URLs da aplicação e troca o que só funciona com servidor.
     *
     * @param array<string, string> $substituicoes
     */
    private function adaptarPagina(string $html, ?string $script, array $substituicoes): string
    {
        $html = preg_replace('/<!-- DEBUG-VIEW .*? -->\R?/', '', $html) ?? $html;
        $html = preg_replace('/<input type="hidden" name="' . preg_quote(csrf_token(), '/') . '"[^>]*>/', '', $html) ?? $html;
        $html = str_replace(base_url(), '', $html);
        $html = strtr($html, $substituicoes + ['method="post"' => 'method="get"']);

        $html = str_replace(
            '</head>',
            '    <meta name="robots" content="noindex">' . "\n"
            . '        <link rel="stylesheet" href="assets/css/demo.css">' . "\n"
            . '    </head>',
            $html,
        );

        $html = str_replace(
            '<main class="conteudo">',
            '<p class="aviso-demo">Demonstração estática com dados fictícios. O sistema completo roda em PHP, '
            . 'CodeIgniter e MySQL: <a href="https://github.com/RicardoRb06/NEAD-prototipo-php">ver o código</a>.</p>'
            . "\n" . '            <main class="conteudo">',
            $html,
        );

        if ($script !== null) {
            $html = str_replace('</body>', '    <script src="' . $script . '"></script>' . "\n" . '    </body>', $html);
        }

        return $html;
    }

    /**
     * Só os campos que a busca precisa: o restante fica nos PDFs.
     *
     * @param list<Aluno> $alunos
     */
    private function dadosDaBusca(array $alunos): string
    {
        $dados = array_map(static fn (Aluno $aluno): array => [
            'matricula'   => $aluno->matricula,
            'cpf'         => $aluno->cpfFormatado(),
            'nome'        => $aluno->nome,
            'nomeBusca'   => $aluno->nome_busca,
            'cursoId'     => $aluno->curso_id,
            'curso'       => $aluno->curso,
            'campusId'    => $aluno->campus_id,
            'campus'      => $aluno->campus,
            'anoIngresso' => $aluno->ano_ingresso,
            'anoFormacao' => $aluno->ano_formacao,
        ], $alunos);

        return json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
