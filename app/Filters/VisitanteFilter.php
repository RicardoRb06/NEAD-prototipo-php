<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Quem já está logado não vê a tela de entrada: vai direto para a busca.
 */
class VisitanteFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (service('autenticacao')->estaAutenticado()) {
            return redirect()->route('alunos');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
