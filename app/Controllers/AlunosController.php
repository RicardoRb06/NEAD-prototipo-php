<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\FiltroAlunos;
use App\Models\AlunoModel;
use App\Models\CampusModel;
use App\Models\CursoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AlunosController extends BaseController
{
    private const ERRO_RELATORIO_VAZIO = 'Não há alunos para incluir no PDF. Ajuste os critérios de busca.';

    /**
     * Página completa, ou só o trecho de resultados nas atualizações via fetch.
     */
    public function index(): ResponseInterface
    {
        $alunoModel = model(AlunoModel::class);
        $filtro     = $this->filtroDaRequisicao();
        $pagina     = (int) $this->request->getGet('page', FILTER_VALIDATE_INT);

        $resultados = [
            'alunos' => $alunoModel->buscarPaginado($filtro, $pagina),
            'pager'  => $alunoModel->pager,
            'erro'   => session()->getFlashdata('erro'),
        ];

        // A mesma URL devolve conteúdos diferentes: o cache precisa distingui-los.
        $this->response->setHeader('Vary', 'X-Requested-With');

        if ($this->request->isAJAX()) {
            return $this->response->setBody(view('alunos/_resultados', $resultados));
        }

        return $this->response->setBody(view('alunos/index', $resultados + [
            'filtro'  => $filtro,
            'cursos'  => model(CursoModel::class)->opcoes(),
            'campi'   => model(CampusModel::class)->opcoes(),
            'usuario' => service('autenticacao')->usuarioAtual(),
        ]));
    }

    /**
     * Histórico de todos os alunos que atendem aos critérios da busca.
     */
    public function relatorio(): DownloadResponse|RedirectResponse
    {
        $filtro = $this->filtroDaRequisicao();
        $alunos = model(AlunoModel::class)->buscarTodos($filtro);

        if ($alunos === []) {
            $consulta = http_build_query($this->request->getGet() ?? []);

            return redirect()->to(url_to('alunos') . ($consulta === '' ? '' : '?' . $consulta))
                ->with('erro', self::ERRO_RELATORIO_VAZIO);
        }

        $nomeArquivo = $filtro->estaAtivo() ? 'Relatorio_Alunos_Filtrados.pdf' : 'Relatorio_Alunos_Todos.pdf';

        return $this->baixarPdf($nomeArquivo, service('historicoPdf')->gerar($alunos));
    }

    public function historico(string $matricula): DownloadResponse
    {
        $aluno = model(AlunoModel::class)->porMatricula($matricula);

        if ($aluno === null) {
            throw PageNotFoundException::forPageNotFound('Aluno não encontrado.');
        }

        return $this->baixarPdf('Historico_' . $aluno->matricula . '.pdf', service('historicoPdf')->gerar([$aluno]));
    }

    private function filtroDaRequisicao(): FiltroAlunos
    {
        return FiltroAlunos::deArray($this->request->getGet() ?? []);
    }

    private function baixarPdf(string $nomeArquivo, string $conteudo): DownloadResponse
    {
        return $this->response->download($nomeArquivo, $conteudo)->setContentType('application/pdf');
    }
}
